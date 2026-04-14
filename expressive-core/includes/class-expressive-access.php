<?php

class Expressive_Access {

	/**
	 * Check if a user has an active subscription.
	 * Hierarchy: Admin > Manual Override > API External > Local Status > WooCommerce
	 */
	public function has_active_subscription( $user_id = 0 ) {
		if ( ! $user_id && is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		// --- 1. PASSE ADMINISTRATIVO VITALÍCIO ---
		if ( user_can( $user_id, 'manage_options' ) ) {
			Expressive_Logger::debug( 'ACCESS', "Acesso concedido: Admin vitalício", array( 'user_id' => $user_id ) );
			return true;
		}

		// --- 2. SOBRESCRITA MANUAL (ADMIN OVERDRIVE) ---
		$manual_status = get_user_meta( $user_id, '_lms_elite_manual_status', true ) ?: 'none';
		if ( $manual_status === 'blocked' ) {
			Expressive_Logger::warning( 'ACCESS', "Acesso BLOQUEADO: Override manual", array( 'user_id' => $user_id, 'manual_status' => 'blocked' ) );
			return false;
		}
		if ( $manual_status === 'unblocked' ) {
			Expressive_Logger::info( 'ACCESS', "Acesso concedido: Override manual (desbloqueado)", array( 'user_id' => $user_id ) );
			return true;
		}

		// --- 3. API EXTERNA (Cache Inteligente) ---
		$api_url = get_option( 'lms_external_api_url', '' );
		if ( ! empty( $api_url ) && class_exists( 'Expressive_External_API' ) ) {
			$cached_status = get_user_meta( $user_id, '_lms_elite_api_status', true );
			$last_check    = get_user_meta( $user_id, '_lms_elite_api_last_check', true );
			$one_day       = 24 * HOUR_IN_SECONDS;

			// Se ativo e verificado nas últimas 24h, evita bater na API
			if ( $cached_status === 'active' && ( time() - (int) $last_check ) < $one_day ) {
				Expressive_Logger::debug( 'ACCESS', "Acesso concedido: Cache de API válido (24h)", array( 'user_id' => $user_id ) );
				return true;
			}

			// Se inativo ou cache expirado, faz a verificação em tempo real
			Expressive_Logger::info( 'ACCESS', "Consultando API externa (cache expirado ou inativo)", array( 'user_id' => $user_id, 'cached_status' => $cached_status ) );
			$api_status = Expressive_External_API::check_user_status( $user_id );
			if ( $api_status === 'active' ) {
				Expressive_Logger::info( 'ACCESS', "Acesso concedido via API externa", array( 'user_id' => $user_id ) );
				return true;
			}
			if ( $api_status === 'inactive' ) {
				Expressive_Logger::warning( 'ACCESS', "Acesso NEGADO via API externa", array( 'user_id' => $user_id ) );
				return false;
			}
			Expressive_Logger::warning( 'ACCESS', "API retornou null (erro/timeout), fallback para status local", array( 'user_id' => $user_id ) );
		}

		// --- 4. STATUS LOCAL (mesma lógica da página Assinantes) ---
		$local_status = get_user_meta( $user_id, '_lms_subscription_status', true );
		if ( $local_status === 'suspended' ) {
			Expressive_Logger::warning( 'ACCESS', "Acesso BLOQUEADO: Status local suspenso", array( 'user_id' => $user_id ) );
			return false;
		}

		// --- 5. WOOCOMMERCE SUBSCRIPTIONS (Fallback final) ---
		if ( function_exists( 'wcs_user_has_subscription' ) ) {
			$wc_active = wcs_user_has_subscription( $user_id, '', 'active' );
			Expressive_Logger::debug( 'ACCESS', "Fallback WooCommerce Subscriptions", array( 'user_id' => $user_id, 'wc_active' => $wc_active ) );
			return $wc_active;
		}

		// Sem API, sem suspensão local, sem WooCommerce = acesso liberado por padrão
		Expressive_Logger::debug( 'ACCESS', "Acesso concedido: Nenhuma restrição encontrada (padrão)", array( 'user_id' => $user_id ) );
		return true;
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
		} elseif ( $status === 'active' || $status === 'none' ) {
			update_user_meta( $user_id, '_lms_subscription_status', 'active' );
			update_user_meta( $user_id, '_lms_elite_manual_status', 'none' );
			delete_user_meta( $user_id, '_lms_elite_api_status' ); // Clean up cache to re-trigger API check
			Expressive_Logger::info( 'ACCESS', "Status de acesso ATUALIZADO para: AUTOMÁTICO/ATIVO", array( 'target_user' => $user_id ) );
		} elseif ( $status === 'unblocked' ) {
			update_user_meta( $user_id, '_lms_subscription_status', 'active' );
			update_user_meta( $user_id, '_lms_elite_manual_status', 'unblocked' );
			Expressive_Logger::info( 'ACCESS', "Status de acesso ATUALIZADO para: LIBERADO (MANUAL)", array( 'target_user' => $user_id ) );
		}
	}

}
