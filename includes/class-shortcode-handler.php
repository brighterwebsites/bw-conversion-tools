<?php
/**
 * Shortcode Handler Class
 * Manages quiz rendering and output
 */

class BW_Shortcode_Handler {

    /**
     * Register all shortcodes
     */
    public static function register_shortcodes() {
        add_shortcode( 'bw_service_pathway_quiz', array( __CLASS__, 'render_service_pathway_quiz' ) );
    }

    /**
     * Render Service Pathway Quiz shortcode
     */
    public static function render_service_pathway_quiz( $atts ) {
        $atts = shortcode_atts( array(
            'title'    => 'Not sure what path will get you where you want to go?',
            'subtitle' => 'Answer 3 quick questions to find your starting point.',
        ), $atts, 'bw_service_pathway_quiz' );

        ob_start();
        ?>
        <div class="bw-quiz-container" data-quiz-id="service-pathway">
            <!-- Progress Bar -->
            <div class="bw-quiz-progress-wrapper">
                <div class="bw-quiz-progress-bar">
                    <div class="bw-quiz-progress-fill" style="width: 33%"></div>
                </div>
                <p class="bw-quiz-counter"><span class="bw-current-question">1</span> of 3</p>
            </div>

            <!-- Quiz Form -->
            <form id="bw-service-pathway-form" class="bw-quiz-form">
                <?php wp_nonce_field( 'bw_quiz_nonce', 'bw_quiz_nonce' ); ?>

                <!-- Question 1: Stage -->
                <div class="bw-quiz-question" data-question="q1" style="display: block;">
                    <div class="bw-question-options">
                        <label class="bw-option">
                            <input type="radio" name="q1" value="stage_early" required>
                            <span class="bw-option-text">I'm early stage or don't have an established <span class="bw-highlight">brand</span> or <span class="bw-highlight">website</span></span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q1" value="stage_established" required>
                            <span class="bw-option-text">I'm established and making sales but enquiries are <span class="bw-highlight">unpredictable or slow</span></span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q1" value="stage_scaling" required>
                            <span class="bw-option-text">I have steady sales, I want to <span class="bw-highlight">expand</span> and need more <span class="bw-highlight">growth</span></span>
                        </label>
                    </div>
                </div>

                <!-- Question 2: Problem -->
                <div class="bw-quiz-question" data-question="q2" style="display: none;">
                    <div class="bw-question-options">
                        <label class="bw-option">
                            <input type="radio" name="q2" value="problem_brand" required>
                            <span class="bw-option-text">I don't have <span class="bw-highlight">consistent brand visuals</span> and message</span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q2" value="problem_search" required>
                            <span class="bw-option-text">I'm not showing up in <span class="bw-highlight">search or AI chats</span> where I'm expected</span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q2" value="problem_leads" required>
                            <span class="bw-option-text">I don't get <span class="bw-highlight">enough leads</span> or they are inconsistent</span>
                        </label>
                    </div>
                </div>

                <!-- Question 3: Approach -->
                <div class="bw-quiz-question" data-question="q3" style="display: none;">
                    <div class="bw-question-options">
                        <label class="bw-option">
                            <input type="radio" name="q3" value="approach_mvf" required>
                            <span class="bw-option-text"><span class="bw-highlight">Minimum Viable Foundation</span> — tight budget, need a core setup I can build on</span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q3" value="approach_lean" required>
                            <span class="bw-option-text"><span class="bw-highlight">Strategic but Lean</span> — prioritise highest-impact work now</span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q3" value="approach_handled" required>
                            <span class="bw-option-text">Someone to just <span class="bw-highlight">handle it all</span> — sustainable growth system that scales</span>
                        </label>
                    </div>
                </div>

            </form>

            <!-- Result Screen -->
            <div class="bw-quiz-result" style="display: none;">
                <div class="bw-result-header">
                    <p class="bw-result-pathway-label"></p>
                </div>

                <div class="bw-result-diagnosis"></div>

                <div class="bw-result-next-steps">
                    <h3 class="bw-result-next-heading">Explore your pathway</h3>
                    <p class="bw-result-next-subtext"></p>
                    <div class="bw-result-buttons">
                        <a href="#" class="bw-btn bw-btn-primary bw-result-pathway-link">View Your Pathway</a>
                        <a href="#" class="bw-btn bw-btn-secondary bw-result-email-link">Talk to Vanessa →</a>
                    </div>
                </div>

                <div class="bw-result-reset">
                    <button type="button" class="bw-btn bw-btn-reset">↺ Take Quiz Again</button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
