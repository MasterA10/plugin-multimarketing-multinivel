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

		if ( ! $user_id ) {
			return false;
		}

		// --- 1. PASSE ADMINISTRATIVO VITALÍCIO ---
		if ( user_can( $user_id, 'manage_options' ) ) {
			Expressive_Logger::debug( 'ACCESS', "Acesso LIBERADO: Bypass Administrativo detectado", array( 'user_id' => $user_id ) );
			return true;
		}

		// --- 2. SOBRESCRITA MANUAL (ADMIN OVERDRIVE) ---
		$manual_status = get_user_meta( $user_id, '_lms_elite_manual_status', true ) ?: 'none';
		if ( $manual_status === 'blocked' ) {
			Expressive_Logger::warning( 'ACCESS', "Acesso BLOQUEADO: Sobreposição manual 'BLOQUEADO' ativa", array( 'user_id' => $user_id ) );
			return false;
		}
		if ( $manual_status === 'unblocked' ) {
			Expressive_Logger::info( 'ACCESS', "Acesso LIBERADO: Sobreposição manual 'LIBERADO' ativa", array( 'user_id' => $user_id ) );
			return true;
		}

		// --- 3. API EXTERNA (Cache Inteligente) ---
		$api_url = get_option( 'lms_external_api_url', '' );
		if ( ! empty( $api_url ) && class_exists( 'Expressive_External_API' ) ) {
			$cached_status = get_user_meta( $user_id, '_lms_elite_api_status', true );
			$last_check    = get_user_meta( $user_id, '_lms_elite_api_last_check', true );
			$sync_interval = get_option( 'lms_api_sync_interval', 3 );
			$cache_ttl     = max( $sync_interval * MINUTE_IN_SECONDS * 2, HOUR_IN_SECONDS );

			// Se temos um status da API e a verificação é recente, confia nele como verdade absoluta
			if ( ! empty( $cached_status ) && $last_check && ( time() - (int) $last_check ) < $cache_ttl ) {
				$is_active = ( $cached_status === 'active' );
				Expressive_Logger::debug( 'ACCESS', "Acesso " . ( $is_active ? 'LIBERADO' : 'NEGADO' ) . ": Baseado em cache de API recente", array( 'user_id' => $user_id, 'status' => $cached_status ) );
				return $is_active;
			}

			// Cache expirado ou sem cache: tenta verificação em tempo real (SÓ SE PERMITIDO)
			if ( $allow_api ) {
				$api_status = Expressive_External_API::check_user_status( $user_id );
				if ( $api_status === 'active' ) {
					Expressive_Logger::info( 'ACCESS', "Acesso LIBERADO: Confirmado em tempo real via API Externa", array( 'user_id' => $user_id ) );
					return true;
				}
				if ( $api_status === 'inactive' ) {
					Expressive_Logger::warning( 'ACCESS', "Acesso NEGADO: Confirmado em tempo real via API Externa", array( 'user_id' => $user_id ) );
					return false;
				}
			} else {
				// No modo silencioso (dashboard), confia no que já temos
				if ( ! empty( $cached_status ) ) {
					$is_active = ( $cached_status === 'active' );
					Expressive_Logger::debug( 'ACCESS', "Acesso " . ( $is_active ? 'LIBERADO' : 'NEGADO' ) . ": Modo silencioso usando cache existente", array( 'user_id' => $user_id, 'status' => $cached_status ) );
					return $is_active;
				}
			}
		}

		// --- 4. STATUS LOCAL (Lógica da página Assinantes) ---
		$local_status = get_user_meta( $user_id, '_lms_subscription_status', true );
		if ( $local_status === 'suspended' ) {
			Expressive_Logger::warning( 'ACCESS', "Acesso BLOQUEADO: Status local marcado como SUSPENSO", array( 'user_id' => $user_id ) );
			return false;
		}

		// --- 5. FALLBACK PARA WOOCOMMERCE ---
		$wc_status = $this->has_active_woocommerce_subscription( $user_id );
		Expressive_Logger::debug( 'ACCESS', "Acesso " . ( $wc_status ? 'LIBERADO' : 'NEGADO' ) . ": Verificação final via WooCommerce Fallback", array( 'user_id' => $user_id ) );
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
