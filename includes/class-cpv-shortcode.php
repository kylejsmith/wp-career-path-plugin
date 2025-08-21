<?php
/**
 * Shortcode handler class
 */

if (!defined('ABSPATH')) {
    exit;
}

class CPV_Shortcode {
    
    public function init() {
        add_shortcode('career_progression', array($this, 'render_shortcode'));
    }
    
    public function render_shortcode($atts) {
        // Get settings defaults
        $settings = get_option('cpv_settings', array());
        
        // Use settings as defaults
        $default_width = isset($settings['width']) ? $settings['width'] : '1200px';
        $default_height = isset($settings['height']) ? $settings['height'] : '600px';
        $default_theme = isset($settings['theme']) ? $settings['theme'] : 'light';
        
        // Parse attributes
        $atts = shortcode_atts(array(
            'width' => $default_width,
            'height' => $default_height,
            'theme' => $default_theme
        ), $atts, 'career_progression');
        
        // Generate unique ID for this instance
        $chart_id = 'cpv-chart-' . uniqid();
        
        ob_start();
        ?>
        <div class="cpv-container cpv-theme-<?php echo esc_attr($atts['theme']); ?>" data-theme="<?php echo esc_attr($atts['theme']); ?>">
            <div id="<?php echo esc_attr($chart_id); ?>" class="cpv-chart" style="width: <?php 
                $width = $atts['width'];
                if (strpos($width, '%') !== false || strpos($width, 'px') !== false) {
                    echo esc_attr($width);
                } else {
                    echo esc_attr($width) . 'px';
                }
            ?>; height: <?php 
                $height = $atts['height'];
                if (strpos($height, '%') !== false || strpos($height, 'px') !== false) {
                    echo esc_attr($height);
                } else {
                    echo esc_attr($height) . 'px';
                }
            ?>;">
                <div class="cpv-loading">
                    <span class="cpv-spinner"></span>
                    <p><?php _e('Loading career progression...', 'career-progression'); ?></p>
                </div>
            </div>
            <div class="cpv-controls">
                <button class="cpv-btn" data-action="zoom-in"><?php _e('Zoom In', 'career-progression'); ?></button>
                <button class="cpv-btn" data-action="zoom-out"><?php _e('Zoom Out', 'career-progression'); ?></button>
            </div>
            <div class="cpv-tooltip" style="display: none;">
                <div class="cpv-tooltip-content"></div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof initCareerVisualization === 'function') {
                    initCareerVisualization('<?php echo esc_js($chart_id); ?>', <?php echo json_encode($atts); ?>);
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }
}