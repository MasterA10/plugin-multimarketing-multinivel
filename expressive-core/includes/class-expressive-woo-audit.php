<?php

/**
 * Expressive Woo Audit — Monitoramento de eventos do WooCommerce.
 *
 * Registra criação de pedidos, mudanças de status e pagamentos concluídos.
 * Só funciona se a opção 'lms_enable_woo_logging' estiver como 'yes'.
 */
class Expressive_Woo_Audit {

	public function register_hooks() {
		// 1. Criação do pedido no checkout
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'log_order_created' ), 10, 3 );

		// 2. Mudança de status do pedido
		add_action( 'woocommerce_order_status_changed', array( $this, 'log_status_change' ), 10, 4 );

		// 3. Pagamento concluído
		add_action( 'woocommerce_payment_complete', array( $this, 'log_payment_complete' ), 10, 1 );

		// 4. Forçar status concluído (Debug Tool) - Hooking later for reliability
		add_action( 'woocommerce_order_status_changed', array( $this, 'maybe_force_order_completed' ), 20, 4 );
	}

	/**
	 * Verifica se o log do WooCommerce está habilitado.
	 */
	private function is_enabled() {
		return get_option( 'lms_enable_woo_logging', 'no' ) === 'yes';
	}

	/**
	 * Verifica se a ferramenta de forçar conclusão está ativa.
	 */
	private function is_force_completed_enabled() {
		return get_option( 'lms_force_orders_completed', 'no' ) === 'yes';
	}

	/**
	 * Força o pedido para concluído se a ferramenta de debug estiver ativa.
	 * Hooked to woocommerce_order_status_changed for high reliability.
	 */
	public function maybe_force_order_completed( $order_id, $from, $to, $order ) {
		static $forcing_completed = false;
		
		if ( $forcing_completed ) return;
		if ( ! $this->is_force_completed_enabled() ) return;

		// Se mudar para qualquer coisa que não seja 'completed', forçamos de novo
		if ( $to !== 'completed' ) {
			$forcing_completed = true;
			$order->update_status( 'completed', '[Debug] Status forçado via Elite LMS Logs.' );
			$forcing_completed = false;
			
			Expressive_Logger::warning( 'WOO', "Status de Pedido FORÇADO para Concluído", array( 
				'order_id' => $order_id, 
				'original_status' => $to 
			) );
		}
	}

	/**
	 * Loga quando um novo pedido é processado no checkout.
	 */
	public function log_order_created( $order_id, $posted_data, $order ) {
		if ( ! $this->is_enabled() ) return;

		$items = array();
		foreach ( $order->get_items() as $item_id => $item ) {
			$items[] = array(
				'id'   => $item->get_product_id(),
				'qty'  => $item->get_quantity(),
				'name' => $item->get_name()
			);
		}

		Expressive_Logger::info( 'WOO', "Pedido Criado via Checkout", array(
			'order_id' => $order_id,
			'total'    => $order->get_total(),
			'items'    => $items,
			'user_id'  => $order->get_user_id() ?: 'guest'
		) );
	}

	/**
	 * Loga mudanças de status de pedido.
	 */
	public function log_status_change( $order_id, $from, $to, $order ) {
		if ( ! $this->is_enabled() ) return;

		Expressive_Logger::info( 'WOO', "Mudança de Status de Pedido", array(
			'order_id' => $order_id,
			'from'     => $from,
			'to'       => $to,
			'user_id'  => $order->get_user_id() ?: 'guest'
		) );
	}

	/**
	 * Loga quando um pagamento é confirmado.
	 */
	public function log_payment_complete( $order_id ) {
		if ( ! $this->is_enabled() ) return;

		Expressive_Logger::info( 'WOO', "Pagamento Confirmado", array(
			'order_id' => $order_id
		) );
	}
}
