/**
 * Career Progression Admin JavaScript
 */

jQuery(document).ready(function($) {
    // Initialize datepicker
    if ($.fn.datepicker) {
        $('#start_date, #end_date').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: '1990:2030'
        });
    }
    
    // Handle form submission
    $('#cpv-entry-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'cpv_save_career_entry');
        formData.append('nonce', $('#cpv_nonce').val());
        
        // Add path information based on selected path type
        const pathType = $('#path_type').val();
        formData.append('path_type', pathType);
        formData.append('path_color', getPathColor(pathType));
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Career entry saved successfully!');
                    window.location.href = 'admin.php?page=career-progression';
                } else {
                    alert('Error saving entry. Please try again.');
                }
            },
            error: function() {
                alert('Error saving entry. Please try again.');
            }
        });
    });
    
    // Handle delete button - use delegated event for dynamically loaded content
    $(document).on('click', '.cpv-delete-entry', function() {
        if (!confirm('Are you sure you want to delete this entry?')) {
            return;
        }
        
        const entryId = $(this).data('entry-id');
        const row = $(this).closest('tr');
        
        // Use the nonce from the global variable set in the page
        const nonce = typeof cpv_admin_nonce !== 'undefined' ? cpv_admin_nonce : '';
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cpv_delete_career_entry',
                entry_id: entryId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    row.fadeOut(300, function() {
                        $(this).remove();
                        // Check if table is now empty
                        if ($('.wp-list-table tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert('Error deleting entry: ' + (response.data || 'Please try again.'));
                }
            },
            error: function(xhr, status, error) {
                alert('Error deleting entry: ' + error);
            }
        });
    });
    
    // Handle Delete All button
    $('#cpv-delete-all').on('click', function() {
        $('#cpv-delete-all-modal').show();
    });
    
    // Handle Cancel delete all
    $('#cpv-cancel-delete').on('click', function() {
        $('#cpv-delete-all-modal').hide();
    });
    
    // Handle Confirm delete all
    $('#cpv-confirm-delete').on('click', function() {
        const nonce = typeof cpv_admin_nonce !== 'undefined' ? cpv_admin_nonce : '';
        
        $(this).prop('disabled', true).text('Deleting...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cpv_delete_all_entries',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('All entries have been deleted successfully!');
                    location.reload();
                } else {
                    alert('Error deleting entries: ' + (response.data || 'Please try again.'));
                    $('#cpv-confirm-delete').prop('disabled', false).text('Delete All Entries');
                    $('#cpv-delete-all-modal').hide();
                }
            },
            error: function(xhr, status, error) {
                alert('Error deleting entries: ' + error);
                $('#cpv-confirm-delete').prop('disabled', false).text('Delete All Entries');
                $('#cpv-delete-all-modal').hide();
            }
        });
    });
    
    // Helper function to get path color
    function getPathColor(pathType) {
        const colors = {
            'IT Path': '#4299e1',
            'Design Path': '#ed8936',
            'Engineering Path': '#48bb78',
            'Management Path': '#9f7aea',
            'Business Path': '#f56565'
        };
        return colors[pathType] || '#4299e1';
    }
    
    // Add path type selector to form if not exists
    if ($('#path_type').length === 0 && $('#cpv-entry-form').length > 0) {
        const pathTypeRow = `
            <tr>
                <th scope="row">
                    <label for="path_type">Career Path</label>
                </th>
                <td>
                    <select id="path_type" name="path_type" class="regular-text">
                        <option value="IT Path">IT Path</option>
                        <option value="Design Path">Design Path</option>
                        <option value="Engineering Path">Engineering Path</option>
                        <option value="Management Path">Management Path</option>
                        <option value="Business Path">Business Path</option>
                    </select>
                    <p class="description">Select the career path this position belongs to</p>
                </td>
            </tr>
        `;
        
        // Insert after company field
        $('#company').closest('tr').after(pathTypeRow);
    }
    
    // Skills input enhancement
    $('#skills').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const currentValue = $(this).val();
            if (currentValue && !currentValue.endsWith(',')) {
                $(this).val(currentValue + ', ');
            }
        }
    });
    
    
    // Color picker for custom paths
    if ($('#custom_path_color').length > 0) {
        $('#custom_path_color').wpColorPicker();
    }
    
    // Export/Import functionality
    $('#export-career-data').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cpv_export_data',
                nonce: cpv_admin_nonce
            },
            success: function(response) {
                if (response.success) {
                    const dataStr = JSON.stringify(response.data, null, 2);
                    const dataBlob = new Blob([dataStr], {type: 'application/json'});
                    const url = URL.createObjectURL(dataBlob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'career-progression-data.json';
                    link.click();
                    URL.revokeObjectURL(url);
                }
            }
        });
    });
    
    $('#import-career-data').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = JSON.parse(e.target.result);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cpv_import_data',
                        data: JSON.stringify(data),
                        nonce: cpv_admin_nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Data imported successfully!');
                            location.reload();
                        } else {
                            alert('Error importing data. Please check the file format.');
                        }
                    }
                });
            } catch (error) {
                alert('Invalid JSON file. Please check the file format.');
            }
        };
        reader.readAsText(file);
    });
});