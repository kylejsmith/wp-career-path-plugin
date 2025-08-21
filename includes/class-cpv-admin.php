<?php
/**
 * Admin functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPV_Admin {
    
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // AJAX handlers for admin
        add_action('wp_ajax_cpv_save_career_entry', array($this, 'ajax_save_career_entry'));
        add_action('wp_ajax_cpv_delete_career_entry', array($this, 'ajax_delete_career_entry'));
        add_action('wp_ajax_cpv_delete_all_entries', array($this, 'ajax_delete_all_entries'));
        add_action('wp_ajax_cpv_get_career_entry', array($this, 'ajax_get_career_entry'));
        add_action('wp_ajax_cpv_import_json', array($this, 'ajax_import_json'));
        add_action('wp_ajax_cpv_export_json', array($this, 'ajax_export_json'));
        add_action('wp_ajax_cpv_convert_linkedin_data', array($this, 'ajax_convert_linkedin_data'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Career Progression', 'career-progression'),
            __('Career Progression', 'career-progression'),
            'manage_options',
            'career-progression',
            array($this, 'render_main_page'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'career-progression',
            __('All Entries', 'career-progression'),
            __('All Entries', 'career-progression'),
            'manage_options',
            'career-progression',
            array($this, 'render_main_page')
        );
        
        add_submenu_page(
            'career-progression',
            __('Add New Entry', 'career-progression'),
            __('Add New Entry', 'career-progression'),
            'manage_options',
            'career-progression-add',
            array($this, 'render_add_page')
        );
        
        add_submenu_page(
            'career-progression',
            __('LinkedIn Import', 'career-progression'),
            __('LinkedIn Import', 'career-progression'),
            'manage_options',
            'career-progression-linkedin',
            array($this, 'render_linkedin_page')
        );
        
        add_submenu_page(
            'career-progression',
            __('Import/Export JSON', 'career-progression'),
            __('Import/Export JSON', 'career-progression'),
            'manage_options',
            'career-progression-json',
            array($this, 'render_json_page')
        );
        
        add_submenu_page(
            'career-progression',
            __('Settings', 'career-progression'),
            __('Settings', 'career-progression'),
            'manage_options',
            'career-progression-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('cpv_settings_group', 'cpv_settings');
    }
    
    public function render_main_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'career_progression';
        $entries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY start_date DESC");
        
        include CPV_PLUGIN_DIR . 'admin/views/main-page.php';
    }
    
    public function render_add_page() {
        $entry_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $entry = null;
        
        if ($entry_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'career_progression';
            $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $entry_id));
        }
        
        include CPV_PLUGIN_DIR . 'admin/views/add-page.php';
    }
    
    public function render_settings_page() {
        $settings = get_option('cpv_settings', array());
        include CPV_PLUGIN_DIR . 'admin/views/settings-page.php';
    }
    
    public function render_json_page() {
        include CPV_PLUGIN_DIR . 'admin/views/json-import-export.php';
    }
    
    public function render_linkedin_page() {
        // Include LinkedIn class if not already loaded
        if (!class_exists('CPV_LinkedIn')) {
            require_once CPV_PLUGIN_DIR . 'includes/class-cpv-linkedin.php';
        }
        include CPV_PLUGIN_DIR . 'admin/views/linkedin-page.php';
    }
    
    private function get_path_color($path_type) {
        // Predefined colors for common paths
        $path_colors = array(
            'IT Path' => '#4299e1',
            'Design Path' => '#ed8936',
            'Engineering Path' => '#48bb78',
            'Management Path' => '#9f7aea',
            'Business Path' => '#f6ad55',
            'Marketing Path' => '#fc8181',
            'Sales Path' => '#4fd1c5',
            'Product Path' => '#63b3ed'
        );
        
        if (isset($path_colors[$path_type])) {
            return $path_colors[$path_type];
        }
        
        // Generate a color for new paths
        $colors = array('#e53e3e', '#dd6b20', '#d69e2e', '#38a169', '#319795', '#3182ce', '#5a67d8', '#805ad5', '#d53f8c');
        return $colors[crc32($path_type) % count($colors)];
    }
    
    public function ajax_save_career_entry() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cpv_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'career_progression';
        
        $data = array(
            'position' => sanitize_text_field($_POST['position']),
            'company' => sanitize_text_field($_POST['company']),
            'start_date' => sanitize_text_field($_POST['start_date']),
            'end_date' => !empty($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : null,
            'description' => sanitize_textarea_field($_POST['description']),
            'skills' => json_encode(array_map('sanitize_text_field', explode(',', $_POST['skills']))),
            'achievements' => json_encode(array_map('sanitize_text_field', explode("\n", $_POST['achievements']))),
            'location' => sanitize_text_field($_POST['location']),
            'company_image' => isset($_POST['company_image']) ? esc_url_raw($_POST['company_image']) : '',
            'path_type' => sanitize_text_field($_POST['path_type']),
            'path_color' => $this->get_path_color($_POST['path_type'])
        );
        
        if (!empty($_POST['entry_id'])) {
            // Update existing entry
            $wpdb->update(
                $table_name,
                $data,
                array('id' => intval($_POST['entry_id']))
            );
            $entry_id = intval($_POST['entry_id']);
        } else {
            // Insert new entry
            $wpdb->insert($table_name, $data);
            $entry_id = $wpdb->insert_id;
        }
        
        wp_send_json_success(array('entry_id' => $entry_id));
    }
    
    public function ajax_delete_career_entry() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cpv_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'career_progression';
        
        $wpdb->delete(
            $table_name,
            array('id' => intval($_POST['entry_id']))
        );
        
        wp_send_json_success();
    }
    
    public function ajax_delete_all_entries() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cpv_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'career_progression';
        
        // Delete all entries from the table
        $wpdb->query("TRUNCATE TABLE $table_name");
        
        wp_send_json_success();
    }
    
    public function ajax_get_career_entry() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'career_progression';
        
        $entry = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            intval($_GET['entry_id'])
        ));
        
        if ($entry) {
            $entry->skills = json_decode($entry->skills);
            $entry->achievements = json_decode($entry->achievements);
        }
        
        wp_send_json_success($entry);
    }
    
    public function ajax_export_json() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cpv_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'career_progression';
        
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY start_date ASC");
        
        // Build hierarchical JSON structure matching the schema
        $paths = array();
        
        foreach ($results as $job) {
            $path_type = $job->path_type ?: 'Default Path';
            $path_color = $job->path_color ?: '#4299e1';
            
            if (!isset($paths[$path_type])) {
                $paths[$path_type] = array(
                    'name' => $path_type,
                    'type' => 'path',
                    'color' => $path_color,
                    'startYear' => 9999,
                    'description' => '',
                    'children' => array()
                );
            }
            
            $start_year = intval(date('Y', strtotime($job->start_date)));
            $end_year = $job->end_date ? intval(date('Y', strtotime($job->end_date))) : intval(date('Y'));
            
            // Update path start year if this job is earlier
            if ($start_year < $paths[$path_type]['startYear']) {
                $paths[$path_type]['startYear'] = $start_year;
            }
            
            $job_entry = array(
                'name' => $job->company,
                'title' => $job->position,
                'dates' => date('F Y', strtotime($job->start_date)) . ' - ' . ($job->end_date ? date('F Y', strtotime($job->end_date)) : 'Present'),
                'startYear' => $start_year,
                'endYear' => $end_year,
                'type' => 'job',
                'description' => $job->description
            );
            
            $paths[$path_type]['children'][] = $job_entry;
        }
        
        // Create root structure
        $export_data = array(
            'name' => 'Career Journey',
            'startYear' => min(array_column($paths, 'startYear')),
            'children' => array_values($paths)
        );
        
        wp_send_json_success($export_data);
    }
    
    public function ajax_import_json() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cpv_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $json_data = stripslashes($_POST['json_data']);
        $data = json_decode($json_data, true);
        
        if (!$data || !isset($data['children'])) {
            wp_send_json_error('Invalid JSON structure');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'career_progression';
        
        // Delete all existing entries
        $wpdb->query("TRUNCATE TABLE $table_name");
        
        // Process each path
        foreach ($data['children'] as $path) {
            if (!isset($path['children'])) continue;
            
            $path_name = $path['name'];
            $path_color = $path['color'] ?? '#4299e1';
            
            // Process jobs in this path
            $this->process_path_children($path['children'], $path_name, $path_color, $table_name);
        }
        
        wp_send_json_success(array('message' => 'Data imported successfully'));
    }
    
    private function process_path_children($children, $path_name, $path_color, $table_name) {
        global $wpdb;
        
        foreach ($children as $item) {
            if ($item['type'] === 'job' || $item['type'] === 'role') {
                // It's a job or role entry
                $start_date = isset($item['startYear']) ? $item['startYear'] . '-01-01' : date('Y-m-d');
                $end_date = isset($item['endYear']) && $item['endYear'] != date('Y') + 1 ? $item['endYear'] . '-12-31' : null;
                
                $wpdb->insert(
                    $table_name,
                    array(
                        'position' => $item['title'] ?? $item['name'],
                        'company' => $item['name'] ?? '',
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'description' => $item['description'] ?? '',
                        'skills' => json_encode(array()),
                        'achievements' => json_encode(array()),
                        'location' => '',
                        'company_image' => '',
                        'path_type' => $path_name,
                        'path_color' => $path_color
                    )
                );
            } elseif (isset($item['children'])) {
                // It's a nested structure (like Microsoft with multiple roles)
                // Use the parent company name for all child roles
                foreach ($item['children'] as $role) {
                    $start_date = isset($role['startYear']) ? $role['startYear'] . '-01-01' : date('Y-m-d');
                    $end_date = isset($role['endYear']) && $role['endYear'] != date('Y') + 1 ? $role['endYear'] . '-12-31' : null;
                    
                    $wpdb->insert(
                        $table_name,
                        array(
                            'position' => $role['title'] ?? $role['name'],
                            'company' => $item['name'],
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                            'description' => $role['description'] ?? '',
                            'skills' => json_encode(array()),
                            'achievements' => json_encode(array()),
                            'location' => '',
                            'company_image' => '',
                            'path_type' => $path_name,
                            'path_color' => $path_color
                        )
                    );
                }
            }
        }
    }
    
    public function ajax_convert_linkedin_data() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cpv_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $linkedin_json = stripslashes($_POST['linkedin_data']);
        $linkedin_data = json_decode($linkedin_json, true);
        
        if (!$linkedin_data || !is_array($linkedin_data)) {
            wp_send_json_error('Invalid LinkedIn data format');
            return;
        }
        
        // Use the LinkedIn class to parse the data
        $career_data = CPV_LinkedIn::parse_linkedin_export($linkedin_data);
        
        if (!$career_data) {
            wp_send_json_error('Failed to parse LinkedIn data');
            return;
        }
        
        wp_send_json_success($career_data);
    }
}