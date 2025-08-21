<?php
/**
 * Main plugin class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Career_Progression {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        // Constructor
    }
    
    public function init() {
        // Load text domain
        add_action('init', array($this, 'load_textdomain'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Initialize components
        $this->init_components();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('career-progression', false, dirname(CPV_PLUGIN_BASENAME) . '/languages');
    }
    
    public function enqueue_frontend_scripts() {
        // D3.js library
        wp_enqueue_script(
            'd3-js',
            'https://d3js.org/d3.v7.min.js',
            array(),
            '7.8.5',
            true
        );
        
        // Plugin frontend script
        wp_enqueue_script(
            'cpv-frontend',
            CPV_PLUGIN_URL . 'assets/js/career-visualization.js',
            array('d3-js', 'jquery'),
            CPV_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('cpv-frontend', 'cpv_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cpv_nonce')
        ));
        
        // Plugin frontend styles
        wp_enqueue_style(
            'cpv-frontend',
            CPV_PLUGIN_URL . 'assets/css/career-visualization.css',
            array(),
            CPV_VERSION
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'career-progression') === false) {
            return;
        }
        
        // Admin scripts
        wp_enqueue_script(
            'cpv-admin',
            CPV_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery', 'jquery-ui-datepicker'),
            CPV_VERSION,
            true
        );
        
        // Admin styles
        wp_enqueue_style(
            'cpv-admin',
            CPV_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            CPV_VERSION
        );
        
        wp_enqueue_style('jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css');
        
        // Load visualization scripts on settings page for preview
        if (strpos($hook, 'settings') !== false) {
            // D3.js
            wp_enqueue_script(
                'cpv-d3',
                'https://d3js.org/d3.v7.min.js',
                array(),
                '7.8.5',
                true
            );
            
            // Visualization script
            wp_enqueue_script(
                'cpv-visualization',
                CPV_PLUGIN_URL . 'assets/js/career-visualization.js',
                array('jquery', 'cpv-d3'),
                CPV_VERSION,
                true
            );
            
            // Localize script for AJAX
            wp_localize_script('cpv-visualization', 'cpv_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cpv_nonce')
            ));
            
            // Visualization styles
            wp_enqueue_style(
                'cpv-visualization',
                CPV_PLUGIN_URL . 'assets/css/career-visualization.css',
                array(),
                CPV_VERSION
            );
        }
    }
    
    private function init_components() {
        // Initialize admin
        if (is_admin()) {
            $admin = new CPV_Admin();
            $admin->init();
        }
        
        // Initialize shortcode
        $shortcode = new CPV_Shortcode();
        $shortcode->init();
        
        // AJAX handlers
        add_action('wp_ajax_cpv_get_career_data', array($this, 'ajax_get_career_data'));
        add_action('wp_ajax_nopriv_cpv_get_career_data', array($this, 'ajax_get_career_data'));
    }
    
    public function ajax_get_career_data() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cpv_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'career_progression';
        
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY start_date ASC");
        
        // Build hierarchical data structure matching the new schema
        $paths = array();
        $min_year = 9999;
        
        foreach ($results as $job) {
            $path_type = $job->path_type ?: 'Default Path';
            $path_color = $job->path_color ?: '#4299e1';
            
            $start_year = intval(date('Y', strtotime($job->start_date)));
            $end_year = $job->end_date ? intval(date('Y', strtotime($job->end_date))) : intval(date('Y')) + 1;
            
            // Track minimum year for root
            if ($start_year < $min_year) {
                $min_year = $start_year;
            }
            
            if (!isset($paths[$path_type])) {
                $paths[$path_type] = array(
                    'name' => $path_type,
                    'type' => 'path',
                    'color' => $path_color,
                    'startYear' => $start_year,
                    'description' => '',
                    'children' => array()
                );
            }
            
            // Update path start year if this job is earlier
            if ($start_year < $paths[$path_type]['startYear']) {
                $paths[$path_type]['startYear'] = $start_year;
            }
            
            // Format dates string
            $start_date_formatted = date('F Y', strtotime($job->start_date));
            $end_date_formatted = $job->end_date ? date('F Y', strtotime($job->end_date)) : 'Present';
            
            $paths[$path_type]['children'][] = array(
                'id' => $job->id,
                'name' => $job->company,
                'title' => $job->position,
                'dates' => $start_date_formatted . ' – ' . $end_date_formatted,
                'startYear' => $start_year,
                'endYear' => $end_year,
                'type' => 'job',
                'description' => $job->description,
                'skills' => json_decode($job->skills),
                'achievements' => json_decode($job->achievements),
                'salary' => $job->salary,
                'location' => $job->location
            );
        }
        
        // Create root structure matching the schema
        $career_data = array(
            'name' => 'Career Journey',
            'startYear' => $min_year !== 9999 ? $min_year : intval(date('Y')),
            'children' => array_values($paths)
        );
        
        wp_send_json_success($career_data);
    }
}