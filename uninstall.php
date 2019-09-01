<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) && ! defined( 'WP_CLI' ) ) {
	exit;
}

if ( ! defined( 'WC1C_PLUGIN_DIR' ) ) {
	define( 'WC1C_PLUGIN_DIR', __DIR__ . '/' );
}
if ( ! defined( 'WC1C_DATA_DIR' ) ) {
	$upload_dir = wp_upload_dir();
	define( 'WC1C_DATA_DIR', "{$upload_dir['basedir']}/woocommerce-1c/" );
}

require WC1C_PLUGIN_DIR . 'exchange.php';
wc1c_disable_time_limit();

global $wpdb;

if ( is_dir( WC1C_DATA_DIR ) ) {
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( WC1C_DATA_DIR, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $iterator as $wc1c_path => $item ) {
		$item->isDir() ? rmdir( $wc1c_path ) : unlink( $wc1c_path );
	}
	rmdir( WC1C_DATA_DIR );
}

$index_table_names = array(
	$wpdb->postmeta,
	$wpdb->termmeta,
	$wpdb->usermeta,
);
foreach ( $index_table_names as $index_table_name ) {
	$index_name = 'wc1c_meta_key_meta_value';
	$result     = $wpdb->get_var( "SHOW INDEX FROM $index_table_name WHERE Key_name = '$index_name';" );
	if ( ! $result ) {
		continue;
	}

	$wpdb->query( "DROP INDEX $index_name ON $index_table_name" );
}
