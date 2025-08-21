<div class="wrap">
    <h1><?php echo $entry ? __('Edit Career Entry', 'career-progression') : __('Add New Career Entry', 'career-progression'); ?></h1>
    
    <script type="text/javascript">
        var cpv_admin_nonce = '<?php echo wp_create_nonce('cpv_admin_nonce'); ?>';
    </script>
    
    <form id="cpv-entry-form" method="post">
        <?php wp_nonce_field('cpv_admin_nonce', 'cpv_nonce'); ?>
        <?php if ($entry): ?>
            <input type="hidden" name="entry_id" value="<?php echo $entry->id; ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="position"><?php _e('Position Title', 'career-progression'); ?> *</label>
                </th>
                <td>
                    <input type="text" id="position" name="position" class="regular-text" required 
                           value="<?php echo $entry ? esc_attr($entry->position) : ''; ?>">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="company"><?php _e('Company', 'career-progression'); ?> *</label>
                </th>
                <td>
                    <input type="text" id="company" name="company" class="regular-text" required
                           value="<?php echo $entry ? esc_attr($entry->company) : ''; ?>">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="location"><?php _e('Location', 'career-progression'); ?></label>
                </th>
                <td>
                    <input type="text" id="location" name="location" class="regular-text"
                           value="<?php echo $entry ? esc_attr($entry->location) : ''; ?>">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="path_type"><?php _e('Career Path', 'career-progression'); ?> *</label>
                </th>
                <td>
                    <?php
                    // Get existing paths from database
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'career_progression';
                    $existing_paths = $wpdb->get_col("SELECT DISTINCT path_type FROM $table_name WHERE path_type IS NOT NULL ORDER BY path_type");
                    
                    // Common career paths for the modal - 125+ options
                    $common_paths = array(
                        'Technology & IT' => array(
                            'Software Development', 'Web Development', 'Mobile Development', 'DevOps', 'Data Science', 
                            'Data Analytics', 'Cybersecurity', 'IT Support', 'Cloud Architecture', 'Machine Learning', 
                            'AI Engineering', 'QA/Testing', 'Database Administration', 'Network Engineering', 'Systems Administration',
                            'Technical Support', 'IT Management'
                        ),
                        'Healthcare & Medical' => array(
                            'Nursing', 'Medical Practice', 'Surgery', 'Pharmacy', 'Physical Therapy', 
                            'Occupational Therapy', 'Dental Practice', 'Mental Health Counseling', 'Medical Research', 
                            'Healthcare Administration', 'Medical Technology', 'Radiology', 'Laboratory Science', 
                            'Emergency Medical Services', 'Home Healthcare', 'Veterinary Medicine'
                        ),
                        'Trades & Construction' => array(
                            'Electrician', 'Plumbing', 'Carpentry', 'HVAC Technician', 'Welding', 
                            'Masonry', 'Roofing', 'Painting & Decorating', 'Flooring Installation', 'Drywall Installation',
                            'Construction Management', 'Heavy Equipment Operation', 'Landscaping', 'Concrete Work',
                            'Glazing', 'Insulation', 'Scaffolding', 'Demolition'
                        ),
                        'Manufacturing & Industrial' => array(
                            'Manufacturing Operations', 'Machine Operation', 'Quality Control', 'Industrial Maintenance',
                            'Tool & Die Making', 'Sheet Metal Work', 'Assembly Line Work', 'Warehouse Management',
                            'Forklift Operation', 'Inventory Management', 'Production Planning', 'Plant Management',
                            'Packaging Operations', 'Materials Handling'
                        ),
                        'Business & Management' => array(
                            'Business Analysis', 'Product Management', 'Project Management', 'Operations Management',
                            'General Management', 'Executive Leadership', 'Strategy Consulting', 'Management Consulting',
                            'Business Development', 'Entrepreneurship', 'Franchise Management', 'Small Business Owner',
                            'Supply Chain Management', 'Logistics Management'
                        ),
                        'Sales & Customer Service' => array(
                            'Sales Representative', 'Account Management', 'Sales Engineering', 'Customer Success',
                            'Retail Sales', 'Inside Sales', 'Outside Sales', 'Sales Management', 'Customer Service',
                            'Call Center Operations', 'Technical Sales', 'Real Estate Sales', 'Insurance Sales',
                            'Pharmaceutical Sales', 'B2B Sales', 'B2C Sales'
                        ),
                        'Finance & Accounting' => array(
                            'Accounting', 'Bookkeeping', 'Financial Analysis', 'Investment Banking', 'Commercial Banking',
                            'Risk Management', 'Actuarial Science', 'Financial Planning', 'Tax Preparation',
                            'Auditing', 'Treasury Management', 'Credit Analysis', 'Mortgage Banking', 'Wealth Management'
                        ),
                        'Marketing & Communications' => array(
                            'Digital Marketing', 'Content Marketing', 'Brand Marketing', 'Social Media Management',
                            'SEO/SEM', 'Marketing Analytics', 'Public Relations', 'Communications', 'Advertising',
                            'Market Research', 'Email Marketing', 'Event Marketing', 'Product Marketing', 'Growth Marketing'
                        ),
                        'Education & Training' => array(
                            'Teaching', 'Special Education', 'Educational Administration', 'School Counseling',
                            'Curriculum Development', 'Corporate Training', 'Instructional Design', 'Tutoring',
                            'Early Childhood Education', 'Higher Education', 'Vocational Training', 'Library Services',
                            'Educational Technology', 'Academic Research'
                        ),
                        'Creative & Design' => array(
                            'Graphic Design', 'Web Design', 'UX/UI Design', 'Product Design', 'Interior Design',
                            'Fashion Design', 'Animation', 'Video Production', 'Photography', 'Film Production',
                            'Music Production', 'Writing/Journalism', 'Content Creation', 'Game Design', 'Art Direction'
                        ),
                        'Transportation & Logistics' => array(
                            'Truck Driving', 'Delivery Services', 'Aviation/Pilot', 'Flight Attendant', 'Railroad Operations',
                            'Maritime/Shipping', 'Logistics Coordination', 'Dispatch Operations', 'Transportation Planning',
                            'Fleet Management', 'Courier Services', 'Moving Services', 'Public Transit Operation'
                        ),
                        'Hospitality & Food Service' => array(
                            'Restaurant Management', 'Culinary Arts', 'Chef/Cook', 'Bartending', 'Food Service',
                            'Hotel Management', 'Event Planning', 'Catering', 'Tourism', 'Travel Planning',
                            'Hospitality Management', 'Sommelier', 'Bakery/Pastry'
                        ),
                        'Public Service & Government' => array(
                            'Law Enforcement', 'Firefighting', 'Military Service', 'Public Administration',
                            'Social Work', 'Emergency Management', 'Urban Planning', 'Environmental Services',
                            'Parks & Recreation', 'Code Enforcement', 'Court Administration', 'Corrections'
                        ),
                        'Legal & Compliance' => array(
                            'Attorney/Lawyer', 'Paralegal', 'Legal Secretary', 'Compliance Officer', 'Contract Management',
                            'Intellectual Property', 'Corporate Law', 'Criminal Law', 'Family Law', 'Immigration Law',
                            'Legal Research', 'Court Reporting'
                        ),
                        'Agriculture & Natural Resources' => array(
                            'Farming', 'Ranching', 'Agricultural Management', 'Forestry', 'Conservation',
                            'Wildlife Management', 'Fishing/Commercial Fishing', 'Horticulture', 'Agricultural Science',
                            'Environmental Science', 'Mining Operations', 'Oil & Gas Operations'
                        ),
                        'Personal Services' => array(
                            'Hair Styling/Cosmetology', 'Massage Therapy', 'Personal Training', 'Life Coaching',
                            'Pet Grooming', 'Housekeeping', 'Childcare Services', 'Elder Care', 'Personal Assistant',
                            'Esthetics/Skincare', 'Nail Technology', 'Barber Services'
                        ),
                        'Science & Research' => array(
                            'Research Science', 'Laboratory Work', 'Clinical Research', 'Biotechnology',
                            'Environmental Science', 'Chemistry', 'Physics', 'Biology', 'Geology',
                            'Marine Biology', 'Astronomy', 'Data Science Research'
                        ),
                        'Sports & Recreation' => array(
                            'Professional Athletics', 'Sports Coaching', 'Sports Management', 'Recreation Management',
                            'Fitness Instruction', 'Sports Medicine', 'Athletic Training', 'Sports Analytics',
                            'Outdoor Recreation', 'Adventure Tourism'
                        ),
                        'Real Estate & Property' => array(
                            'Real Estate Agent', 'Property Management', 'Real Estate Development', 'Appraisal',
                            'Property Inspection', 'Facilities Management', 'Building Management', 'Leasing Agent'
                        ),
                        'Other Specialized' => array(
                            'Entrepreneurship', 'Freelance/Consulting', 'Non-Profit Management', 'Volunteer Coordination',
                            'Fundraising', 'Grant Writing', 'Translation/Interpretation', 'Sign Language Interpretation',
                            'Renewable Energy', 'Sustainability', 'Drone Operations', 'Security Services',
                            'Investigation Services', 'Locksmith Services', 'Appliance Repair', 'Pest Control'
                        )
                    );
                    ?>
                    <select id="path_type_select" class="regular-text">
                        <option value=""><?php _e('-- Select a path --', 'career-progression'); ?></option>
                        <?php foreach ($existing_paths as $path): ?>
                            <option value="<?php echo esc_attr($path); ?>" <?php echo ($entry && $entry->path_type == $path) ? 'selected' : ''; ?>>
                                <?php echo esc_html($path); ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (count($existing_paths) > 0): ?>
                            <option disabled>──────────</option>
                        <?php endif; ?>
                        <option value="__add_new__"><?php _e('Add new...', 'career-progression'); ?></option>
                    </select>
                    <input type="hidden" id="path_type" name="path_type" value="<?php echo $entry ? esc_attr($entry->path_type) : ''; ?>" required>
                    <p class="description"><?php _e('Select an existing path or add a new one', 'career-progression'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="company_image"><?php _e('Company Logo/Image URL', 'career-progression'); ?></label>
                </th>
                <td>
                    <input type="url" id="company_image" name="company_image" class="large-text"
                           value="<?php echo $entry && isset($entry->company_image) ? esc_attr($entry->company_image) : ''; ?>"
                           placeholder="https://example.com/logo.png">
                    <p class="description"><?php _e('URL to company logo or image (optional)', 'career-progression'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="start_date"><?php _e('Start Date', 'career-progression'); ?> *</label>
                </th>
                <td>
                    <input type="date" id="start_date" name="start_date" required
                           value="<?php echo $entry ? esc_attr($entry->start_date) : ''; ?>">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="end_date"><?php _e('End Date', 'career-progression'); ?></label>
                </th>
                <td>
                    <input type="date" id="end_date" name="end_date"
                           value="<?php echo $entry ? esc_attr($entry->end_date) : ''; ?>">
                    <p class="description"><?php _e('Leave empty if this is your current position', 'career-progression'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="description"><?php _e('Job Description', 'career-progression'); ?></label>
                </th>
                <td>
                    <textarea id="description" name="description" rows="5" cols="50" class="large-text"><?php echo $entry ? esc_textarea($entry->description) : ''; ?></textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="skills"><?php _e('Skills', 'career-progression'); ?></label>
                </th>
                <td>
                    <input type="text" id="skills" name="skills" class="large-text"
                           value="<?php echo $entry && $entry->skills ? implode(', ', json_decode($entry->skills)) : ''; ?>">
                    <p class="description"><?php _e('Comma-separated list of skills used in this position', 'career-progression'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="achievements"><?php _e('Key Achievements', 'career-progression'); ?></label>
                </th>
                <td>
                    <textarea id="achievements" name="achievements" rows="5" cols="50" class="large-text"><?php 
                        if ($entry && $entry->achievements) {
                            echo esc_textarea(implode("\n", json_decode($entry->achievements)));
                        }
                    ?></textarea>
                    <p class="description"><?php _e('One achievement per line', 'career-progression'); ?></p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php echo $entry ? __('Update Entry', 'career-progression') : __('Add Entry', 'career-progression'); ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=career-progression'); ?>" class="button">
                <?php _e('Cancel', 'career-progression'); ?>
            </a>
        </p>
    </form>
    
    <!-- Add New Path Modal -->
    <div id="cpv-add-path-modal" style="display: none;">
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998;"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; z-index: 9999; width: 500px; max-height: 80vh; overflow-y: auto;">
            <h2><?php _e('Add New Career Path', 'career-progression'); ?></h2>
            
            <div style="margin: 20px 0;">
                <label for="cpv-path-search"><?php _e('Search or enter custom path name:', 'career-progression'); ?></label>
                <input type="text" id="cpv-path-search" class="regular-text" style="width: 100%; margin-top: 10px;" placeholder="<?php _e('Type to search or enter custom path...', 'career-progression'); ?>">
            </div>
            
            <div id="cpv-path-suggestions" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; margin: 20px 0;">
                <?php foreach ($common_paths as $category => $paths): ?>
                    <div class="path-category">
                        <h4 style="background: #f0f0f0; margin: 0; padding: 10px; font-size: 13px; text-transform: uppercase; color: #666;"><?php echo esc_html($category); ?></h4>
                        <div style="padding: 10px;">
                            <?php foreach ($paths as $path): ?>
                                <div class="path-suggestion" data-path="<?php echo esc_attr($path); ?>" style="padding: 8px; cursor: pointer; border-radius: 4px;">
                                    <?php echo esc_html($path); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="button" onclick="closePathModal()"><?php _e('Cancel', 'career-progression'); ?></button>
                <button type="button" class="button button-primary" onclick="selectNewPath()"><?php _e('Add Path', 'career-progression'); ?></button>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Initialize path field from selector
        if ($('#path_type_select').val() && $('#path_type_select').val() !== '__add_new__') {
            $('#path_type').val($('#path_type_select').val());
        }
        
        // Handle path selector change
        $('#path_type_select').on('change', function() {
            if ($(this).val() === '__add_new__') {
                $('#cpv-add-path-modal').show();
                $('#cpv-path-search').val('').focus();
            } else {
                $('#path_type').val($(this).val());
            }
        });
        
        // Filter suggestions as user types
        $('#cpv-path-search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            if (searchTerm.length === 0) {
                $('.path-suggestion').show();
                $('.path-category').show();
            } else {
                $('.path-suggestion').each(function() {
                    const pathText = $(this).text().toLowerCase();
                    if (pathText.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                
                // Hide empty categories
                $('.path-category').each(function() {
                    if ($(this).find('.path-suggestion:visible').length === 0) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            }
        });
        
        // Handle clicking on a suggestion
        $('.path-suggestion').on('click', function() {
            $('#cpv-path-search').val($(this).data('path'));
            $('.path-suggestion').removeClass('selected');
            $(this).addClass('selected');
        });
        
        // Style for hover and selected
        $('.path-suggestion').hover(
            function() { $(this).css('background', '#f0f0f0'); },
            function() { if (!$(this).hasClass('selected')) $(this).css('background', 'transparent'); }
        );
    });
    
    function closePathModal() {
        jQuery('#cpv-add-path-modal').hide();
        jQuery('#path_type_select').val('');
    }
    
    function selectNewPath() {
        const newPath = jQuery('#cpv-path-search').val().trim();
        if (newPath) {
            // Add the new path to the select dropdown
            const newOption = new Option(newPath, newPath, true, true);
            jQuery('#path_type_select').append(newOption);
            jQuery('#path_type').val(newPath);
            jQuery('#cpv-add-path-modal').hide();
        } else {
            alert('Please enter a path name');
        }
    }
    </script>
</div>