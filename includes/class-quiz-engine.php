<?php
/**
 * Quiz Engine Class
 * Handles quiz logic, routing, and result determination
 */

class BW_Quiz_Engine {

    private static $instance = null;
    private $quizzes = array();

    /**
     * Singleton instance
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a quiz
     */
    public function register_quiz( $quiz_id, $quiz_data ) {
        $this->quizzes[ $quiz_id ] = $quiz_data;
    }

    /**
     * Get quiz data
     */
    public function get_quiz( $quiz_id ) {
        return isset( $this->quizzes[ $quiz_id ] ) ? $this->quizzes[ $quiz_id ] : null;
    }

    /**
     * Process quiz answers and return result
     *
     * @param string $quiz_id The quiz identifier
     * @param array $answers User answers keyed by question ID
     * @return array Result data including pathway, message, and metadata
     */
    public function process_answers( $quiz_id, $answers ) {
        $quiz = $this->get_quiz( $quiz_id );

        if ( ! $quiz ) {
            return array( 'error' => 'Quiz not found' );
        }

        $q1 = isset( $answers['q1'] ) ? $answers['q1'] : null;
        $q2 = isset( $answers['q2'] ) ? $answers['q2'] : null;
        $q3 = isset( $answers['q3'] ) ? $answers['q3'] : null;

        $pathway = $this->determine_pathway( $q1, $q2, $q3 );

        $result = array(
            'pathway'       => $pathway,
            'pathway_label' => $this->get_pathway_label( $pathway ),
            'diagnosis'     => $this->get_diagnosis( $pathway ),
            'explanation'   => $this->get_explanation( $pathway ),
            'next_steps'    => $this->get_next_steps( $pathway ),
            'social_proof'  => $this->get_social_proof( $pathway ),
            'cta_link'      => $this->get_cta_link( $pathway ),
            'answers'       => $answers,
        );

        return $result;
    }

    /**
     * Determine pathway based on routing logic
     */
    private function determine_pathway( $q1, $q2, $q3 ) {

        // Early stage: brand problem always → Launch
        if ( $q1 === 'stage_early' && $q2 === 'problem_brand' ) {
            return 'launch';
        }

        // Early stage: visibility/lead problem → Q3 decides
        if ( $q1 === 'stage_early' && ( $q2 === 'problem_search' || $q2 === 'problem_leads' ) ) {
            if ( $q3 === 'approach_mvf' ) {
                return 'launch';
            }
            if ( $q3 === 'approach_lean' || $q3 === 'approach_handled' ) {
                return 'growth';
            }
        }

        // Established: brand problem → Q3 decides
        if ( $q1 === 'stage_established' && $q2 === 'problem_brand' ) {
            if ( $q3 === 'approach_mvf' || $q3 === 'approach_lean' ) {
                return 'growth';
            }
            if ( $q3 === 'approach_handled' ) {
                return 'scale';
            }
        }

        // Established: visibility/lead problem → Q3 decides
        if ( $q1 === 'stage_established' && ( $q2 === 'problem_search' || $q2 === 'problem_leads' ) ) {
            if ( $q3 === 'approach_mvf' || $q3 === 'approach_lean' ) {
                return 'growth';
            }
            if ( $q3 === 'approach_handled' ) {
                return 'scale';
            }
        }

        // Scaling: brand problem → Q3 decides
        if ( $q1 === 'stage_scaling' && $q2 === 'problem_brand' ) {
            if ( $q3 === 'approach_mvf' || $q3 === 'approach_lean' ) {
                return 'growth';
            }
            if ( $q3 === 'approach_handled' ) {
                return 'scale';
            }
        }

        // Scaling: visibility/lead problem → Q3 decides
        if ( $q1 === 'stage_scaling' && ( $q2 === 'problem_search' || $q2 === 'problem_leads' ) ) {
            if ( $q3 === 'approach_mvf' ) {
                return 'growth';
            }
            if ( $q3 === 'approach_lean' || $q3 === 'approach_handled' ) {
                return 'scale';
            }
        }

        return 'growth';
    }

    /**
     * Get diagnosis message for pathway
     */
    private function get_diagnosis( $pathway ) {
        $diagnoses = array(
            'launch' => 'You need solid foundations before anything else will stick — a credible brand, a visible website, and the basics done right.',
            'growth' => "You're in the market but not getting the consistent leads your effort deserves. The fix is strategic visibility and conversion foundations.",
            'scale'  => "You've proven the model. Now it's about amplifying what works and building systems that grow without you.",
        );

        return isset( $diagnoses[ $pathway ] ) ? $diagnoses[ $pathway ] : $diagnoses['growth'];
    }

    /**
     * Get pathway label for display
     */
    private function get_pathway_label( $pathway ) {
        $labels = array(
            'launch' => 'Launch Fast',
            'growth' => 'Grow Visibility',
            'scale'  => 'Scale Smarter',
        );

        return isset( $labels[ $pathway ] ) ? $labels[ $pathway ] : 'Grow Visibility';
    }

    /**
     * Get explanation for pathway
     */
    private function get_explanation( $pathway ) {
        $explanations = array(
            'launch' => 'The Launch Fast pathway builds a professional, AI-ready website with brand clarity, SEO, and CRO best practices baked in from day one — so you can start attracting leads and building trust fast.',
            'growth' => 'The Grow Visibility pathway builds long-term predictability — integrating SEO, content strategy, and conversion design to generate steady, high-intent leads month after month.',
            'scale'  => 'The Scale Smarter system uses automation, social amplification, and performance data to expand your visibility and compound the results that already work.',
        );

        return isset( $explanations[ $pathway ] ) ? $explanations[ $pathway ] : $explanations['growth'];
    }

    /**
     * Get next steps content for pathway
     */
    private function get_next_steps( $pathway ) {
        $next_steps = array(
            'launch' => array(
                'heading'  => 'Explore your Launch Fast pathway',
                'subtext'  => 'In 2–3 weeks, you could be online, visible, and built to start winning new business.',
                'cta_text' => 'View Launch Fast →',
            ),
            'growth' => array(
                'heading'  => 'Explore your Grow Visibility pathway',
                'subtext'  => 'Build the foundations that generate consistent, high-intent leads without you chasing them.',
                'cta_text' => 'View Grow Visibility →',
            ),
            'scale' => array(
                'heading'  => 'Explore your Scale Smarter pathway',
                'subtext'  => "Amplify what's working and build systems that grow without you.",
                'cta_text' => 'View Scale Smarter →',
            ),
        );

        return isset( $next_steps[ $pathway ] ) ? $next_steps[ $pathway ] : $next_steps['growth'];
    }

    /**
     * Get social proof for pathway
     */
    private function get_social_proof( $pathway ) {
        $proofs = array(
            'launch' => array(
                'stat'   => '+53% increase in enquiries within 90 days',
                'client' => 'One Team Counselling',
                'detail' => 'From dated site to confident bookings through brand clarity and CRO.',
            ),
            'growth' => array(
                'stat'   => '+541% organic traffic growth in 90 days',
                'client' => 'Paws For Support',
                'detail' => 'SEO matched paid ad volume with 9× higher conversion rate.',
            ),
            'scale' => array(
                'stat'   => '+148% organic visibility growth',
                'client' => 'Guerrilla Steel',
                'detail' => 'Dominated local search through infrastructure and amplification.',
            ),
        );

        return isset( $proofs[ $pathway ] ) ? $proofs[ $pathway ] : $proofs['growth'];
    }

    /**
     * Get CTA link for pathway
     */
    private function get_cta_link( $pathway ) {
        $links = array(
            'launch' => home_url( '/services/pathway-launch/' ),
            'growth' => home_url( '/services/pathway-growth/' ),
            'scale'  => home_url( '/services/pathway-scale/' ),
        );

        return isset( $links[ $pathway ] ) ? $links[ $pathway ] : home_url();
    }
}
