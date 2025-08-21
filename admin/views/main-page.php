<div class="wrap">
    <h1><?php _e('Career Progression Entries', 'career-progression'); ?>
        <a href="<?php echo admin_url('admin.php?page=career-progression-add'); ?>" class="page-title-action">
            <?php _e('Add New', 'career-progression'); ?>
        </a>
        <?php if (!empty($entries)): ?>
        <button id="cpv-delete-all" class="page-title-action" style="background: #dc3545; color: white; border-color: #dc3545;">
            <?php _e('Delete All', 'career-progression'); ?>
        </button>
        <?php endif; ?>
    </h1>
    
    <script type="text/javascript">
        var cpv_admin_nonce = '<?php echo wp_create_nonce('cpv_admin_nonce'); ?>';
    </script>
    
    <?php if (!empty($entries)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Position', 'career-progression'); ?></th>
                    <th><?php _e('Company', 'career-progression'); ?></th>
                    <th><?php _e('Start Date', 'career-progression'); ?></th>
                    <th><?php _e('End Date', 'career-progression'); ?></th>
                    <th><?php _e('Location', 'career-progression'); ?></th>
                    <th><?php _e('Actions', 'career-progression'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><strong><?php echo esc_html($entry->position); ?></strong></td>
                        <td><?php echo esc_html($entry->company); ?></td>
                        <td><?php echo esc_html(date('F Y', strtotime($entry->start_date))); ?></td>
                        <td>
                            <?php 
                            if ($entry->end_date) {
                                echo esc_html(date('F Y', strtotime($entry->end_date)));
                            } else {
                                echo '<span style="color: green;">' . __('Current', 'career-progression') . '</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html($entry->location); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=career-progression-add&id=' . $entry->id); ?>" class="button button-small">
                                <?php _e('Edit', 'career-progression'); ?>
                            </a>
                            <button class="button button-small cpv-delete-entry" data-entry-id="<?php echo $entry->id; ?>">
                                <?php _e('Delete', 'career-progression'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="notice notice-info">
            <p><?php _e('No career entries found. Click "Add New" to create your first entry.', 'career-progression'); ?></p>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px;">
        <h2><?php _e('Shortcode Usage', 'career-progression'); ?></h2>
        <p><?php _e('Use the following shortcode to display your career progression visualization:', 'career-progression'); ?></p>
        <code>[career_progression type="timeline" width="100%" height="600"]</code>
        <p><?php _e('Available types: timeline, tree, graph, sankey', 'career-progression'); ?></p>
    </div>
    
    <!-- Delete All Confirmation Modal -->
    <div id="cpv-delete-all-modal" style="display: none;">
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998;"></div>
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 9999; max-width: 400px;">
            <h2 style="margin-top: 0; color: #dc3545;"><?php _e('Confirm Delete All', 'career-progression'); ?></h2>
            <p><?php _e('Are you sure you want to delete ALL career progression entries? This action cannot be undone.', 'career-progression'); ?></p>
            <p><strong><?php _e('This will permanently delete all career data from the database.', 'career-progression'); ?></strong></p>
            <div style="margin-top: 20px; text-align: right;">
                <button id="cpv-cancel-delete" class="button button-large"><?php _e('Cancel', 'career-progression'); ?></button>
                <button id="cpv-confirm-delete" class="button button-primary button-large" style="background: #dc3545; border-color: #dc3545; margin-left: 10px;">
                    <?php _e('Delete All Entries', 'career-progression'); ?>
                </button>
            </div>
        </div>
    </div>
</div>