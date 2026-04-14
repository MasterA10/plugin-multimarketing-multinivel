<?php

class Expressive_Activator {

	public static function activate() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// 1. wp_lms_chat_messages
		$table_chat = $wpdb->prefix . 'lms_chat_messages';
		$sql_chat = "CREATE TABLE $table_chat (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			lesson_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			message longtext NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY lesson_id (lesson_id),
			KEY user_id (user_id)
		) $charset_collate;";
		dbDelta( $sql_chat );

		// 2. wp_lms_referrals
		$table_referrals = $wpdb->prefix . 'lms_referrals';
		$sql_referrals = "CREATE TABLE $table_referrals (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			educator_id bigint(20) NOT NULL,
			authority_id bigint(20) NOT NULL,
			order_id bigint(20) DEFAULT 0 NOT NULL,
			order_total decimal(10,2) DEFAULT '0.00' NOT NULL,
			commission_amount decimal(10,2) DEFAULT '0.00' NOT NULL,
			referred_role varchar(50) DEFAULT '' NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY educator_id (educator_id),
			KEY authority_id (authority_id),
			KEY order_id (order_id)
		) $charset_collate;";
		dbDelta( $sql_referrals );

		// Manual migration for existing table
		$check_order_id = $wpdb->get_results( "SHOW COLUMNS FROM `$table_referrals` LIKE 'order_id'" );
		if ( empty( $check_order_id ) ) {
			$wpdb->query( "ALTER TABLE `$table_referrals` ADD COLUMN order_id bigint(20) DEFAULT 0 NOT NULL AFTER authority_id" );
		}
		
		$check_order_total = $wpdb->get_results( "SHOW COLUMNS FROM `$table_referrals` LIKE 'order_total'" );
		if ( empty( $check_order_total ) ) {
			$wpdb->query( "ALTER TABLE `$table_referrals` ADD COLUMN order_total decimal(10,2) DEFAULT '0.00' NOT NULL AFTER order_id" );
		}

		$check_commission = $wpdb->get_results( "SHOW COLUMNS FROM `$table_referrals` LIKE 'commission_amount'" );
		if ( empty( $check_commission ) ) {
			$wpdb->query( "ALTER TABLE `$table_referrals` ADD COLUMN commission_amount decimal(10,2) DEFAULT '0.00' NOT NULL AFTER order_total" );
		}

		$check_role = $wpdb->get_results( "SHOW COLUMNS FROM `$table_referrals` LIKE 'referred_role'" );
		if ( empty( $check_role ) ) {
			$wpdb->query( "ALTER TABLE `$table_referrals` ADD COLUMN referred_role varchar(50) DEFAULT '' NOT NULL AFTER commission_amount" );
		}

		// 3. wp_lms_bonus_log
		$table_bonus = $wpdb->prefix . 'lms_bonus_log';
		$sql_bonus = "CREATE TABLE $table_bonus (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			amount decimal(10,2) NOT NULL,
			source varchar(255) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset_collate;";
		dbDelta( $sql_bonus );

		// 4. Automatic Page Creation
		self::create_static_pages();
	}

	private static function create_static_pages() {
		$pages = array(
			'login' => array(
				'title'   => 'Login Aluno',
				'content' => '[expressive_login]',
				'slug'    => 'login',
			),
			'area-de-membros' => array(
				'title'   => 'Área de Membros',
				'content' => '[expressive_dashboard]',
				'slug'    => 'area-de-membros',
			),
			'dashboard-educador' => array(
				'title'   => 'Dashboard Educador',
				'content' => '[expressive_educator_dashboard]',
				'slug'    => 'dashboard-educador',
			),
		);

		foreach ( $pages as $slug => $data ) {
			$page_check = get_page_by_path( $slug );
			if ( ! isset( $page_check->ID ) ) {
				$page_id = wp_insert_post( array(
					'post_title'   => $data['title'],
					'post_content' => $data['content'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_name'    => $slug,
				) );
				if ( ! is_wp_error( $page_id ) ) {
					update_post_meta( $page_id, '_lms_page_type', $slug );
				}
			} else {
				// Ensure metadata exists for legacy pages
				update_post_meta( $page_check->ID, '_lms_page_type', $slug );
			}
		}
	}

}
