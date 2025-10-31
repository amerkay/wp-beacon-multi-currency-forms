<?php
// Fallback if called directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
delete_option( 'wbcd_currencies' );
delete_option( 'wbcd_target_page_id' );
