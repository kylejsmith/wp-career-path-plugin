<div class="wrap">
    <h1><?php _e('Import from LinkedIn', 'career-progression'); ?></h1>
    
    <script type="text/javascript">
        var cpv_admin_nonce = '<?php echo wp_create_nonce('cpv_admin_nonce'); ?>';
    </script>
    
    <div class="cpv-linkedin-container">
        
        <!-- Simple Import Options -->
        <div class="cpv-form-section">
            <h2><?php _e('How to Import Your LinkedIn Career History', 'career-progression'); ?></h2>
            
            <div class="notice notice-info">
                <p><strong><?php _e('Good news!', 'career-progression'); ?></strong> <?php _e('LinkedIn provides all your career history data for free. It takes about 10 minutes.', 'career-progression'); ?></p>
            </div>
            
            <h3><?php _e('📋 Step 1: Request Your LinkedIn Data', 'career-progression'); ?></h3>
            <div style="background: #f9f9f9; padding: 20px; border-left: 4px solid #0073aa; margin: 20px 0;">
                <ol style="line-height: 2.2; margin: 0;">
                    <li><strong><?php _e('Sign in to LinkedIn', 'career-progression'); ?></strong> <?php _e('and click your profile picture in the top right', 'career-progression'); ?></li>
                    <li><?php _e('Click', 'career-progression'); ?> <strong>"Settings & Privacy"</strong></li>
                    <li><?php _e('In the left sidebar, click', 'career-progression'); ?> <strong>"Data privacy"</strong></li>
                    <li><?php _e('Find and click', 'career-progression'); ?> <strong>"Get a copy of your data"</strong></li>
                    <li><?php _e('Important: Select', 'career-progression'); ?> <strong>"Want something in particular"</strong> <?php _e('(not "The works")', 'career-progression'); ?></li>
                    <li><?php _e('Check the box for', 'career-progression'); ?> <strong>"Positions"</strong> <?php _e('only', 'career-progression'); ?></li>
                    <li><?php _e('Click', 'career-progression'); ?> <strong>"Request archive"</strong></li>
                </ol>
                
                <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 4px;">
                    <strong>⏱️ <?php _e('Wait Time:', 'career-progression'); ?></strong> <?php _e('LinkedIn will email you within 10 minutes with a download link', 'career-progression'); ?>
                </div>
            </div>
            
            <p style="text-align: center; margin: 20px 0;">
                <a href="https://www.linkedin.com/psettings/member-data" target="_blank" class="button button-primary button-hero">
                    <?php _e('🚀 Go to LinkedIn Data Export Page', 'career-progression'); ?>
                </a>
            </p>
            
            <h3><?php _e('📥 Step 2: Download and Extract', 'career-progression'); ?></h3>
            <div style="background: #f9f9f9; padding: 20px; border-left: 4px solid #46b450; margin: 20px 0;">
                <ol style="line-height: 2.2; margin: 0;">
                    <li><?php _e('Check your email for', 'career-progression'); ?> <strong>"Your LinkedIn data is ready"</strong></li>
                    <li><?php _e('Click the download link in the email', 'career-progression'); ?></li>
                    <li><?php _e('Save the ZIP file to your computer', 'career-progression'); ?></li>
                    <li><strong><?php _e('Extract/Unzip the file', 'career-progression'); ?></strong> <?php _e('(double-click on Mac/Windows)', 'career-progression'); ?></li>
                    <li><?php _e('Look for', 'career-progression'); ?> <strong style="color: #d63638;">Positions.json</strong> <?php _e('in the extracted folder', 'career-progression'); ?></li>
                </ol>
                
                <div style="margin-top: 15px; padding: 10px; background: #d1ecf1; border-radius: 4px;">
                    <strong>💡 <?php _e('Tip:', 'career-progression'); ?></strong> <?php _e('The file you need is called', 'career-progression'); ?> <code style="background: white; padding: 2px 6px;">Positions.json</code> - <?php _e('ignore all other files', 'career-progression'); ?>
                </div>
            </div>
        </div>
        
        <div class="cpv-form-section">
            <h3><?php _e('📤 Step 3: Upload Your Positions.json File', 'career-progression'); ?></h3>
            
            <div style="background: #f0f8ff; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="margin: 20px 0;">
                    <label for="linkedin-file-upload" class="button button-primary button-large" style="font-size: 16px; padding: 12px 24px;">
                        <?php _e('📁 Click to Select Positions.json', 'career-progression'); ?>
                    </label>
                    <input type="file" id="linkedin-file-upload" accept=".json" style="display: none;">
                    <div id="file-name" style="margin-top: 10px; font-weight: bold; color: #46b450;"></div>
                </div>
                
                <p style="margin: 20px 0; color: #666;">— <?php _e('OR', 'career-progression'); ?> —</p>
                
                <p><?php _e('Open Positions.json in a text editor and paste its contents here:', 'career-progression'); ?></p>
                <textarea id="linkedin-json" rows="8" style="width: 100%; font-family: monospace; font-size: 12px; border: 2px dashed #ccc; border-radius: 4px;" 
                          placeholder='[&#10;  {&#10;    "companyName": "Example Company",&#10;    "title": "Job Title",&#10;    ...&#10;  }&#10;]'></textarea>
            </div>
            
            <div style="margin-top: 15px;">
                <button type="button" id="preview-linkedin-import" class="button button-large">
                    <?php _e('Preview Import', 'career-progression'); ?>
                </button>
                <button type="button" id="import-linkedin-data" class="button button-primary button-large" disabled>
                    <?php _e('Import Career History', 'career-progression'); ?>
                </button>
            </div>
            
            <!-- Preview Section -->
            <div id="linkedin-preview" style="display: none; margin-top: 30px;">
                <h3><?php _e('Preview - Your Career Data Will Look Like This:', 'career-progression'); ?></h3>
                <div class="notice notice-warning">
                    <p><?php _e('⚠️ Importing will REPLACE all existing career entries. Make sure to export your current data first if needed.', 'career-progression'); ?></p>
                </div>
                <div id="preview-summary" style="margin: 15px 0; padding: 15px; background: #f0f0f1; border-radius: 5px;"></div>
                <textarea id="preview-json" rows="10" style="width: 100%; font-family: monospace; font-size: 12px;" readonly></textarea>
            </div>
        </div>
        
        <!-- Troubleshooting Help -->
        <div class="cpv-form-section" style="margin-top: 40px; padding: 20px; background: #fff8e1; border-radius: 8px;">
            <h3><?php _e('🔧 Troubleshooting', 'career-progression'); ?></h3>
            
            <details style="margin: 10px 0;">
                <summary style="cursor: pointer; font-weight: bold; padding: 10px; background: white; border-radius: 4px;">
                    <?php _e('Can\'t find Positions.json?', 'career-progression'); ?>
                </summary>
                <div style="padding: 15px; margin-top: 10px; background: white; border-radius: 4px;">
                    <ul style="line-height: 1.8;">
                        <li><?php _e('Make sure you selected "Want something in particular" not "The works"', 'career-progression'); ?></li>
                        <li><?php _e('Check that you selected "Positions" when requesting data', 'career-progression'); ?></li>
                        <li><?php _e('The file is in the root of the extracted ZIP folder', 'career-progression'); ?></li>
                        <li><?php _e('File name is exactly', 'career-progression'); ?> <code>Positions.json</code> <?php _e('(capital P)', 'career-progression'); ?></li>
                    </ul>
                </div>
            </details>
            
            <details style="margin: 10px 0;">
                <summary style="cursor: pointer; font-weight: bold; padding: 10px; background: white; border-radius: 4px;">
                    <?php _e('Import not working?', 'career-progression'); ?>
                </summary>
                <div style="padding: 15px; margin-top: 10px; background: white; border-radius: 4px;">
                    <ul style="line-height: 1.8;">
                        <li><?php _e('Make sure you\'re using Positions.json, not Profile.json or other files', 'career-progression'); ?></li>
                        <li><?php _e('The file should start with', 'career-progression'); ?> <code>[</code> <?php _e('and end with', 'career-progression'); ?> <code>]</code></li>
                        <li><?php _e('If copying/pasting, select ALL text (Ctrl+A or Cmd+A) in the file', 'career-progression'); ?></li>
                        <li><?php _e('Don\'t modify the JSON content', 'career-progression'); ?></li>
                    </ul>
                </div>
            </details>
            
            <details style="margin: 10px 0;">
                <summary style="cursor: pointer; font-weight: bold; padding: 10px; background: white; border-radius: 4px;">
                    <?php _e('Haven\'t received the email from LinkedIn?', 'career-progression'); ?>
                </summary>
                <div style="padding: 15px; margin-top: 10px; background: white; border-radius: 4px;">
                    <ul style="line-height: 1.8;">
                        <li><?php _e('Check your spam/junk folder', 'career-progression'); ?></li>
                        <li><?php _e('It usually takes 5-10 minutes, but can take up to 24 hours', 'career-progression'); ?></li>
                        <li><?php _e('Try requesting again - you can request multiple times', 'career-progression'); ?></li>
                        <li><?php _e('Make sure your email is verified on LinkedIn', 'career-progression'); ?></li>
                    </ul>
                </div>
            </details>
        </div>
        
        <!-- Alternative Manual Entry -->
        <div class="cpv-form-section" style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #ddd;">
            <h3><?php _e('Alternative: Manual Entry', 'career-progression'); ?></h3>
            <p><?php _e('If you prefer not to export your data or have issues with the import:', 'career-progression'); ?></p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=career-progression-add'); ?>" class="button button-large">
                    <?php _e('✏️ Add Career Entries Manually', 'career-progression'); ?>
                </a>
            </p>
        </div>
        
        <!-- Sample Format Reference -->
        <div class="cpv-form-section">
            <h3><?php _e('LinkedIn Data Format (Reference)', 'career-progression'); ?></h3>
            <details>
                <summary style="cursor: pointer; font-weight: bold;"><?php _e('Click to see example format', 'career-progression'); ?></summary>
                <pre style="background: #f0f0f0; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; margin-top: 10px;"><code>[
  {
    "companyName": "GoDaddy",
    "title": "Principal Product Manager",
    "description": "Leading growth and monetization initiatives",
    "startDate": {
      "month": 3,
      "year": 2023
    },
    "endDate": null,
    "location": "Remote"
  },
  {
    "companyName": "Microsoft Corporation",
    "title": "Senior Consultant",
    "startDate": {
      "month": 11,
      "year": 1998
    },
    "endDate": {
      "month": 4,
      "year": 2000
    },
    "location": "Redmond, WA"
  }
]</code></pre>
            </details>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // File upload handler
    $('#linkedin-file-upload').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Check if it's the right file
        if (file.name === 'Positions.json') {
            $('#file-name').html(`✅ <span style="color: #46b450;">Perfect! "${file.name}" selected</span>`);
        } else if (file.name.toLowerCase().includes('position')) {
            $('#file-name').html(`✓ "${file.name}" selected`);
        } else {
            $('#file-name').html(`⚠️ <span style="color: #d63638;">"${file.name}" selected - Make sure this is your Positions.json file</span>`);
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#linkedin-json').val(e.target.result);
            // Auto-trigger preview
            $('#preview-linkedin-import').click();
        };
        reader.readAsText(file);
    });
    
    // Preview LinkedIn import
    $('#preview-linkedin-import').on('click', function() {
        const jsonData = $('#linkedin-json').val();
        
        if (!jsonData.trim()) {
            alert('Please upload or paste your LinkedIn Positions.json file first');
            return;
        }
        
        try {
            // Parse to validate JSON
            const data = JSON.parse(jsonData);
            
            // Check if it's array format (LinkedIn export)
            if (!Array.isArray(data)) {
                alert('Invalid format. Please use the Positions.json file from LinkedIn export.');
                return;
            }
            
            // Create summary
            const jobCount = data.length;
            const companies = [...new Set(data.map(j => j.companyName))];
            const years = data.map(j => j.startDate?.year).filter(y => y);
            const minYear = Math.min(...years);
            const maxYear = Math.max(...years);
            
            $('#preview-summary').html(`
                <strong>Summary:</strong><br>
                • ${jobCount} positions found<br>
                • ${companies.length} companies<br>
                • Career span: ${minYear} - ${maxYear || 'Present'}<br>
                • Companies: ${companies.slice(0, 5).join(', ')}${companies.length > 5 ? '...' : ''}
            `);
            
            // Convert to our format
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cpv_convert_linkedin_data',
                    linkedin_data: jsonData,
                    nonce: cpv_admin_nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#preview-json').val(JSON.stringify(response.data, null, 2));
                        $('#linkedin-preview').show();
                        $('#import-linkedin-data').prop('disabled', false).data('career-data', response.data);
                        
                        // Scroll to preview
                        $('html, body').animate({
                            scrollTop: $('#linkedin-preview').offset().top - 100
                        }, 500);
                    } else {
                        alert('Error processing LinkedIn data: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Error processing LinkedIn data');
                }
            });
        } catch (e) {
            alert('Invalid JSON format. Please make sure you\'re using the Positions.json file from LinkedIn.');
        }
    });
    
    // Import LinkedIn data
    $('#import-linkedin-data').on('click', function() {
        if (!confirm('This will REPLACE all existing career entries with your LinkedIn data.\n\nAre you sure you want to proceed?')) {
            return;
        }
        
        const careerData = $(this).data('career-data');
        
        $(this).prop('disabled', true).text('Importing...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cpv_import_json',
                json_data: JSON.stringify(careerData),
                nonce: cpv_admin_nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Success! Your LinkedIn career history has been imported.');
                    window.location.href = 'admin.php?page=career-progression';
                } else {
                    alert('Import failed: ' + (response.data || 'Unknown error'));
                    $('#import-linkedin-data').prop('disabled', false).text('Import Career History');
                }
            },
            error: function() {
                alert('Import failed: Server error');
                $('#import-linkedin-data').prop('disabled', false).text('Import Career History');
            }
        });
    });
    
    // Auto-validate on paste
    $('#linkedin-json').on('paste', function() {
        setTimeout(() => {
            $('#preview-linkedin-import').click();
        }, 100);
    });
});
</script>