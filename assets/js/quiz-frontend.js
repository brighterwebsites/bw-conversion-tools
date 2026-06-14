/**
 * Brighter Websites Quiz Frontend
 * Handles quiz flow, validation, and submission
 */

(function($) {
    'use strict';
    
    const QuizManager = {
        currentQuestion: 1,
        totalQuestions: 4,
        answers: {},
        
        /**
         * Initialize quiz
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
        },
        
        /**
         * Cache jQuery elements
         */
        cacheElements: function() {
            this.$container = $('.bw-quiz-container');
            this.$form = $('#bw-service-pathway-form');
            this.$questions = this.$form.find('.bw-quiz-question');
            this.$progress = this.$form.find('.bw-quiz-progress-fill');
            this.$counter = this.$form.find('.bw-current-question');
            this.$nextBtn = this.$form.find('.bw-btn-next');
            this.$backBtn = this.$form.find('.bw-btn-back');
            this.$result = this.$container.find('.bw-quiz-result');
        },
        
        /**
         * Bind event listeners
         */
        bindEvents: function() {
            const self = this;
            
            // Option selection changes
            this.$form.on('change', 'input[type="radio"]', function() {
                self.onOptionSelected($(this));
            });
            
            // Next button
            this.$nextBtn.on('click', function(e) {
                e.preventDefault();
                self.nextQuestion();
            });
            
            // Back button
            this.$backBtn.on('click', function(e) {
                e.preventDefault();
                self.previousQuestion();
            });
            
            // Reset button
            this.$container.on('click', '.bw-btn-reset', function(e) {
                e.preventDefault();
                self.resetQuiz();
            });
        },
        
        /**
         * Handle option selection
         */
        onOptionSelected: function($input) {
            const questionId = $input.attr('name');
            const value = $input.val();
            
            // Store answer
            this.answers[questionId] = value;
            
            // Highlight selected option
            $input.closest('.bw-option').siblings().removeClass('selected');
            $input.closest('.bw-option').addClass('selected');
            
            // Auto-advance after short delay for better UX
            const self = this;
            setTimeout(function() {
                self.nextQuestion();
            }, 300);
        },
        
        /**
         * Move to next question
         */
        nextQuestion: function() {
            // Validate current question answered
            if (!this.isCurrentQuestionAnswered()) {
                console.log('Please answer the current question');
                return;
            }
            
            // Check if this is the last question
            if (this.currentQuestion === this.totalQuestions) {
                this.submitQuiz();
                return;
            }
            
            // Move to next question
            this.currentQuestion++;
            this.updateDisplay();
        },
        
        /**
         * Move to previous question
         */
        previousQuestion: function() {
            if (this.currentQuestion > 1) {
                this.currentQuestion--;
                this.updateDisplay();
            }
        },
        
        /**
         * Check if current question is answered
         */
        isCurrentQuestionAnswered: function() {
            const currentQ = this.$questions.eq(this.currentQuestion - 1);
            const questionName = currentQ.find('input[type="radio"]').attr('name');
            return this.answers.hasOwnProperty(questionName) && this.answers[questionName] !== '';
        },
        
        /**
         * Update display (show/hide questions, update progress)
         */
        updateDisplay: function() {
            // Hide all questions
            this.$questions.hide();
            
            // Show current question
            this.$questions.eq(this.currentQuestion - 1).show();
            
            // Update progress bar
            const progressPercent = (this.currentQuestion / this.totalQuestions) * 100;
            this.$progress.css('width', progressPercent + '%');
            
            // Update counter
            this.$counter.text(this.currentQuestion);
            
            // Update button visibility
            if (this.currentQuestion === 1) {
                this.$backBtn.hide();
            } else {
                this.$backBtn.show();
            }
            
            // Last question: change next button text
            if (this.currentQuestion === this.totalQuestions) {
                this.$nextBtn.text('See My Pathway →');
            } else {
                this.$nextBtn.text('Next →');
            }
            
            // Scroll to question
            $('html, body').animate({
                scrollTop: this.$form.offset().top - 100
            }, 300);
        },
        
        /**
         * Submit quiz
         */
        submitQuiz: function() {
            const self = this;
            
            // Send data silently to support email for analytics
            const name = null;
            const email = 'support@brighterwebsites.com.au';
            
            // Show loading state
            this.$nextBtn.prop('disabled', true).text('Processing...');
            
            // Send to server
            $.ajax({
                type: 'POST',
                url: bwQuizzes.ajaxUrl,
                data: {
                    action: 'bw_submit_quiz',
                    quiz_id: 'service-pathway',
                    answers: this.answers,
                    name: name,
                    email: email,
                    nonce: bwQuizzes.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showResult(response.data.result);
                    } else {
                        console.error('Quiz submission failed:', response.data);
                        self.$nextBtn.prop('disabled', false).text('Next →');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    self.$nextBtn.prop('disabled', false).text('Next →');
                }
            });
        },
        
        /**
         * Display result
         */
        showResult: function(result) {
            // Hide form and header
            this.$form.hide();
            this.$container.find('.bw-quiz-header').hide();
            this.$container.find('.bw-quiz-progress-wrapper').hide();
            
            const proof = result.social_proof;
            const nextSteps = result.next_steps;
            
            // Update diagnosis
            this.$result.find('.bw-result-diagnosis').html(
                '<p><strong>' + this.escapeHtml(result.diagnosis) + '</strong></p>'
            );
            
            // Update explanation
            this.$result.find('.bw-result-explanation').html(
                '<p>' + this.escapeHtml(result.explanation) + '</p>'
            );
            
            // Update next steps section
            this.$result.find('.bw-result-next-heading').text(nextSteps.heading);
            this.$result.find('.bw-result-next-subtext').text(nextSteps.subtext);
            this.$result.find('.bw-result-pathway-link')
                .attr('href', result.cta_link)
                .text(nextSteps.cta_text);
            
            // Build mailto link
            const mailtoLink = this.buildMailtoLink(result);
            this.$result.find('.bw-result-email-link').attr('href', mailtoLink);
            
            // Update social proof
            this.$result.find('.bw-result-social-proof').html(
                '<div class="bw-result-social-proof-stat">' + this.escapeHtml(proof.stat) + '</div>' +
                '<div class="bw-result-social-proof-client">' + this.escapeHtml(proof.client) + '</div>' +
                '<div class="bw-result-social-proof-detail">' + this.escapeHtml(proof.detail) + '</div>'
            );
            
            // Show result
            this.$result.show();
            
            // Scroll to result
            $('html, body').animate({
                scrollTop: this.$result.offset().top - 100
            }, 300);
        },
        
        /**
         * Reset quiz to start
         */
        resetQuiz: function() {
            // Reset state
            this.currentQuestion = 1;
            this.answers = {};
            
            // Clear all selections
            this.$form.find('input[type="radio"]').prop('checked', false);
            this.$form.find('.bw-option').removeClass('selected');
            
            // Reset button state
            this.$nextBtn.prop('disabled', false).text('Next →');
            
            // Hide result, show form and header
            this.$result.hide();
            this.$form.show();
            this.$container.find('.bw-quiz-header').show();
            this.$container.find('.bw-quiz-progress-wrapper').show();
            
            // Reset display
            this.updateDisplay();
            
            // Scroll to quiz
            $('html, body').animate({
                scrollTop: this.$container.offset().top - 100
            }, 300);
        },
        
        /**
         * Build mailto link with quiz data
         */
        buildMailtoLink: function(result) {
            const pathwayLabel = result.pathway_label || this.getPathwayLabel(result.pathway);
            const email = 'support@brighterwebsites.com.au';
            const subject = 'Can you tell me more about ' + pathwayLabel + '?';
            
            // Format answers for email body
            const answerLabels = {
                // Q1: Business Stage
                'stage_1_have_stuff': 'I have a service/product but haven\'t made consistent sales yet',
                'stage_2_sell_stuff': 'I\'m making sales but it\'s mostly me hustling, not predictable',
                'stage_3_profitable': 'I\'m profitable with steady customers, now I want to scale',
                'stage_4_scaling': 'I\'m scaling and traffic/conversions work, I need to dominate my market',
                // Q2: Bottleneck
                'bottleneck_visibility': 'No website / I don\'t show up / AI doesn\'t mention me',
                'bottleneck_consistency': 'Getting consistent enquiries without doing all the work myself',
                'bottleneck_conversions': 'Visitors come but don\'t convert / I don\'t know why they leave',
                'bottleneck_trajectory': 'I have leads and sales, but it\'s not predictable enough',
                // Q3: Approach
                'approach_urgent': 'Urgent & Decisive (2-4 weeks)',
                'approach_strategic_lean': 'Strategic but Lean (2-3 months)',
                'approach_minimal_viable': 'Minimum Viable Foundation (budget tight)',
                'approach_flexible_longterm': 'Flexible Long-Term (sustained growth)',
                // Q4: Success Goal
                'success_consistent_flow': 'Consistent lead flow without me hunting',
                'success_scale_confidently': 'Predictable conversions so I can scale ads/marketing',
                'success_own_market': 'Own my market in my niche/location',
                'success_sellable_asset': 'Build something I could eventually sell or systemize'
            };
            
            let body = 'Hi Vanessa,\n\n';
            body += 'I took the quiz and I\'d like to know more about the ' + pathwayLabel + ' pathway.\n\n';
            body += '---\n';
            body += 'My Quiz Answers:\n\n';
            body += '• Where I am now: ' + (answerLabels[this.answers.q1] || this.answers.q1 || 'Not answered') + '\n';
            body += '• My biggest bottleneck: ' + (answerLabels[this.answers.q2] || this.answers.q2 || 'Not answered') + '\n';
            body += '• My approach/timeline: ' + (answerLabels[this.answers.q3] || this.answers.q3 || 'Not answered') + '\n';
            body += '• What success looks like: ' + (answerLabels[this.answers.q4] || this.answers.q4 || 'Not answered') + '\n\n';
            body += '---\n';
            body += 'Quiz Recommendation: ' + pathwayLabel + '\n';
            body += '"' + result.diagnosis + '"\n\n';
            body += '---\n\n';
            body += '[Add your questions here]\n\n';
            body += 'Thanks!';
            
            return 'mailto:' + email + '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body);
        },
        
        /**
         * Get pathway label for display
         */
        getPathwayLabel: function(pathway) {
            const labels = {
                'launch': 'Launch',
                'grow_seo': 'Grow (SEO-First)',
                'growth_cro': 'Growth (CRO-First)',
                'scale': 'Scale'
            };
            
            return labels[pathway] || 'Growth';
        },
        
        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        QuizManager.init();
    });
    
})(jQuery);
