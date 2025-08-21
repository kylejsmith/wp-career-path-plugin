<div class="wrap">
    <h1><?php _e('Career Progression Settings', 'career-progression'); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('cpv_settings_group'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cpv_theme"><?php _e('Visualization Theme', 'career-progression'); ?></label>
                </th>
                <td>
                    <select id="cpv_theme" name="cpv_settings[theme]">
                        <option value="light" <?php selected($settings['theme'] ?? 'light', 'light'); ?>>
                            <?php _e('Light', 'career-progression'); ?>
                        </option>
                        <option value="dark" <?php selected($settings['theme'] ?? '', 'dark'); ?>>
                            <?php _e('Dark', 'career-progression'); ?>
                        </option>
                        <option value="system" <?php selected($settings['theme'] ?? '', 'system'); ?>>
                            <?php _e('System', 'career-progression'); ?>
                        </option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="cpv_width"><?php _e('Default Width', 'career-progression'); ?></label>
                </th>
                <td>
                    <input type="text" id="cpv_width" name="cpv_settings[width]" 
                           value="<?php echo esc_attr($settings['width'] ?? '1200px'); ?>" 
                           class="regular-text" />
                    <p class="description">
                        <?php _e('Enter width in pixels (e.g., 1200 or 1200px) or percentage (e.g., 100%)', 'career-progression'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="cpv_height"><?php _e('Default Height', 'career-progression'); ?></label>
                </th>
                <td>
                    <input type="text" id="cpv_height" name="cpv_settings[height]" 
                           value="<?php echo esc_attr($settings['height'] ?? '600px'); ?>" 
                           class="regular-text" />
                    <p class="description">
                        <?php _e('Enter height in pixels (e.g., 600 or 600px) or percentage (e.g., 80%)', 'career-progression'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="cpv_date_format"><?php _e('Date Format', 'career-progression'); ?></label>
                </th>
                <td>
                    <select id="cpv_date_format" name="cpv_settings[date_format]">
                        <option value="F Y" <?php selected($settings['date_format'] ?? '', 'F Y'); ?>>
                            <?php echo date('F Y'); ?> - US (January 2024)
                        </option>
                        <option value="M Y" <?php selected($settings['date_format'] ?? '', 'M Y'); ?>>
                            <?php echo date('M Y'); ?> - US Short (Jan 2024)
                        </option>
                        <option value="m/Y" <?php selected($settings['date_format'] ?? '', 'm/Y'); ?>>
                            <?php echo date('m/Y'); ?> - US Numeric (01/2024)
                        </option>
                        <option value="Y-m" <?php selected($settings['date_format'] ?? '', 'Y-m'); ?>>
                            <?php echo date('Y-m'); ?> - ISO (2024-01)
                        </option>
                        <option value="m.Y" <?php selected($settings['date_format'] ?? '', 'm.Y'); ?>>
                            <?php echo date('m.Y'); ?> - European (01.2024)
                        </option>
                        <option value="Y年m月" <?php selected($settings['date_format'] ?? '', 'Y年m月'); ?>>
                            <?php echo date('Y年m月'); ?> - Japanese
                        </option>
                        <option value="Y" <?php selected($settings['date_format'] ?? '', 'Y'); ?>>
                            <?php echo date('Y'); ?> - Year Only
                        </option>
                    </select>
                </td>
            </tr>
            
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <hr style="margin: 40px 0;">
    
    <div class="cpv-preview-section">
        <h2><?php _e('Live Preview', 'career-progression'); ?></h2>
        
        <div style="margin-bottom: 20px;">
            <p style="color: #666; margin-bottom: 10px;">
                <?php _e('Example shortcode usage:', 'career-progression'); ?><br>
                <code>[career_progression]</code> - Uses settings defaults<br>
                <code>[career_progression width="100%" height="800px"]</code> - Full width, 800px height<br>
                <code>[career_progression width="1400px" height="700px"]</code> - Custom pixel dimensions<br>
                <code>[career_progression width="80%" theme="dark"]</code> - 80% width, dark theme
            </p>
            
            <div style="display: flex; align-items: center; gap: 10px;">
                <input type="text" 
                       id="cpv-preview-shortcode" 
                       value="[career_progression]" 
                       style="flex: 1; padding: 8px; font-family: monospace;"
                       placeholder="[career_progression]">
                <button type="button" 
                        id="cpv-update-preview" 
                        class="button button-primary">
                    <?php _e('Update Preview', 'career-progression'); ?>
                </button>
            </div>
        </div>
        
        <hr style="margin: 20px 0;">
        
        <div id="cpv-settings-preview" style="min-height: 400px; border: 1px solid #ddd; padding: 20px; background: #fafafa; overflow-x: auto;">
            <!-- Preview will be rendered here -->
        </div>
    </div>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        let previewContainer = null;
        
        // Function to parse shortcode attributes
        function parseShortcode(shortcode) {
            // Get defaults from settings fields
            const defaultWidth = $('#cpv_width').val() || '1200px';
            const defaultHeight = $('#cpv_height').val() || '600px';
            
            const attrs = {
                type: 'tree',
                width: defaultWidth,
                height: defaultHeight,
                theme: $('#cpv_theme').val() || 'light'
            };
            
            // Extract attributes from shortcode
            const regex = /(\w+)=["']([^"']+)["']/g;
            let match;
            while ((match = regex.exec(shortcode)) !== null) {
                attrs[match[1]] = match[2];
            }
            
            return attrs;
        }
        
        // Function to render preview
        function renderPreview() {
            const shortcode = $('#cpv-preview-shortcode').val();
            const attrs = parseShortcode(shortcode);
            
            // Apply current settings from dropdown (override what was in shortcode)
            attrs.theme = $('#cpv_theme').val() || 'light';
            
            // Clear previous preview
            const widthStyle = attrs.width.includes('%') || attrs.width.includes('px') ? attrs.width : attrs.width + 'px';
            const heightStyle = attrs.height.includes('%') || attrs.height.includes('px') ? attrs.height : attrs.height + 'px';
            
            $('#cpv-settings-preview').html(`
                <div class="cpv-container cpv-theme-${attrs.theme}" style="width: ${widthStyle}; margin: 0 auto;">
                    <div id="cpv-preview-chart" class="cpv-chart" style="width: 100%; height: ${heightStyle};">
                        <div class="cpv-loading">
                            <span class="cpv-spinner"></span>
                            <p><?php _e('Loading preview...', 'career-progression'); ?></p>
                        </div>
                    </div>
                    <div class="cpv-controls">
                        <button class="cpv-btn" data-action="zoom-in"><?php _e('Zoom In', 'career-progression'); ?></button>
                        <button class="cpv-btn" data-action="zoom-out"><?php _e('Zoom Out', 'career-progression'); ?></button>
                    </div>
                </div>
            `);
            
            // Initialize visualization
            if (typeof initCareerVisualization === 'function') {
                setTimeout(() => {
                    initCareerVisualization('cpv-preview-chart', attrs);
                }, 100);
            }
        }
        
        // Initial render
        renderPreview();
        
        // Update preview on button click
        $('#cpv-update-preview').on('click', renderPreview);
        
        // Update preview on enter key in shortcode field
        $('#cpv-preview-shortcode').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                renderPreview();
            }
        });
        
        // Auto-update preview when settings change
        $('#cpv_theme, #cpv_date_format, #cpv_width, #cpv_height').on('change', function() {
            renderPreview();
        });
        
        // Auto-format width/height fields when user enters just a number
        $('#cpv_width, #cpv_height').on('blur', function() {
            let value = $(this).val().trim();
            
            // If it's just a number (no px or %), add px
            if (value && /^\d+$/.test(value)) {
                $(this).val(value + 'px');
            }
        });
        
        // Also update on form submit to ensure proper formatting
        $('form').on('submit', function() {
            $('#cpv_width, #cpv_height').each(function() {
                let value = $(this).val().trim();
                if (value && /^\d+$/.test(value)) {
                    $(this).val(value + 'px');
                }
            });
        });
    });
    </script>
</div>