<?php

/**
 * Fired during plugin activation
 *
 * @link       https://dominai.cloud
 * @since      1.0.0
 *
 * @package    Multinivel_marketing
 * @subpackage Multinivel_marketing/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Multinivel_marketing
 * @subpackage Multinivel_marketing/includes
 * @author     Alex Alves <nasalexalves@gmail.com>
 */
class Multinivel_marketing_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// User Progress Table
		$table_name = $wpdb->prefix . 'mlm_user_progress';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			course_id bigint(20) NOT NULL,
			lesson_id bigint(20) NOT NULL,
			completion_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY lesson_id (lesson_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// Create Default Pages
		self::create_plugin_pages();
	}

	private static function create_plugin_pages() {
		$pages = array(
			'dashboard' => array(
				'title'   => 'Área de Membros',
				'content' => '[mlm_dashboard]',
				'slug'    => 'area-de-membros'
			),
			'login' => array(
				'title'   => 'Login Aluno',
				'content' => '[mlm_login]',
				'slug'    => 'login-aluno'
			),
		);

		foreach ( $pages as $page ) {
			$page_check = get_page_by_path( $page['slug'] );
			if ( ! isset( $page_check->ID ) ) {
				wp_insert_post( array(
					'post_title'    => $page['title'],
					'post_content'  => $page['content'],
					'post_status'   => 'publish',
					'post_type'     => 'page',
					'post_name'     => $page['slug']
				) );
			}
		}
	}

}
