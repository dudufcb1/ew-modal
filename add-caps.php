<?php
/**
 * Add EWM capabilities to administrator role
 */

// Add the main capability needed for the modal builder
$role = get_role('administrator');
if ($role) {
    $role->add_cap('edit_ew_modals');
    echo "Added edit_ew_modals capability to administrator role\n";
} else {
    echo "Administrator role not found\n";
}
