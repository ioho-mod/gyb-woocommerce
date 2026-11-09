<?php
/**
 * GetYourBill Uninstall
 *
 * Uninstalling GetYourBill deletes meta.
 *
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
global $wpdb, $wp_version;



// Delete options.
//$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wc_settings_getyourbill_%';" );
//$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'getyourbill_%';" );
//$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'config_isonline';" );

// Delete all meta.
//$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE 'getyourbill_\_%';" );


// Clear any cached data that has been removed.
wp_cache_flush();
