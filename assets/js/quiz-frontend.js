/**
 * Brighter Websites Quiz Frontend
 * Handles quiz flow, validation, and submission
 */

(function($) {
    'use strict';

    const QuizManager = {
        currentQuestion: 1,
        totalQuestions: 3,
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
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        },

        /**
         * Display result
         */
        showResult: function(result) {
            // Hide form
            this.$form.hide();
            this.$container.find('.bw-quiz-progress-wrapper').hide();

            const nextSteps = result.next_steps;

            // Update pathway label
            this.$result.find('.bw-result-pathway-label').text(result.pathway_label);

            // Update diagnosis
            this.$result.find('.bw-result-diagnosis').html(
                '<p><strong>' + this.escapeHtml(result.diagnosis) + '</strong></p>'
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

            // Hide result, show form
            this.$result.hide();
            this.$form.show();
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

            const answerLabels = {
                // Q1: Stage
                'stage_early':       "I'm early stage or don't have an established brand or website",
                'stage_established': "I'm established and making sales but enquiries are unpredictable or slow",
                'stage_scaling':     'I have steady sales, I want to expand and need more growth',
                // Q2: Problem
                'problem_brand':  "I don't have consistent brand visuals and message",
                'problem_search': "I'm not showing up in search or AI chats where I'm expected",
                'problem_leads':  "I don't get enough leads or they are inconsistent",
                // Q3: Approach
                'approach_mvf':     'Minimum Viable Foundation — tight budget, need a core setup I can build on',
                'approach_lean':    'Strategic but Lean — prioritise highest-impact work now',
                'approach_handled': 'Someone to just handle it all — sustainable growth system that scales',
            };

            let body = 'Hi Vanessa,\n\n';
            body += "I took the quiz and I'd like to know more about the " + pathwayLabel + ' pathway.\n\n';
            body += '---\n';
            body += 'My Quiz Answers:\n\n';
            body += '• Where I am now: ' + (answerLabels[this.answers.q1] || this.answers.q1 || 'Not answered') + '\n';
            body += '• My biggest problem: ' + (answerLabels[this.answers.q2] || this.answers.q2 || 'Not answered') + '\n';
            body += '• My approach: ' + (answerLabels[this.answers.q3] || this.answers.q3 || 'Not answered') + '\n\n';
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
                'launch': 'Launch Fast',
                'growth': 'Grow Visibility',
                'scale':  'Scale Smarter',
            };

            return labels[pathway] || 'Grow Visibility';
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
