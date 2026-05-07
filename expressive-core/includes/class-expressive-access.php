<?php

class Expressive_Access {

	/**
	 * Check if a user has an active subscription.
	 * Hierarchy: Admin > Manual Override > Local/API Status > WooCommerce Subscriptions.
	 */
	public function has_active_subscription( $user_id = 0, $allow_api = true ) {
		if ( ! $user_id && is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) return false;

		if ( $allow_api ) {
			self::maybe_refresh_access_from_api( $user_id );
		}

		$snapshot = self::get_user_access_snapshot( $user_id );
		return ! empty( $snapshot['has_access'] );
	}

	/**
	 * Return a single access/payment state used by the engine, admin and dashboard.
	 */
	public static function get_user_access_snapshot( $user_id = 0 ) {
		if ( ! $user_id && is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}

		$default = array(
			'user_id'                  => $user_id,
			'has_access'               => false,
			'effective_status'         => 'inactive',
			'status_label'             => 'Expirada / Inativa',
			'source_label'             => 'Automático',
			'plan_name'                => 'Plano Elite',
			'payment_label'            => 'Gateway externo',
			'access_expires_at'        => '',
			'grace_ends_at'            => '',
			'manual_status'            => 'none',
			'api_status'               => '',
			'local_status'             => '',
			'is_lifetime'              => false,
			'is_manually_blocked'      => false,
			'is_active'                => false,
			'is_grace'                 => false,
			'is_cancelled_with_access' => false,
			'is_expired'               => true,
			'can_cancel'               => false,
		);

		$user = $user_id ? get_userdata( $user_id ) : null;
		if ( ! $user ) {
			return $default;
		}

		$manual_status = get_user_meta( $user_id, '_lms_elite_manual_status', true ) ?: 'none';
		$api_status    = get_user_meta( $user_id, '_lms_elite_api_status', true );
		$api_expiry    = get_user_meta( $user_id, '_lms_elite_api_expiry', true );
		$api_plan      = get_user_meta( $user_id, '_lms_elite_api_plan', true );
		$local         = self::get_local_access_record( $user_id );

		$default['manual_status'] = $manual_status;
		$default['api_status'] = $api_status;
		$default['access_expires_at'] = $api_expiry;
		$default['plan_name'] = $api_plan ?: $default['plan_name'];

		if ( $local ) {
			$default['local_status'] = $local->status;
			$default['access_expires_at'] = $local->access_expires_at ?: $default['access_expires_at'];
			$default['grace_ends_at'] = $local->grace_ends_at ?: '';
			$default['plan_name'] = $local->plan_name ?: $default['plan_name'];
		}

		if ( user_can( $user_id, 'manage_options' ) ) {
			return array_merge( $default, array(
				'has_access'       => true,
				'effective_status' => 'admin',
				'status_label'     => 'Passe Administrativo',
				'source_label'     => 'Administrador',
				'plan_name'        => 'Acesso Administrativo',
				'payment_label'    => 'Bypass administrativo',
				'is_active'        => true,
				'is_expired'       => false,
			) );
		}

		if ( $manual_status === 'blocked' ) {
			return array_merge( $default, array(
				'has_access'          => false,
				'effective_status'    => 'blocked',
				'status_label'        => 'Bloqueado Manualmente',
				'source_label'        => 'Bloqueio manual',
				'plan_name'           => 'Acesso Suspenso',
				'payment_label'       => 'Suspenso pelo administrador',
				'is_manually_blocked' => true,
				'is_expired'          => true,
			) );
		}

		if ( $manual_status === 'unblocked' ) {
			return array_merge( $default, array(
				'has_access'       => true,
				'effective_status' => 'lifetime',
				'status_label'     => 'Passe Vitalício Concedido',
				'source_label'     => 'Liberação manual',
				'plan_name'        => 'Acesso Vitalício Elite',
				'payment_label'    => 'Concedido manualmente',
				'is_lifetime'      => true,
				'is_active'        => true,
				'is_expired'       => false,
			) );
		}

		if ( $local ) {
			return self::evaluate_subscription_state(
				$default,
				$local->status,
				$local->access_expires_at,
				$local->grace_ends_at,
				'Gateway sincronizado'
			);
		}

		if ( $api_status ) {
			return self::evaluate_subscription_state(
				$default,
				$api_status,
				$api_expiry,
				'',
				'Meta sincronizada'
			);
		}

		$self = new self();
		if ( $self->has_active_woocommerce_subscription( $user_id ) ) {
			return array_merge( $default, array(
				'has_access'       => true,
				'effective_status' => 'active',
				'status_label'     => 'Ativa / WooCommerce',
				'source_label'     => 'WooCommerce Subscriptions',
				'is_active'        => true,
				'is_expired'       => false,
				'can_cancel'       => false,
			) );
		}

		return $default;
	}

	private static function evaluate_subscription_state( $base, $status, $access_expires_at = '', $grace_ends_at = '', $source_label = 'Automático' ) {
		$status = $status ? sanitize_key( strtolower( $status ) ) : 'inactive';
		$now = time();
		$expiry_ts = self::parse_access_date_end( $access_expires_at );
		$grace_ts = self::parse_access_date_end( $grace_ends_at );
		$fallback_days = max( 0, intval( get_option( 'lms_hard_fallback_days', 30 ) ) );
		$hard_limit = $fallback_days * DAY_IN_SECONDS;

		$base['source_label'] = $source_label;
		$base['access_expires_at'] = $access_expires_at ?: $base['access_expires_at'];
		$base['grace_ends_at'] = $grace_ends_at ?: $base['grace_ends_at'];

		if ( $status === 'active' ) {
			if ( $expiry_ts && ( $expiry_ts + $hard_limit ) < $now ) {
				return array_merge( $base, array(
					'has_access'       => false,
					'effective_status' => 'expired',
					'status_label'     => 'Expirada / Limite de Segurança',
					'is_expired'       => true,
				) );
			}

			$is_tolerated = $expiry_ts && $expiry_ts < $now;
			return array_merge( $base, array(
				'has_access'       => true,
				'effective_status' => $is_tolerated ? 'active_tolerance' : 'active',
				'status_label'     => $is_tolerated ? 'Ativa / Regularização Pendente' : 'Ativa / Recorrência Ligada',
				'is_active'        => true,
				'is_expired'       => false,
				'can_cancel'       => true,
			) );
		}

		if ( $status === 'grace_period' ) {
			$valid_until = $grace_ts ?: $expiry_ts;
			if ( $valid_until && $valid_until >= $now ) {
				return array_merge( $base, array(
					'has_access'       => true,
					'effective_status' => 'grace_period',
					'status_label'     => 'Período de Carência',
					'is_grace'         => true,
					'is_expired'       => false,
				) );
			}

			return array_merge( $base, array(
				'has_access'       => false,
				'effective_status' => 'expired',
				'status_label'     => 'Carência Expirada',
				'is_expired'       => true,
			) );
		}

		if ( in_array( $status, array( 'cancelled', 'canceled', 'inactive', 'past_due', 'overdue' ), true ) ) {
			if ( $expiry_ts && $expiry_ts >= $now ) {
				return array_merge( $base, array(
					'has_access'               => true,
					'effective_status'         => 'cancelled_with_access',
					'status_label'             => 'Cancelada / Acesso Vigente',
					'payment_label'            => 'Recorrência cancelada',
					'is_cancelled_with_access' => true,
					'is_expired'               => false,
				) );
			}

			return array_merge( $base, array(
				'has_access'       => false,
				'effective_status' => 'inactive',
				'status_label'     => 'Expirada / Inativa',
				'payment_label'    => 'Sem recorrência ativa',
				'is_expired'       => true,
			) );
		}

		return array_merge( $base, array(
			'has_access'       => false,
			'effective_status' => $status,
			'status_label'     => strtoupper( str_replace( '_', ' ', $status ) ),
			'is_expired'       => true,
		) );
	}

	private static function maybe_refresh_access_from_api( $user_id ) {
		$api_url = get_option( 'lms_external_api_url_status' ) ?: get_option( 'lms_external_api_url', '' );
		if ( empty( $api_url ) || ! class_exists( 'Expressive_External_API' ) ) {
			return;
		}

		$local = self::get_local_access_record( $user_id );
		$last_check = $local && ! empty( $local->last_sync_at ) ? strtotime( $local->last_sync_at ) : intval( get_user_meta( $user_id, '_lms_elite_api_last_check', true ) );
		$cache_ttl = HOUR_IN_SECONDS * 6;

		if ( ! $last_check || ( time() - $last_check ) > $cache_ttl ) {
			Expressive_External_API::check_user_status( $user_id );
		}
	}

	private static function get_local_access_record( $user_id ) {
		global $wpdb;

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return null;
		}

		$table = $wpdb->prefix . 'elite_subscription_access';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE email = %s", strtolower( trim( $user->user_email ) ) ) );
	}

	private static function parse_access_date_end( $date ) {
		if ( empty( $date ) ) {
			return false;
		}

		$date = trim( (string) $date );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			$date .= ' 23:59:59';
		}

		return strtotime( $date );
	}

	/**
	 * Final fallback: Check WooCommerce Subscriptions or native WooCommerce orders.
	 */
	public function has_active_woocommerce_subscription( $user_id ) {
		// 5.1 WooCommerce Subscriptions plugin (if active)
		if ( function_exists( 'wcs_user_has_subscription' ) ) {
			return wcs_user_has_subscription( $user_id, '', 'active' );
		}

		// 5.2 Native Fallback: We no longer trust old legacy meta.
		// Only WC Subscriptions (if active) are allowed as secondary fallback.

		return false;
	}

	/**
	 * Middleware to protect restricted content.
	 */
	public function protect_content_middleware() {
		if ( ! is_singular( array( 'lms_lesson', 'lms_course', 'lms_live' ) ) ) {
			return;
		}

		$post_type = get_post_type();
		$post_id   = get_the_ID();

		// Administrators always pass
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		// 1. Mandatory Login (Exempting Content Pages for Curiosity Access)
		if ( ! is_user_logged_in() ) {
			if ( ! in_array( $post_type, array( 'lms_lesson', 'lms_course', 'lms_live' ) ) ) {
				Expressive_Logger::info( 'ACCESS', "Redirecionado para login: Visitante tentou acessar página restrita", array( 'post_id' => $post_id, 'post_type' => $post_type ) );
				wp_redirect( site_url( '/login/' ) );
				exit;
			}
			return; // Allow guest access to content pages
		}

		// 3. Subscription Check for Lessons and Lives
		// REFACTORED: We no longer redirect logged-in users out of lessons.
		// Instead, the templates (single-lms_lesson.php, etc) handle the "Locked UI"
		// to allow Curiosity Access for inactive members.
		/*
		if ( in_array( $post_type, array( 'lms_lesson', 'lms_live' ) ) ) {
			if ( ! $this->has_active_subscription() ) {
				Expressive_Logger::warning( 'ACCESS', "Acesso BLOQUEADO a conteúdo: Assinatura inativa", array( 'user_id' => get_current_user_id(), 'post_id' => $post_id, 'post_type' => $post_type ) );
				wp_redirect( site_url( '/area-de-membros/?restricted=1' ) );
				exit;
			}
		}
		*/

		// 4. RBAC Check (Educadora vs Autoridade)
		$visibility = get_post_meta( $post_id, '_lms_visibility_role', true ) ?: 'all';
		if ( $visibility !== 'all' ) {
			$is_educator = Expressive_Referral::is_educator( get_current_user_id() );

			if ( $visibility === 'educadora' && ! $is_educator ) {
				Expressive_Logger::warning( 'ACCESS', "Acesso BLOQUEADO por RBAC: Conteúdo exclusivo para Educadoras", array( 'user_id' => get_current_user_id(), 'post_id' => $post_id, 'visibility' => $visibility ) );
				wp_redirect( site_url( '/area-de-membros/?rbac_restricted=1' ) );
				exit;
			}

			if ( $visibility === 'autoridade' && $is_educator ) {
				Expressive_Logger::warning( 'ACCESS', "Acesso BLOQUEADO por RBAC: Conteúdo exclusivo para Autoridades", array( 'user_id' => get_current_user_id(), 'post_id' => $post_id, 'visibility' => $visibility ) );
				wp_redirect( site_url( '/area-de-membros/?rbac_restricted=1' ) );
				exit;
			}
		}
	}

	/**
	 * Centralized method to update user access status across all fields.
	 * Synchronizes _lms_subscription_status (Local) and _lms_elite_manual_status (Manual).
	 */
	public static function update_access_status( $user_id, $status ) {
		// Maps statuses to respective field values
		if ( $status === 'suspended' || $status === 'blocked' ) {
			update_user_meta( $user_id, '_lms_subscription_status', 'suspended' );
			update_user_meta( $user_id, '_lms_elite_manual_status', 'blocked' );
			Expressive_Logger::info( 'ACCESS', "Status de acesso ATUALIZADO para: SUSPENSO", array( 'target_user' => $user_id ) );
		} elseif ( $status === 'none' ) {
			// AUTOMATIC MODE: Secure by default.
			// We set local status to suspended so they ONLY enter if the API validates them as active.
			update_user_meta( $user_id, '_lms_subscription_status', 'suspended' );
			update_user_meta( $user_id, '_lms_elite_manual_status', 'none' );
			delete_user_meta( $user_id, '_lms_elite_api_status' ); // Re-trigger API check
			Expressive_Logger::info( 'ACCESS', "Status de acesso ATUALIZADO para: AUTOMÁTICO (Seguro por padrão)", array( 'target_user' => $user_id ) );
		} elseif ( $status === 'active' ) {
			update_user_meta( $user_id, '_lms_subscription_status', 'active' );
			update_user_meta( $user_id, '_lms_elite_manual_status', 'none' );
			Expressive_Logger::info( 'ACCESS', "Status de acesso ATUALIZADO para: ATIVO", array( 'target_user' => $user_id ) );
		} elseif ( $status === 'unblocked' ) {
			update_user_meta( $user_id, '_lms_subscription_status', 'active' );
			update_user_meta( $user_id, '_lms_elite_manual_status', 'unblocked' );
			Expressive_Logger::info( 'ACCESS', "Status de acesso ATUALIZADO para: LIBERADO (MANUAL)", array( 'target_user' => $user_id ) );
		}
	}

}
