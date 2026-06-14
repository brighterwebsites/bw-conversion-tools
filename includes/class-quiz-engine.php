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
        
        // Extract key answers for routing (Q4 now = success goal, not fear)
        $q1 = isset( $answers['q1'] ) ? $answers['q1'] : null;
        $q2 = isset( $answers['q2'] ) ? $answers['q2'] : null;
        $q3 = isset( $answers['q3'] ) ? $answers['q3'] : null;
        $q4 = isset( $answers['q4'] ) ? $answers['q4'] : null;  // Now = success goal
        
        // Determine pathway using routing logic (based on Q1, Q2, Q3)
        $pathway = $this->determine_pathway( $q1, $q2, $q3 );
        
        // Build result
        $result = array(
            'pathway' => $pathway,
            'pathway_label' => $this->get_pathway_label( $pathway ),
            'diagnosis' => $this->get_diagnosis( $pathway, $q1, $q2, $q3 ),
            'explanation' => $this->get_explanation( $pathway, $q3 ),
            'next_steps' => $this->get_next_steps( $pathway ),
            'social_proof' => $this->get_social_proof( $pathway ),
            'cta_link' => $this->get_cta_link( $pathway ),
            'answers' => $answers, // Store for webhook/email
        );
        
        return $result;
    }
    
    /**
     * Determine pathway based on routing logic
     */
    private function determine_pathway( $q1, $q2, $q3 ) {
        
        // Route 1: "I have stuff to sell" → Launch (any Q2, any Q3)
        if ( $q1 === 'stage_1_have_stuff' ) {
            return 'launch';
        }
        
        // Route 2: "I sell stuff" + "No website/don't show up" + "Urgent & Decisive" → Launch
        if ( $q1 === 'stage_2_sell_stuff' && $q2 === 'bottleneck_visibility' && $q3 === 'approach_urgent' ) {
            return 'launch';
        }
        
        // Route 3: "I sell stuff" + "Getting consistent enquiries" + "Strategic but Lean" → Grow (SEO-first)
        if ( $q1 === 'stage_2_sell_stuff' && $q2 === 'bottleneck_consistency' && $q3 === 'approach_strategic_lean' ) {
            return 'grow_seo';
        }
        
        // Route 4: "I sell stuff" OR "I'm profitable" + "Visitors don't convert" + any Q3 → Growth (CRO-first)
        if ( ( $q1 === 'stage_2_sell_stuff' || $q1 === 'stage_3_profitable' ) && $q2 === 'bottleneck_conversions' ) {
            return 'growth_cro';
        }
        
        // Route 5: "I'm profitable" OR "I'm scaling" + "Not predictable/on upwards trajectory" + "Flexible Long-Term" → Growth or Scale
        if ( ( $q1 === 'stage_3_profitable' || $q1 === 'stage_4_scaling' ) && $q2 === 'bottleneck_trajectory' && $q3 === 'approach_flexible_longterm' ) {
            return 'growth_cro'; // Default to Growth for this combination
        }
        
        // Route 6: "I'm scaling" + any Q2 + any Q3 → Scale
        if ( $q1 === 'stage_4_scaling' ) {
            return 'scale';
        }
        
        // Default fallback: Route to Growth (most common middle ground)
        return 'growth_cro';
    }
    
    /**
     * Get diagnosis message based on pathway and answers
     */
    private function get_diagnosis( $pathway, $q1, $q2, $q3 ) {
        $diagnoses = array(
            'launch' => 'You\'re at the starting line or early stage. It\'s time to look credible, visible, and get ready for growth.',
            'grow_seo' => 'You\'re winning work, but it\'s a little unpredictable. Let\'s turn that momentum into a steady stream of qualified leads.',
            'growth_cro' => 'You\'re getting traffic, but not enough quality enquiries. That\'s a conversion infrastructure problem, and we can fix it.',
            'scale' => 'You\'re ready to dominate your market. It\'s time to scale what\'s working with automation and amplification.',
      
        );
        
        return isset( $diagnoses[ $pathway ] ) ? $diagnoses[ $pathway ] : $diagnoses['growth_cro'];
    }
    
    /**
     * Get pathway label for display
     */
    private function get_pathway_label( $pathway ) {
        $labels = array(
            'launch' => 'Launch Fast',
            'grow_seo' => 'Grow Visibility',
            'growth_cro' => 'Conversion-Focused Design',
            'scale' => 'Scale Smarter',
        );
        
        return isset( $labels[ $pathway ] ) ? $labels[ $pathway ] : 'Growth';
    }
    
    /**
     * Get explanation personalized by approach (Q3)
     */
    private function get_explanation( $pathway, $q3_approach ) {
        $base_explanations = array(
            'launch' => 'At the moment the biggest constraint is how credible you appear online, and the technical foundation that makes you appear in search. The Launch Fast package builds a professional, AI-ready website with SEO and CRO best practices baked in — so you can start attracting leads and building trust fast.',
            'grow_seo' => 'You\'re making sales, but your marketing efforts (and sales growth) feel inconsistent. The Grow Visibility pathway builds long-term predictability — integrating SEO, content strategy, and analytics to generate steady, high-intent leads month after month.',
            'growth_cro' => 'You already have traffic — the missing piece is conversion. The Conversion-Focused Design process re-engineers your user journey, builds trust through proof, and removes friction from enquiry to booking.',
            'scale' => 'You\'ve proven your model and want to scale. The Scale Smarter system uses automation, social amplification, and performance data to expand your visibility — compounding the results that already work.',
        );
        
        // Approach-specific additions
        $approach_additions = array(
            'approach_urgent' => array(
                'launch' => ' We can have you online in 2–3 weeks with a focused, high-impact build.',
                'grow_seo' => ' We\'ll prioritize the highest-ROI actions first so you see movement fast.',
                'growth_cro' => ' Quick wins first — we\'ll identify and fix the biggest conversion leaks immediately.',
                'scale' => ' Fast execution on what\'s already working, then systematic expansion.',
            ),
            'approach_strategic_lean' => array(
                'launch' => ' We\'ll build smart foundations that can grow with you, without overbuilding now.',
                'grow_seo' => ' Strategic investment in content and SEO that compounds over 2–3 months.',
                'growth_cro' => ' Data-driven testing to maximize ROI on every change we make.',
                'scale' => ' Methodical scaling with clear metrics at each stage.',
            ),
            'approach_minimal_viable' => array(
                'launch' => ' We\'ll focus on essentials that work standalone — no lock-in, no fluff.',
                'grow_seo' => ' Prioritized foundations that deliver value even if you pause later.',
                'growth_cro' => ' Targeted fixes for your biggest leaks — maximum impact, minimum spend.',
                'scale' => ' Core systems first, with clear upgrade paths when you\'re ready.',
            ),
            'approach_flexible_longterm' => array(
                'launch' => ' We\'ll build for where you\'re going, not just where you are now.',
                'grow_seo' => ' Full strategic roadmap with compounding returns over 6–12 months.',
                'growth_cro' => ' Comprehensive optimization with ongoing refinement and testing.',
                'scale' => ' Complete ecosystem build — SEO, social amplification, and automation working together.',
            ),
        );
        
        $explanation = isset( $base_explanations[ $pathway ] ) ? $base_explanations[ $pathway ] : $base_explanations['growth_cro'];
        
        // Add approach-specific content if available
        if ( $q3_approach && isset( $approach_additions[ $q3_approach ][ $pathway ] ) ) {
            $explanation .= $approach_additions[ $q3_approach ][ $pathway ];
        }
        
        return $explanation;
    }
    
    /**
     * Get next steps content for pathway
     */
    private function get_next_steps( $pathway ) {
        $next_steps = array(
            'launch' => array(
                'heading' => 'Explore your Launch Fast pathway',
                'subtext' => 'In 2–3 weeks, you could be online, visible, and built to start winning new business.',
                'cta_text' => 'View Launch Fast →',
            ),
            'grow_seo' => array(
                'heading' => 'Explore your Grow Visibility pathway',
                'subtext' => 'Build the SEO and content foundations that generate consistent, high-intent leads.',
                'cta_text' => 'View Grow Visibility →',
            ),
            'growth_cro' => array(
                'heading' => 'Explore your Conversion-Focused pathway',
                'subtext' => 'Turn the traffic you have into the leads and bookings you need.',
                'cta_text' => 'View Conversion Design →',
            ),
            'scale' => array(
                'heading' => 'Explore your Scale Smarter pathway',
                'subtext' => 'Amplify what\'s working and build systems that grow without you.',
                'cta_text' => 'View Scale Smarter →',
            ),
        );
        
        return isset( $next_steps[ $pathway ] ) ? $next_steps[ $pathway ] : $next_steps['growth_cro'];
    }
    
    /**
     * Get social proof for pathway
     */
    private function get_social_proof( $pathway ) {
        $proofs = array(
            'launch' => array(
                'stat' => '+53% increase in enquiries within 90 days',
                'client' => 'One Team Counselling',
                'detail' => 'From dated site to confident bookings through CRO and clarity.',
            ),
            'grow_seo' => array(
                'stat' => '+541% organic traffic growth in 90 days',
                'client' => 'Paws For Support',
                'detail' => 'SEO matched paid ad volume with 9× higher conversion rate.',
            ),
            'growth_cro' => array(
                'stat' => '+200% increase in on-page engagement',
                'client' => 'One Team Counselling',
                'detail' => 'Better conversions through user behavior design.',
            ),
            'scale' => array(
                'stat' => '+148% organic visibility growth',
                'client' => 'Guerrilla Steel',
                'detail' => 'Dominated local search through infrastructure + amplification.',
            ),
        );
        
        return isset( $proofs[ $pathway ] ) ? $proofs[ $pathway ] : $proofs['growth_cro'];
    }
    
    /**
     * Get CTA link for pathway
     */
    private function get_cta_link( $pathway ) {
        $links = array(
            'launch' => 'https://brighterwebsites.com.au/services/pathway-launch/',
            'grow_seo' => 'https://brighterwebsites.com.au/services/pathway-growth#seo',
            'growth_cro' => 'https://brighterwebsites.com.au/services/pathway-growth#cro',
            'scale' => 'https://brighterwebsites.com.au/services/pathway-scale/',
        );
        
        // Fallback to home if links not configured
        return isset( $links[ $pathway ] ) ? $links[ $pathway ] : home_url();
    }
}
