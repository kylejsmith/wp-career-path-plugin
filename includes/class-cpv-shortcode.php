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
        // Parse attributes
        $atts = shortcode_atts(array(
            'width' => '1200',
            'height' => '600',
            'theme' => 'professional'
        ), $atts, 'career_progression');
        
        // Generate unique ID for this instance
        $chart_id = 'cpv-chart-' . uniqid();
        
        ob_start();
        ?>
        <div class="cpv-container cpv-theme-<?php echo esc_attr($atts['theme']); ?>" data-theme="<?php echo esc_attr($atts['theme']); ?>">
            <div id="<?php echo esc_attr($chart_id); ?>" class="cpv-chart" style="width: <?php echo strpos($atts['width'], '%') !== false ? esc_attr($atts['width']) : esc_attr($atts['width']) . 'px'; ?>; height: <?php echo strpos($atts['height'], '%') !== false ? esc_attr($atts['height']) : esc_attr($atts['height']) . 'px'; ?>;">
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