<?php

class Expressive_Access {

	/**
	 * Check if a user has an active subscription.
	 * Hierarchy: Admin > Manual Override > API External > Local Status > WooCommerce
	 */
	public function has_active_subscription( $user_id = 0, $allow_api = true ) {
		if ( ! $user_id && is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) return false;

		// --- 1. PASSE ADMINISTRATIVO VITALÍCIO ---
		if ( user_can( $user_id, 'manage_options' ) ) return true;

		// --- 2. SOBRESCRITA MANUAL (ADMIN OVERDRIVE) ---
		$manual_status = get_user_meta( $user_id, '_lms_elite_manual_status', true ) ?: 'none';
		if ( $manual_status === 'blocked' ) return false;
		if ( $manual_status === 'unblocked' ) return true;

		// --- 3. NOVA VERDADE: TABELA LOCAL DE ACESSO ---
		global $wpdb;
		$user = get_userdata( $user_id );
		$table = $wpdb->prefix . 'elite_subscription_access';
		$local = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE email = %s", $user->user_email ) );

		if ( $local ) {
			if ( $local->status === 'active' ) {
				Expressive_Logger::debug( 'ACCESS', "Acesso LIBERADO: Status 'active' na tabela local", array( 'user_id' => $user_id ) );
				return true;
			}

			if ( $local->status === 'grace_period' ) {
				$grace_ends = strtotime( $local->grace_ends_at . ' 23:59:59' );
				if ( $grace_ends && $grace_ends >= time() ) {
					Expressive_Logger::debug( 'ACCESS', "Acesso LIBERADO: Grace Period ativo até " . $local->grace_ends_at, array( 'user_id' => $user_id ) );
					return true;
				}
				Expressive_Logger::warning( 'ACCESS', "Acesso BLOQUEADO: Grace Period expirado em " . $local->grace_ends_at, array( 'user_id' => $user_id ) );
				return false;
			}

			// Se temos registro local e não é active/grace, bloqueia (a menos que permitamos re-checar na API)
			if ( ! $allow_api ) {
				return false;
			}
		}

		// --- 4. API EXTERNA (Se não houver registro local ou se permitido re-checar) ---
		$api_url = get_option( 'lms_external_api_url', '' );
		if ( ! empty( $api_url ) && class_exists( 'Expressive_External_API' ) ) {
			// Check if we should re-sync (cache TTL)
			$last_check = $local ? strtotime($local->last_sync_at) : 0;
			$cache_ttl  = HOUR_IN_SECONDS * 6; // 6h default cache

			if ( (time() - $last_check) > $cache_ttl && $allow_api ) {
				$api_status = Expressive_External_API::check_user_status( $user_id );
				// check_user_status already calls update_local_access, so we re-read the state
				if ( $api_status === 'active' ) return true;
				
				// Re-verify after sync
				$local = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE email = %s", $user->user_email ) );
				if ( $local && $local->status === 'grace_period' && strtotime($local->grace_ends_at . ' 23:59:59') >= time() ) {
					return true;
				}
			}
		}

		// --- 5. FALLBACK PARA WOOCOMMERCE (Legado) ---
		$wc_status = $this->has_active_woocommerce_subscription( $user_id );
		return $wc_status;
	}

	/**
	 * Final fallback: Check WooCommerce Subscriptions or native WooCommerce orders.
	 */
	public function has_active_woocommerce_subscription( $user_id ) {
		// 5.1 WooCommerce Subscriptions plugin (if active)
		if ( function_exists( 'wcs_user_has_subscription' ) ) {
			return wcs_user_has_subscription( $user_id, '', 'active' );
		}

		// 5.2 Native Fallback: Is 'active' locally?
		$local_status = get_user_meta( $user_id, '_lms_subscription_status', true );
		if ( $local_status === 'active' ) {
			return true;
		}

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
