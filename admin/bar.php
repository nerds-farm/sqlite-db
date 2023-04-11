<?php
/**
 * Admin hooks used for SQLite Integration.
 *
 * @package performance-lab
 * @since 2.1.0
 */

/**
 * Adds a link to the admin bar.
 *
 * @since 2.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param WP_Admin_Bar $admin_bar The admin bar object.
 */
function sqlite_db_plugin_adminbar_item( $admin_bar ) {
	global $wpdb;

	if ( defined( 'FQDB' ) && defined( 'DATABASE_TYPE' ) && 'sqlite' === DATABASE_TYPE ) {
		$title = '<span style="color:#46B450;">' . __( 'DB: SQLite', 'performance-lab' ) . '</span>';
	} elseif ( stripos( $wpdb->db_server_info(), 'maria' ) !== false ) {
		$title = '<span style="color:#DC3232;">' . __( 'DB: MariaDB', 'performance-lab' ) . '</span>';
	} else {
		$title = '<span style="color:#DC3232;">' . __( 'DB: MySQL', 'performance-lab' ) . '</span>';
	}

	$args = array(
		'id'     => 'sqlite-db',
		'parent' => 'top-secondary',
		'title'  => $title,
		'href'   => esc_url( admin_url( 'options-general.php?page=sqlite-db' ) ),
		'meta'   => false,
	);
	$admin_bar->add_node( $args );
}
if (WP_DEBUG) {
    add_action( 'admin_bar_menu', 'sqlite_db_plugin_adminbar_item', 999 );
}