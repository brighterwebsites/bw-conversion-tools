<?php
/**
 * Webhook Handler Class
 * Processes quiz submissions and sends to external endpoints
 */

class BW_Webhook_Handler {
    
    /**
     * Handle quiz submission via AJAX
     */
    public static function handle_quiz_submission() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bw_quiz_nonce' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }
        
        // Get quiz data
        $quiz_id = isset( $_POST['quiz_id'] ) ? sanitize_text_field( $_POST['quiz_id'] ) : null;
        $answers = isset( $_POST['answers'] ) ? array_map( 'sanitize_text_field', $_POST['answers'] ) : array();
        
        if ( ! $quiz_id || empty( $answers ) ) {
            wp_send_json_error( 'Missing required data' );
        }
        
        // Process answers
        $engine = BW_Quiz_Engine::instance();
        $result = $engine->process_answers( $quiz_id, $answers );
        
        if ( isset( $result['error'] ) ) {
            wp_send_json_error( $result['error'] );
        }
        
        // Send data silently to support email for analytics
        $email = 'support@brighterwebsites.com.au';
        $name = null;
        self::send_quiz_data_email( $email, $result, $answers );
        
        // Return result for inline display
        wp_send_json_success( array(
            'result' => $result,
        ) );
    }
    
    /**
     * Send quiz data to webhook endpoint
     */
    private static function send_to_webhook( $quiz_id, $name, $email, $answers, $result ) {
        // Get webhook URL from settings (you'll configure this in WordPress admin)
        $webhook_url = get_option( 'bw_quiz_webhook_url' );
        
        if ( empty( $webhook_url ) ) {
            return; // Webhook not configured
        }
        
        // Prepare payload
        $payload = array(
            'timestamp' => current_time( 'c' ),
            'quiz_id' => $quiz_id,
            'name' => $name,
            'email' => $email,
            'pathway' => $result['pathway'],
            'diagnosis' => $result['diagnosis'],
            'answers' => $answers,
        );
        
        // Send async request (non-blocking)
        wp_remote_post( $webhook_url, array(
            'body' => json_encode( $payload ),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 5,
            'blocking' => false, // Non-blocking request
            'sslverify' => apply_filters( 'bw_quiz_webhook_sslverify', true ),
        ) );
    }
    
    /**
     * Send quiz data email to support for analytics
     */
    private static function send_quiz_data_email( $email, $result, $answers ) {
        $site_name = get_bloginfo( 'name' );
        $pathway = $result['pathway'];
        $timestamp = current_time( 'Y-m-d H:i:s' );
        
        $subject = "Quiz Submission: $pathway pathway";
        
        $message = "New service pathway quiz submission\n\n";
        $message .= "Pathway: " . $pathway . "\n";
        $message .= "Time: " . $timestamp . "\n";
        $message .= "Diagnosis: " . $result['diagnosis'] . "\n\n";
        $message .= "Answers:\n";
        $message .= "Q1 (Stage): " . ( isset( $answers['q1'] ) ? $answers['q1'] : 'N/A' ) . "\n";
        $message .= "Q2 (Bottleneck): " . ( isset( $answers['q2'] ) ? $answers['q2'] : 'N/A' ) . "\n";
        $message .= "Q3 (Approach): " . ( isset( $answers['q3'] ) ? $answers['q3'] : 'N/A' ) . "\n";
        $message .= "Q4 (Success Goal): " . ( isset( $answers['q4'] ) ? $answers['q4'] : 'N/A' ) . "\n";
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_option( 'admin_email' ),
        );
        
        wp_mail( $email, $subject, $message, $headers );
    }
    
    /**
     * Get pathway label for display
     */
    private static function get_pathway_label( $pathway ) {
        $labels = array(
            'launch' => 'Launch',
            'grow_seo' => 'Grow (SEO-First)',
            'growth_cro' => 'Growth (CRO-First)',
            'scale' => 'Scale',
        );
        
        return isset( $labels[ $pathway ] ) ? $labels[ $pathway ] : 'Growth';
    }
}
