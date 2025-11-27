<?php
// Fallback if called directly.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}
delete_option('bmcf_currencies');
delete_option('bmcf_target_page_id');
