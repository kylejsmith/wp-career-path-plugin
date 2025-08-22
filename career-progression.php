<?php
/**
 * Plugin Name: Career Progression Visualizer
 * Plugin URI: https://www.kylesmith.com/plugins
 * Description: Visualize your career journey using interactive D3.js charts
 * Version: 0.0.39
 * Author: Kyle Smith
 * Author URI: https://www.kylesmith.com
 * License: GPL v2 or later
 * Text Domain: career-progression
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CPV_VERSION', '0.0.39');
define('CPV_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CPV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CPV_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once CPV_PLUGIN_DIR . 'includes/class-career-progression.php';
require_once CPV_PLUGIN_DIR . 'includes/class-cpv-admin.php';
require_once CPV_PLUGIN_DIR . 'includes/class-cpv-shortcode.php';
require_once CPV_PLUGIN_DIR . 'includes/class-cpv-linkedin.php';
require_once CPV_PLUGIN_DIR . 'includes/sample-data.php';

// Activation hook
register_activation_hook(__FILE__, 'cpv_activate');
function cpv_activate() {
    // Create database table for career data
    global $wpdb;
    $table_name = $wpdb->prefix . 'career_progression';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        position varchar(255) NOT NULL,
        company varchar(255) NOT NULL,
        company_image varchar(500),
        start_date date NOT NULL,
        end_date date DEFAULT NULL,
        description text,
        skills text,
        achievements text,
        location varchar(255),
        path_type varchar(100),
        path_color varchar(20),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Add default options
    add_option('cpv_version', CPV_VERSION);
    add_option('cpv_settings', array(
        'theme' => 'light',
        'width' => '1200px',
        'height' => '600px',
        'animation_speed' => 1000,
        'show_timeline' => true,
        'show_skills' => true
    ));
    
    // Don't insert sample data - start with empty database
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'cpv_deactivate');
function cpv_deactivate() {
    // Clean up temporary data
    wp_clear_scheduled_hook('cpv_daily_cleanup');
}

// Initialize plugin
function cpv_init() {
    $career_progression = new Career_Progression();
    $career_progression->init();
    
    // Initialize LinkedIn integration
    $linkedin = new CPV_LinkedIn();
    $linkedin->init();
}
add_action('plugins_loaded', 'cpv_init');