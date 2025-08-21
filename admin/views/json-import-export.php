<div class="wrap">
    <h1><?php _e('Import/Export Career Data', 'career-progression'); ?></h1>
    
    <script type="text/javascript">
        var cpv_admin_nonce = '<?php echo wp_create_nonce('cpv_admin_nonce'); ?>';
    </script>
    
    <div class="cpv-json-container">
        <!-- Export Section -->
        <div class="cpv-form-section">
            <h2><?php _e('Export Current Data', 'career-progression'); ?></h2>
            <p><?php _e('Export your current career progression data as JSON format.', 'career-progression'); ?></p>
            <button type="button" id="cpv-export-json" class="button button-primary">
                <?php _e('Export to JSON', 'career-progression'); ?>
            </button>
            <div id="export-result" style="margin-top: 20px; display: none;">
                <h3><?php _e('Exported JSON:', 'career-progression'); ?></h3>
                <textarea id="exported-json" rows="15" style="width: 100%; font-family: monospace; font-size: 12px;" readonly></textarea>
                <button type="button" id="cpv-copy-json" class="button" style="margin-top: 10px;">
                    <?php _e('Copy to Clipboard', 'career-progression'); ?>
                </button>
            </div>
        </div>
        
        <!-- Import Section -->
        <div class="cpv-form-section" style="margin-top: 30px;">
            <h2><?php _e('Import JSON Data', 'career-progression'); ?></h2>
            
            <div class="notice notice-warning" style="margin: 20px 0;">
                <p><strong><?php _e('⚠️ WARNING:', 'career-progression'); ?></strong> <?php _e('Importing JSON data will DELETE ALL existing career entries and replace them with the imported data. This action cannot be undone!', 'career-progression'); ?></p>
            </div>
            
            <p><?php _e('Paste your career progression JSON data below:', 'career-progression'); ?></p>
            <textarea id="import-json" rows="15" style="width: 100%; font-family: monospace; font-size: 12px;" placeholder='<?php _e('Paste your JSON here...', 'career-progression'); ?>'></textarea>
            
            <div style="margin-top: 15px;">
                <button type="button" id="cpv-validate-json" class="button">
                    <?php _e('Validate JSON', 'career-progression'); ?>
                </button>
                <button type="button" id="cpv-import-json" class="button button-primary" disabled>
                    <?php _e('Import JSON (Replace All Data)', 'career-progression'); ?>
                </button>
            </div>
            
            <div id="validation-result" style="margin-top: 15px; display: none;"></div>
        </div>
        
        <!-- Schema Example Section -->
        <div class="cpv-form-section" style="margin-top: 30px;">
            <h2><?php _e('JSON Schema Example', 'career-progression'); ?></h2>
            <p><?php _e('Here\'s a sample JSON structure showing 2 paths with 5 jobs total:', 'career-progression'); ?></p>
            
            <pre style="background: #f0f0f0; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px;"><code>{
  "name": "Career Journey",
  "startYear": 2015,
  "children": [
    {
      "name": "Engineering Path",
      "type": "path",
      "color": "#48bb78",
      "startYear": 2015,
      "description": "Software engineering journey",
      "children": [
        {
          "name": "TechCorp",
          "title": "Junior Developer",
          "dates": "January 2015 – June 2017",
          "startYear": 2015,
          "endYear": 2017,
          "type": "job",
          "description": "Built web applications using React and Node.js"
        },
        {
          "name": "StartupXYZ",
          "title": "Senior Developer",
          "dates": "July 2017 – December 2019",
          "startYear": 2017,
          "endYear": 2019,
          "type": "job",
          "description": "Led development of mobile apps and APIs"
        },
        {
          "name": "BigTech Inc",
          "title": "Tech Lead",
          "dates": "January 2020 – Present",
          "startYear": 2020,
          "endYear": 2025,
          "type": "job",
          "description": "Leading a team of 8 engineers on cloud platform"
        }
      ]
    },
    {
      "name": "Management Path",
      "type": "path",
      "color": "#9f7aea",
      "startYear": 2020,
      "description": "Transition to management",
      "children": [
        {
          "name": "BigTech Inc",
          "type": "job",
          "startYear": 2020,
          "endYear": 2025,
          "children": [
            {
              "title": "Engineering Manager",
              "dates": "January 2020 – June 2022",
              "startYear": 2020,
              "endYear": 2022,
              "type": "role",
              "description": "Managed frontend engineering team"
            },
            {
              "title": "Senior Engineering Manager",
              "dates": "July 2022 – Present",
              "startYear": 2022,
              "endYear": 2025,
              "type": "role",
              "description": "Managing multiple engineering teams"
            }
          ]
        }
      ]
    }
  ]
}</code></pre>
            
            <h3><?php _e('Schema Notes:', 'career-progression'); ?></h3>
            <ul style="list-style-type: disc; margin-left: 20px;">
                <li><strong>Root object:</strong> Must have "name", "startYear", and "children" array</li>
                <li><strong>Path objects:</strong> Must have "name", "type": "path", "color", "startYear", and "children" array</li>
                <li><strong>Job objects:</strong> Must have "name" (company), "title", "dates", "startYear", "endYear", "type": "job", and "description"</li>
                <li><strong>Nested roles:</strong> Companies can have "children" array with multiple role objects (type: "role")</li>
                <li><strong>Colors:</strong> Use hex color codes for path colors (e.g., "#48bb78")</li>
                <li><strong>Years:</strong> Use 4-digit years. For current positions, use next year or future year</li>
            </ul>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Export functionality
    $('#cpv-export-json').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cpv_export_json',
                nonce: cpv_admin_nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#exported-json').val(JSON.stringify(response.data, null, 2));
                    $('#export-result').show();
                } else {
                    alert('Error exporting data');
                }
            }
        });
    });
    
    // Copy to clipboard
    $('#cpv-copy-json').on('click', function() {
        $('#exported-json').select();
        document.execCommand('copy');
        $(this).text('Copied!');
        setTimeout(() => {
            $(this).text('Copy to Clipboard');
        }, 2000);
    });
    
    // Validate JSON
    $('#cpv-validate-json').on('click', function() {
        const jsonText = $('#import-json').val();
        
        if (!jsonText.trim()) {
            showValidationResult('error', 'Please paste JSON data first');
            return;
        }
        
        try {
            const data = JSON.parse(jsonText);
            
            // Validate structure
            if (!data.name || !data.children || !Array.isArray(data.children)) {
                showValidationResult('error', 'Invalid structure: Root must have name and children array');
                return;
            }
            
            let jobCount = 0;
            let pathCount = data.children.length;
            
            // Count jobs
            data.children.forEach(path => {
                if (path.children && Array.isArray(path.children)) {
                    path.children.forEach(item => {
                        if (item.type === 'job') {
                            jobCount++;
                        } else if (item.children && Array.isArray(item.children)) {
                            jobCount += item.children.length;
                        }
                    });
                }
            });
            
            showValidationResult('success', `Valid JSON! Found ${pathCount} paths with ${jobCount} total positions.`);
            $('#cpv-import-json').prop('disabled', false);
            
        } catch (e) {
            showValidationResult('error', 'Invalid JSON: ' + e.message);
            $('#cpv-import-json').prop('disabled', true);
        }
    });
    
    // Import JSON
    $('#cpv-import-json').on('click', function() {
        if (!confirm('⚠️ WARNING: This will DELETE ALL existing career entries and replace them with the imported data.\n\nThis action cannot be undone. Are you sure you want to proceed?')) {
            return;
        }
        
        const jsonText = $('#import-json').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cpv_import_json',
                json_data: jsonText,
                nonce: cpv_admin_nonce
            },
            success: function(response) {
                if (response.success) {
                    showValidationResult('success', 'Data imported successfully! Redirecting...');
                    setTimeout(() => {
                        window.location.href = 'admin.php?page=career-progression';
                    }, 2000);
                } else {
                    showValidationResult('error', 'Import failed: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                showValidationResult('error', 'Import failed: Server error');
            }
        });
    });
    
    function showValidationResult(type, message) {
        const resultDiv = $('#validation-result');
        const className = type === 'success' ? 'notice-success' : 'notice-error';
        
        resultDiv.html(`<div class="notice ${className}"><p>${message}</p></div>`);
        resultDiv.show();
    }
    
    // Auto-validate on paste
    $('#import-json').on('paste', function() {
        setTimeout(() => {
            $('#cpv-validate-json').click();
        }, 100);
    });
});
</script>