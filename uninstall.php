<?php
/**
 * Uninstall Script
 *
 * Fired when the plugin is uninstalled.
 * Cleans up plugin data while preserving real ratings and sales.
 *
 * @package KKSR_Data_Faker
 * @since   4.0.0
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean Up Plugin Data
 *
 * What we DELETE:
 * - Plugin settings (kksr_faker_settings)
 * - Visitor log table (wp_kksr_visitor_log)
 *
 * What we KEEP (DO NOT DELETE):
 * - Ratings data (_kksr_count_default, _kksr_avg_default, etc.)
 * - Sales data (total_sales)
 * - All real user data
 */

global $wpdb;

// 1. Delete plugin settings
delete_option( 'kksr_faker_settings' );

// 2. Drop visitor log table
$table_name = $wpdb->prefix . 'kksr_visitor_log';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// 3. DO NOT delete ratings meta (keep real data)
// DO NOT: delete_post_meta_by_key('_kksr_count_default');
// DO NOT: delete_post_meta_by_key('_kksr_avg_default');
// DO NOT: delete_post_meta_by_key('total_sales');

// That's it! Plugin removed cleanly.
