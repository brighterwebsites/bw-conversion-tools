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
            'title' => 'Not sure what path will get you where you want to go?',
            'subtitle' => 'Answer 4 quick questions to find your starting point.',
        ), $atts, 'bw_service_pathway_quiz' );
        
        ob_start();
        ?>
        <div class="bw-quiz-container" data-quiz-id="service-pathway">
            <div class="bw-quiz-header">
                <h2 class="bw-quiz-title"><?php echo esc_html( $atts['title'] ); ?></h2>
                <p class="bw-quiz-subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
            </div>
            
            <!-- Progress Bar -->
            <div class="bw-quiz-progress-wrapper">
                <div class="bw-quiz-progress-bar">
                    <div class="bw-quiz-progress-fill" style="width: 25%"></div>
                </div>
                <p class="bw-quiz-counter"><span class="bw-current-question">1</span> of 4</p>
            </div>
            
            <!-- Quiz Form -->
            <form id="bw-service-pathway-form" class="bw-quiz-form">
                <?php wp_nonce_field( 'bw_quiz_nonce', 'bw_quiz_nonce' ); ?>
                
                <!-- Question 1: Starting Point -->
                <div class="bw-quiz-question" data-question="q1" style="display: block;">
                    <h3 class="bw-question-title">Where are you in your business right now?</h3>
                    <div class="bw-question-options">
                        <label class="bw-option">
                            <input type="radio" name="q1" value="stage_1_have_stuff" required>
                            <span class="bw-option-text">
                                <strong>I have a service/product</strong> but haven't made consistent sales yet
                            </span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q1" value="stage_2_sell_stuff" required>
                            <span class="bw-option-text">
                                <strong>I'm making sales</strong> but it's mostly me hustling, not predictable
                            </span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q1" value="stage_3_profitable" required>
                            <span class="bw-option-text">
                                <strong>I'm profitable</strong> with steady customers, now I want to scale
                            </span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q1" value="stage_4_scaling" required>
                            <span class="bw-option-text">
                                <strong>I'm scaling</strong> and traffic/conversions work, I need to dominate my market
                            </span>
                        </label>
                    </div>
                </div>
                
                <!-- Question 2: Bottleneck -->
                <div class="bw-quiz-question" data-question="q2" style="display: none;">
                    <h3 class="bw-question-title">What's your biggest bottleneck right now?</h3>
                    <div class="bw-question-options">
                        <label class="bw-option">
                            <input type="radio" name="q2" value="bottleneck_visibility" required>
                            <span class="bw-option-text">
                            <strong>I’m not being found</strong><br>
                            No website, no visibility — and AI doesn’t mention me yet.
                            </span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q2" value="bottleneck_consistency" required>
                            <span class="bw-option-text">
                            <strong>Leads are inconsistent</strong><br>
                            I’m getting enquiries, but only when I push hard — not automatically.
                            </span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q2" value="bottleneck_conversions" required>
                            <span class="bw-option-text">
                            <strong>Traffic isn’t converting</strong><br>
                            People visit my site but don’t enquire — I’m not sure why.
                            </span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q2" value="bottleneck_trajectory" required>
                            <span class="bw-option-text">
                            <strong>Growth feels flat</strong><br>
                            I’m making sales, but it’s not consistent or clearly scaling yet.
                            </span>
                        </label>
                    </div>
                </div>
                
                <!-- Question 3: Timeline & Approach -->
                <div class="bw-quiz-question" data-question="q3" style="display: none;">
                    <h3 class="bw-question-title">How are you thinking about timeline and approach?</h3>
                    <div class="bw-question-options">
                        <label class="bw-option">
                            <input type="radio" name="q3" value="approach_urgent" required>
                            <span class="bw-option-text"> <strong>Urgent & Decisive</strong><br>
                            I need results fast (2–4 weeks). Let’s prioritise the highest-impact work now.</span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q3" value="approach_strategic_lean" required>
                            <span class="bw-option-text"> <strong>Strategic but Lean</strong><br>
                            Budget-conscious but committed — solid foundations first, growth next.</span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q3" value="approach_minimal_viable" required>
                            <span class="bw-option-text"> <strong>Minimum Viable Foundation</strong><br>
                            Tight budget — need a core setup that works even if I can’t continue straight away.</span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q3" value="approach_flexible_longterm" required>
                            <span class="bw-option-text"> <strong>Flexible & Long-Term</strong><br>
                            I’m thinking ahead — I want a sustainable growth system that scales.</span>
                        </label>
                    </div>
                </div>
                
                <!-- Question 4: Success in 12 Months -->
                <div class="bw-quiz-question" data-question="q4" style="display: none;">
                    <h3 class="bw-question-title">Think 12 months from now... What closely represents your single most important success factor?</h3>
                    <div class="bw-question-options">
                        <label class="bw-option">
                            <input type="radio" name="q4" value="success_consistent_flow" required>
                            <span class="bw-option-text">    <strong>Steady Lead Flow</strong><br>
                            No more chasing work, Consistent enquiries coming in automatically.</span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q4" value="success_scale_confidently" required>
                            <span class="bw-option-text"> 
                            <strong>Predictable Sales & Ready to Scale</strong><br>
                            Steady conversions so I can increase marketing without wasting spend.


                            </span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q4" value="success_own_market" required>
                            <span class="bw-option-text">      <strong>Market Dominance</strong><br>
                Known as the go-to provider in my niche or region — dominating search and AI mentions.
           </span>
                        </label>
                        <label class="bw-option">
                            <input type="radio" name="q4" value="success_sellable_asset" required>
                            <span class="bw-option-text"><strong>Systemised or Sellable Business</strong><br>
                            A brand and website that runs itself — an asset I could sell or step back from.</span>
                        </label>
                    </div>
                </div>
                
                <!-- Navigation Buttons -->
                <div class="bw-quiz-navigation">
                    <button type="button" class="bw-btn bw-btn-back" style="display: none;">← Back</button>
                    <button type="button" class="bw-btn bw-btn-next">Next →</button>
                </div>
            </form>
            
            <!-- Result Screen -->
            <div class="bw-quiz-result" style="display: none;">
                <div class="bw-result-header">
                    <p>It sounds like...</p>
                </div>
                
                <div class="bw-result-diagnosis"></div>
                <div class="bw-result-explanation"></div>
                
                <div class="bw-result-next-steps">
                    <h3 class="bw-result-next-heading">Explore your pathway</h3>
                    <p class="bw-result-next-subtext"></p>
                    <div class="bw-result-buttons">
                        <a href="#" class="bw-btn bw-btn-primary bw-result-pathway-link">View Your Pathway</a>
                        <a href="#" class="bw-btn bw-btn-secondary bw-result-email-link">Email Your Questions</a>
                    </div>
                </div>

                <div class="bw-result-social-proof"></div>
                
                <div class="bw-result-reset">
                    <button type="button" class="bw-btn bw-btn-reset">↺ Take Quiz Again</button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
