<?php

class Expressive_Referral {

	/**
	 * Define se um usuário é Educador com base nas permissões e meta definidos no cadastro/faturamento.
	 */
	public static function is_educator( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return false;

		if ( get_user_meta( $user_id, '_lms_is_educator', true ) === 'yes' ) return true;
		if ( in_array( 'educadora', (array) $user->roles ) ) return true;
		if ( in_array( 'administrator', (array) $user->roles ) ) return true;

		return false;
	}

	/**
	 * Define se um usuário é Autoridade.
	 */
	public static function is_authority( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return false;
		if ( in_array( 'autoridade', (array) $user->roles ) ) return true;
		return false;
	}

	/**
	 * Retorna true se a assinatura está ativa, false caso contrário (bloqueia o acesso).
	 */
	public static function has_active_subscription( $user_id ) {
		$status = get_user_meta( $user_id, '_lms_subscription_status', true );
		// Por padrão, se não tiver a flag de suspensão, o acesso está liberado.
		if ( $status === 'suspended' ) {
			return false;
		}
		
		return true;
	}

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
			$ref_code = sanitize_text_field( $_COOKIE['exp_ref'] );
			update_user_meta( $user_id, '_exp_referred_by', $ref_code );
			Expressive_Logger::info( 'REFERRAL', "Afiliado vinculado ao novo perfil via Cookie", array( 'user_id' => $user_id, 'ref_code' => $ref_code ) );
		} else {
			Expressive_Logger::debug( 'REFERRAL', "Novo usuário registrado sem cookie de indicação", array( 'user_id' => $user_id ) );
		}
	}

	/**
	 * Save the 'exp_ref' cookie to the WooCommerce order metadata (Classic Checkout Hook).
	 */
	public function save_referral_to_order( $order, $data ) {
		if ( isset( $_COOKIE['exp_ref'] ) ) {
			$referral_code = sanitize_text_field( $_COOKIE['exp_ref'] );
			$order->update_meta_data( '_exp_referred_by', $referral_code );
			Expressive_Logger::info( 'REFERRAL', "Cookie vinculado ao pedido (create_order)", array( 'ref_code' => $referral_code, 'order_id' => $order->get_id() ) );
		} else {
			Expressive_Logger::debug( 'REFERRAL', "Pedido criado sem cookie de indicação presente", array( 'order_id' => $order->get_id() ) );
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

		// Check if order is eligible (must be processing or completed)
		if ( ! in_array( $order->get_status(), array( 'processing', 'completed' ) ) ) {
			Expressive_Logger::debug( 'REFERRAL', "Conversão em espera: aguardando confirmação de pagamento", array( 'order_id' => $order_id, 'status' => $order->get_status() ) );
			return;
		}

		// FALLBACK: Se o pedido não tem a tag, procura no Perfil do Usuário
		if ( ! $referral_code && $authority_id ) {
			$referral_code = get_user_meta( $authority_id, '_exp_referred_by', true );
		}

		if ( ! $referral_code ) {
			Expressive_Logger::debug( 'REFERRAL', "Pedido sem código de indicação", array( 'order_id' => $order_id ) );
			return ;
		}

		// Try to find the educator by referral code
		$educator = $this->find_educator_by_code( $referral_code );
		
		if ( ! $educator ) {
			Expressive_Logger::warning( 'REFERRAL', "Educador não encontrado para o código", array( 'order_id' => $order_id, 'ref_code' => $referral_code ) );
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
			Expressive_Logger::info( 'REFERRAL', "Pedido ignorado: nenhum produto elegível", array( 'order_id' => $order_id, 'ref_code' => $referral_code ) );
			return;
		}
		// -----------------------------------------------

		$order_total = $order->get_total();
		$commission_percentage = get_option( 'lms_commission_percentage', 10 );
		$commission = $order_total * ( $commission_percentage / 100 );
		$referred_role = $this->is_educator( $authority_id ) ? 'educadora' : 'autoridade';

		$this->register_referral_link( $educator->ID, $authority_id, $order_id, $order_total, $commission, $referred_role );

		Expressive_Logger::info( 'REFERRAL', "Indicação processada com sucesso", array(
			'order_id'     => $order_id,
			'educator_id'  => $educator->ID,
			'authority_id' => $authority_id,
			'order_total'  => $order_total,
			'commission'   => $commission,
			'role'         => $referred_role
		) );

		// Clear referral cookie after successful conversion
		if ( isset( $_COOKIE['exp_ref'] ) ) {
			setcookie( 'exp_ref', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
			Expressive_Logger::info( 'REFERRAL', "Cookie exp_ref removido após conversão", array( 'order_id' => $order_id ) );
		}
	}

	/**
	 * Verifica se o produto atual gera indicação/pontuação na rede.
	 * Por padrão, aceita todos os produtos (true).
	 */
	private function is_product_eligible_for_referral( $product_id ) {
		$all_eligible = get_option( 'lms_all_products_eligible', 'yes' );
		if ( $all_eligible === 'yes' ) {
			return true;
		}

		$eligible_ids_raw = get_option( 'lms_eligible_products', '' );
		if ( empty( $eligible_ids_raw ) ) {
			return false; // Se não tem IDs e não é "todos", nenhum é elegível
		}

		$eligible_ids = array_map( 'intval', explode( ',', $eligible_ids_raw ) );
		return in_array( (int) $product_id, $eligible_ids );
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

	public function register_referral_link( $educator_id, $authority_id, $order_id = 0, $order_total = 0, $commission = 0, $role = '' ) {
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
					'educator_id'       => $educator_id,
					'authority_id'      => $authority_id,
					'order_id'          => $order_id,
					'order_total'       => $order_total,
					'commission_amount' => $commission,
					'referred_role'     => $role,
				),
				array( '%d', '%d', '%d', '%f', '%f', '%s' )
			);

			// Clear ranking cache to ensure real-time leaderboard updates
			self::clear_ranking_cache();

			// Trigger Gamification Engine update
			do_action( 'lms_new_referral_registered', $educator_id, $authority_id, $order_id );
		}
	}

	/**
	 * Clears the annual ranking transient cache to force an update.
	 */
	public static function clear_ranking_cache() {
		$current_year = date( 'Y' );
		delete_transient( 'elite_annual_ranking_' . $current_year . '_50' );
		delete_transient( 'elite_annual_ranking_' . $current_year . '_10' );
		delete_transient( 'elite_annual_ranking_' . $current_year . '_5' );
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
			// Path '/' and empty domain for maximum cross-subdomain/AJAX compatibility
			setcookie( 'exp_ref', $ref_code, time() + ( 30 * DAY_IN_SECONDS ), '/', '', is_ssl(), true );
			Expressive_Logger::info( 'REFERRAL', "Cookie de indicação DEFINIDO com sucesso", array( 
				'ref_code'    => $ref_code, 
				'educator_id' => $educator->ID,
				'educator'    => $educator->display_name,
				'landing_url' => $_SERVER['REQUEST_URI']
			) );

			if ( is_user_logged_in() ) {
				update_user_meta( get_current_user_id(), '_exp_referred_by', $ref_code );
				Expressive_Logger::info( 'REFERRAL', "Afiliação vinculada permanentemente ao perfil logado", array( 'user_id' => get_current_user_id(), 'ref_code' => $ref_code ) );
			}
		} else {
			Expressive_Logger::warning( 'REFERRAL', "Tentativa de indicação INVÁLIDA: Código não encontrado", array( 'ref_code' => $ref_code, 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
		}
	}

	/**
	 * FALLBACK / RECOVERY SCRIPT
	 * Processa todos os pedidos completados ou em processamento antigos e tenta conectá-los.
	 */
	public function trigger_recovery() {
		if ( isset( $_GET['recover_referrals'] ) && current_user_can( 'manage_options' ) ) {
			// 1. Re-process old orders
			$orders = wc_get_orders( array(
				'status' => array( 'wc-processing', 'wc-completed' ),
				'limit'  => -1,
			) );

			foreach ( $orders as $order ) {
				$this->process_completed_referral( $order->get_id() );
			}

			// 2. Force clear ranking cache
			self::clear_ranking_cache();

			wp_die('<h1>Recuperação e Cache Concluídos</h1><p>Todas as compras foram re-processadas e o Ranking foi atualizado.</p><a href="/wp-admin/">Voltar ao Painel</a>');
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
	 * Optimized with a single aggregated query and WordPress Transients.
	 */
	public static function get_annual_ranking( $limit = 50 ) {
		global $wpdb;
		$table_referrals = $wpdb->prefix . 'lms_referrals';
		$current_year = date( 'Y' );
		$cache_key = 'elite_annual_ranking_' . $current_year . '_' . $limit;

		// 1. Try to get from Cache (Transient)
		$results = get_transient( $cache_key );
		if ( $results !== false ) {
			return $results;
		}

		// 2. Aggregated Query: Single database hit instead of N+1
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT educator_id, 
					COUNT(*) as ref_count, 
					SUM(order_total) as total_sales, 
					SUM(commission_amount) as total_commissions 
			 FROM $table_referrals 
			 WHERE YEAR(created_at) = %d 
			 GROUP BY educator_id 
			 ORDER BY ref_count DESC, total_sales DESC 
			 LIMIT %d",
			$current_year, $limit
		) );

		// 3. Cast values to correct types
		if ( ! empty( $results ) ) {
			foreach ( $results as &$row ) {
				$row->ref_count         = (int) $row->ref_count;
				$row->total_sales       = (float) $row->total_sales;
				$row->total_commissions = (float) $row->total_commissions;
			}
		} else {
			$results = array();
		}

		// 4. Set Cache for 1 hour (3600 seconds)
		set_transient( $cache_key, $results, HOUR_IN_SECONDS );

		return $results;
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
