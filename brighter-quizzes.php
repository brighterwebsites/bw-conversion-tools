<?php
/**
 * Plugin Name: Brighter Websites  Quizzes
 * Plugin URI: https://brighterwebsites.com.au
 * Description: Reusable quiz system for lead qualification and routing
 * Version: 1.0.0
 * Author: Brighter Websites
 * Author URI: https://brighterwebsites.com.au
 * License: GPL-2.0+
 * Text Domain: bw-quizzes
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'BW_QUIZZES_PATH', plugin_dir_path( __FILE__ ) );
define( 'BW_QUIZZES_URL', plugin_dir_url( __FILE__ ) );
define( 'BW_QUIZZES_VERSION', '1.0.0' );

// Load plugin files
require_once BW_QUIZZES_PATH . 'includes/class-quiz-engine.php';
require_once BW_QUIZZES_PATH . 'includes/class-shortcode-handler.php';
require_once BW_QUIZZES_PATH . 'includes/class-webhook-handler.php';
require_once BW_QUIZZES_PATH . 'includes/quizzes/service-pathway-quiz.php';

/**
 * Initialize the plugin
 */
function bw_quizzes_init() {
    // Load text domain for translations
    load_plugin_textdomain( 'bw-quizzes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
    // Initialize quiz engine
    $engine = BW_Quiz_Engine::instance();
    
    // Register service pathway quiz
    $engine->register_quiz( 'service-pathway', array(
        'id' => 'service-pathway',
        'title' => 'Service Pathway Quiz',
        'description' => 'Find your service pathway: Launch Fast, Grow Visibility, or Scale Smarter.',
    ) );
    
    // Fire action for other quiz registrations
    do_action( 'bw_quizzes_init' );
    
    // Register shortcodes
    BW_Shortcode_Handler::register_shortcodes();
}
add_action( 'plugins_loaded', 'bw_quizzes_init' );

/**
 * Load frontend assets only on pages with quiz shortcode
 * Add page IDs to this array to include quiz on that page
 */
function bw_quizzes_load_assets() {
    // PAGE IDS WHERE QUIZ APPEARS - Add more as needed
    $quiz_page_ids = array(
        40643,  // Launch pathway page
        34068,  // Growth pathway page
        23344,  // Scale pathway page
        17092,  // Contact
        19069,  // Web Design page
        13528,
        131,  // SEO page
        44422, //popup id
        
        // Add more page IDs here as you add quiz to other pages
    );
    
    // Get current page ID
    $current_page_id = get_queried_object_id();
    
    // Only load if current page is in quiz_page_ids array
    if ( ! in_array( $current_page_id, $quiz_page_ids ) ) {
        return;
    }
    
    // Load CSS
    wp_enqueue_style(
        'bw-quizzes-frontend',
        BW_QUIZZES_URL . 'assets/css/quiz-frontend.css',
        array(),
        BW_QUIZZES_VERSION
    );
    
    // Load JS
    wp_enqueue_script(
        'bw-quizzes-frontend',
        BW_QUIZZES_URL . 'assets/js/quiz-frontend.js',
        array( 'jquery' ),
        BW_QUIZZES_VERSION,
        true
    );
    
    // Localize AJAX data
    wp_localize_script(
        'bw-quizzes-frontend',
        'bwQuizzes',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'bw_quiz_nonce' ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'bw_quizzes_load_assets' );

/**
 * Register AJAX handlers
 */
function bw_quizzes_register_ajax() {
    add_action( 'wp_ajax_nopriv_bw_submit_quiz', array( 'BW_Webhook_Handler', 'handle_quiz_submission' ) );
    add_action( 'wp_ajax_bw_submit_quiz', array( 'BW_Webhook_Handler', 'handle_quiz_submission' ) );
}
add_action( 'init', 'bw_quizzes_register_ajax' );

/**
 * Activation hook
 */
function bw_quizzes_activate() {
    // Add any activation routines here
    do_action( 'bw_quizzes_activate' );
}
register_activation_hook( __FILE__, 'bw_quizzes_activate' );

/**
 * Deactivation hook
 */
function bw_quizzes_deactivate() {
    // Add any deactivation routines here
    do_action( 'bw_quizzes_deactivate' );
}
register_deactivation_hook( __FILE__, 'bw_quizzes_deactivate' );