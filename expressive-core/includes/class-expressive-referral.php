<?php

class Expressive_Referral {

	public function register_hooks() {
		// 1. Capture and save cookie to order (Classic & Block Support)
		add_action( 'woocommerce_checkout_create_order', array( $this, 'save_referral_to_order' ), 10, 2 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_referral_to_order_meta' ), 10, 1 );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'save_referral_to_order_blocks' ), 10, 2 );
		
		// 2. Process referral on order completion OR processing (to support test gateways that stop at processing)
		add_action( 'woocommerce_order_status_completed', array( $this, 'process_completed_referral' ) );
		add_action( 'woocommerce_order_status_processing', array( $this, 'process_completed_referral' ) );

		// 3. Handle incoming referral URL early on init for better cookie reliability
		add_action( 'init', array( $this, 'handle_referral_cookie' ) );

		// 4. Save directly to User Profile
		add_action( 'user_register', array( $this, 'save_referral_on_registration' ) );

		// 5. Recovery tool
		add_action( 'init', array( $this, 'trigger_recovery' ) );
	}

	public function save_referral_on_registration( $user_id ) {
		if ( isset( $_COOKIE['exp_ref'] ) ) {
			update_user_meta( $user_id, '_exp_referred_by', sanitize_text_field( $_COOKIE['exp_ref'] ) );
		}
	}

	/**
	 * Save the 'exp_ref' cookie to the WooCommerce order metadata (Classic Checkout Hook).
	 */
	public function save_referral_to_order( $order, $data ) {
		if ( isset( $_COOKIE['exp_ref'] ) ) {
			$referral_code = sanitize_text_field( $_COOKIE['exp_ref'] );
			$order->update_meta_data( '_exp_referred_by', $referral_code );
		}
	}

	/**
	 * Fallback for classic checkout to ensure meta is saved.
	 */
	public function save_referral_to_order_meta( $order_id ) {
		if ( isset( $_COOKIE['exp_ref'] ) ) {
			$referral_code = sanitize_text_field( $_COOKIE['exp_ref'] );
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$order->update_meta_data( '_exp_referred_by', $referral_code );
				$order->save();
			}
		}
	}

	/**
	 * Support for WooCommerce Blocks Checkout.
	 */
	public function save_referral_to_order_blocks( $order, $request ) {
		if ( isset( $_COOKIE['exp_ref'] ) ) {
			$referral_code = sanitize_text_field( $_COOKIE['exp_ref'] );
			$order->update_meta_data( '_exp_referred_by', $referral_code );
		}
	}

	/**
	 * Process the referral when an order is completed.
	 */
	public function process_completed_referral( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) return;

		$referral_code = $order->get_meta( '_exp_referred_by' );
		$authority_id = $order->get_user_id();

		// FALLBACK: Se o pedido não tem a tag, procura no Perfil do Usuário
		if ( ! $referral_code && $authority_id ) {
			$referral_code = get_user_meta( $authority_id, '_exp_referred_by', true );
		}

		if ( ! $referral_code ) {
			return ;
		}

		// Try to find the educator by referral code
		$educator = $this->find_educator_by_code( $referral_code );
		
		if ( ! $educator ) {
			return;
		}

		$authority_id = $order->get_user_id();
		if ( ! $authority_id ) {
			return ; // Guest checkout not supported for referrals yet
		}

		// --- FUTURE-PROOFING: Restrição de Produtos ---
		// Verifica se o pedido contém pelo menos um produto válido para indicação.
		// Atualmente aceita qualquer produto, mas futuramente podemos filtrar por ID.
		$has_eligible_product = false;
		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			if ( $this->is_product_eligible_for_referral( $product_id ) ) {
				$has_eligible_product = true;
				break;
			}
		}

		if ( ! $has_eligible_product ) {
			return; // Nenhum produto qualificável neste pedido
		}
		// -----------------------------------------------

		$this->register_referral_link( $educator->ID, $authority_id, $order_id, $order->get_total() );
	}

	/**
	 * Verifica se o produto atual gera indicação/pontuação na rede.
	 * Por padrão, aceita todos os produtos (true).
	 */
	private function is_product_eligible_for_referral( $product_id ) {
		// Implementação futura:
		// $eligible_ids = array( 123, 456 ); // Lista de IDs de cursos qualificáveis
		// return in_array( $product_id, $eligible_ids );

		return true; // Por enquanto, validar qualquer compra
	}

	private function find_educator_by_code( $code ) {
		// Search by user login or custom meta
		$user = get_user_by( 'login', $code );
		if ( ! $user ) {
			// Fallback: search by custom meta _lms_ref_code
			$users = get_users( array(
				'meta_key'   => '_lms_ref_code',
				'meta_value' => $code,
				'number'     => 1,
			) );
			if ( ! empty( $users ) ) {
				$user = $users[0];
			}
		}
		return $user;
	}

	public function register_referral_link( $educator_id, $authority_id, $order_id = 0, $order_total = 0 ) {
		global $wpdb;
		$table_referrals = $wpdb->prefix . 'lms_referrals';

		// Check if it already exists (each authority can only be referred ONCE, to a SINGLE educator)
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table_referrals WHERE authority_id = %d",
			$authority_id
		) );

		if ( ! $exists ) {
			$wpdb->insert(
				$table_referrals,
				array(
					'educator_id'  => $educator_id,
					'authority_id' => $authority_id,
					'order_id'     => $order_id,
					'order_total'  => $order_total,
				),
				array( '%d', '%d', '%d', '%f' )
			);

			// Trigger Gamification Engine update
			do_action( 'lms_new_referral_registered', $educator_id, $authority_id, $order_id );
		}
	}

	/**
	 * Handle referral URL parameter and set cookie.
	 */
	public function handle_referral_cookie() {
		if ( ! isset( $_GET['ref'] ) ) {
			return ;
		}

		$ref_code = sanitize_text_field( $_GET['ref'] );
		$educator = $this->find_educator_by_code( $ref_code );

		if ( $educator ) {
			// Set cookie for 30 days
			setcookie( 'exp_ref', $ref_code, time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN );

			// Se o usuário já estiver logado (ex: clicou no link após logar), grava direto no perfil
			if ( is_user_logged_in() ) {
				update_user_meta( get_current_user_id(), '_exp_referred_by', $ref_code );
			}
		}
	}

	/**
	 * FALLBACK / RECOVERY SCRIPT
	 * Processa todos os pedidos completados ou em processamento antigos e tenta conectá-los.
	 */
	public function trigger_recovery() {
		if ( isset( $_GET['recover_referrals'] ) && current_user_can( 'manage_options' ) ) {
			$orders = wc_get_orders( array(
				'status' => array( 'wc-processing', 'wc-completed' ),
				'limit'  => -1,
			) );

			foreach ( $orders as $order ) {
				$this->process_completed_referral( $order->get_id() );
			}
			wp_die('<h1>Recuperação Concluída</h1><p>Todas as compras antigas qualificáveis foram conectadas aos educadores.</p><a href="/wp-admin/">Voltar ao Painel</a>');
		}
	}

	/**
	 * Retrieve referrer display name from cookie if valid.
	 */
	public function get_referrer_name_from_cookie() {
		if ( ! isset( $_COOKIE['exp_ref'] ) ) {
			return false;
		}

		$ref_code = sanitize_text_field( $_COOKIE['exp_ref'] );
		$educator = $this->find_educator_by_code( $ref_code );

		return $educator ? $educator->display_name : false;
	}

	/**
	 * Get the top educators ranked by referral count for the current year.
	 */
	/**
	 * Get the top educators ranked by referral count for the current year.
	 */
	public static function get_annual_ranking( $limit = 50 ) {
		global $wpdb;
		$table_referrals = $wpdb->prefix . 'lms_referrals';
		$current_year = date( 'Y' );

		// Pega todos os educadores do sistema para forçar que apareçam na lista (mesmo com 0 indicações)
		$educators = get_users( array(
			'role__in' => array( 'educadora', 'administrator' ),
			'fields'   => 'ID'
		) );

		$results = array();
		if ( empty( $educators ) ) {
			return $results;
		}

		foreach ( $educators as $educator_id ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM $table_referrals WHERE educator_id = %d AND YEAR(created_at) = %d",
				$educator_id, $current_year
			) );

			$results[] = (object) array(
				'educator_id' => $educator_id,
				'ref_count'   => (int) $count
			);
		}

		// Ordena do maior pro menor
		usort( $results, function( $a, $b ) {
			return $b->ref_count - $a->ref_count;
		});

		return array_slice( $results, 0, $limit );
	}

	/**
	 * Get the global rank position of a specific user.
	 * Returns the position as an integer (1 = 1st place).
	 */
	public static function get_user_rank_position( $user_id ) {
		global $wpdb;
		$table_referrals = $wpdb->prefix . 'lms_referrals';
		
		// 1. Get the count for this specific user
		$user_count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table_referrals WHERE educator_id = %d",
			$user_id
		) );

		if ( $user_count === 0 ) {
			return 0; // Not ranked yet
		}

		// 2. Count how many people have MORE referrals than this user
		$higher_ranked_count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM (
				SELECT educator_id, COUNT(*) as c 
				FROM $table_referrals 
				GROUP BY educator_id 
				HAVING c > %d
			) as top_dogs",
			$user_count
		) );

		return $higher_ranked_count + 1;
	}

	/**
	 * Get the list of referrals (sales) for a specific educator.
	 */
	public function get_educator_referrals( $educator_id ) {
		global $wpdb;
		$table_referrals = $wpdb->prefix . 'lms_referrals';
		
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table_referrals WHERE educator_id = %d ORDER BY created_at DESC",
			$educator_id
		) );

		return $results;
	}

}
