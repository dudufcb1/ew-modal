<?php
/**
 * Temporary script to fix EWM Modal CTA capabilities
 */

// Include WordPress
require_once('/var/www/html/plugins/wp-config.php');

// Define the capabilities that need to be added
$ewm_capabilities = array(
    // Capabilities para modales
    'read_ew_modal',
    'read_private_ew_modals',
    'edit_ew_modal',
    'edit_ew_modals',
    'edit_others_ew_modals',
    'edit_private_ew_modals',
    'edit_published_ew_modals',
    'publish_ew_modals',
    'delete_ew_modal',
    'delete_ew_modals',
    'delete_others_ew_modals',
    'delete_private_ew_modals',
    'delete_published_ew_modals',

    // Capabilities para envíos
    'read_ewm_submission',
    'read_private_ewm_submissions',
    'edit_ewm_submission',
    'edit_ewm_submissions',
    'edit_others_ewm_submissions',
    'delete_ewm_submission',
    'delete_ewm_submissions',
    'delete_others_ewm_submissions',

    // Capabilities administrativos
    'manage_ewm_settings',
    'view_ewm_analytics',
    'export_ewm_data',
    'import_ewm_data',
);

// Get administrator role
$admin_role = get_role('administrator');

if ($admin_role) {
    echo "Adding EWM capabilities to administrator role...\n";
    
    foreach ($ewm_capabilities as $capability) {
        $admin_role->add_cap($capability);
        echo "Added capability: $capability\n";
    }
    
    echo "Capabilities added successfully!\n";
    
    // Update the setup option
    update_option('ewm_capabilities_setup', '1.0.0');
    echo "Updated ewm_capabilities_setup option.\n";
    
} else {
    echo "Error: Administrator role not found!\n";
}

// Verify the capabilities were added
echo "\nVerifying capabilities...\n";
$admin_role = get_role('administrator');
$admin_caps = $admin_role->capabilities;

$missing_caps = array();
foreach ($ewm_capabilities as $capability) {
    if (!isset($admin_caps[$capability]) || !$admin_caps[$capability]) {
        $missing_caps[] = $capability;
    }
}

if (empty($missing_caps)) {
    echo "All EWM capabilities are properly set!\n";
} else {
    echo "Missing capabilities: " . implode(', ', $missing_caps) . "\n";
}
