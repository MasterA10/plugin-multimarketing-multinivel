<?php

/**
 * The WooCommerce discount functionality based on User Meta and Roles.
 *
 * @link       https://dominai.cloud
 * @since      1.0.0
 *
 * @package    Multinivel_marketing
 * @subpackage Multinivel_marketing/includes
 */

class Multinivel_marketing_WC_Discounts {

	/**
	 * All possible field names the category might be stored under.
	 * The REAL field from the checkout form is 'billing_usuario'.
	 */
	private $possible_keys = array(
		'billing_usuario',
		'user_registration_radio_1771803100',
		'billing_user_registration_radio_1771803100',
	);

	/**
	 * Ensure the custom roles "Autoridade" and "Educadora" exist.
	 */
	public function ensure_roles_exist() {
		$roles = array(
			'autoridade' => 'Autoridade (30% OFF)',
			'educadora'  => 'Educadora (40% OFF)',
		);

		foreach ( $roles as $role_slug => $role_name ) {
			if ( ! get_role( $role_slug ) ) {
				$base_role = get_role( 'customer' ) ? 'customer' : 'subscriber';
				$capabilities = get_role( $base_role )->capabilities;
				add_role( $role_slug, $role_name, $capabilities );
			}
		}
	}

	/**
	 * Log debugging information (if WP_DEBUG is enabled).
	 */
	private function log( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Multinivel MLM] ' . $message );
		}
	}

	/**
	 * Detect the user's category from all possible sources.
	 * Returns the lowercase category string or empty string.
	 */
	private function detect_user_category() {
		$categoria = '';

		// === SOURCE 1: Direct $_POST (during actual checkout submission or registration) ===
		foreach ( $this->possible_keys as $key ) {
			if ( isset( $_POST[ $key ] ) && ! empty( $_POST[ $key ] ) ) {
				$this->log( "Detectado via \$_POST: $key = " . $_POST[ $key ] );
				$categoria = sanitize_text_field( $_POST[ $key ] );
				break;
			}
		}

		// === SOURCE 2: $_POST['post_data'] (during AJAX cart updates) ===
		if ( empty( $categoria ) && isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $post_data );
			foreach ( $this->possible_keys as $key ) {
				if ( isset( $post_data[ $key ] ) && ! empty( $post_data[ $key ] ) ) {
					$this->log( "Detectado via AJAX post_data: $key = " . $post_data[ $key ] );
					$categoria = sanitize_text_field( $post_data[ $key ] );
					break;
				}
			}
		}

		// === SOURCE 3: User Meta (for already registered users) ===
		if ( empty( $categoria ) && is_user_logged_in() ) {
			$user_id = get_current_user_id();
			foreach ( $this->possible_keys as $key ) {
				$val = get_user_meta( $user_id, $key, true );
				if ( ! empty( $val ) ) {
					$categoria = sanitize_text_field( $val );
					break;
				}
			}
		}

		return strtolower( trim( $categoria ) );
	}

	/**
	 * Get the discount percentage and label for a given category.
	 *
	 * @param string $categoria
	 * @return array ['percentual' => float, 'label' => string]
	 */
	private function get_discount_for_category( $categoria ) {
		switch ( $categoria ) {
			case 'educadora':
				return array( 'percentual' => 0.40, 'label' => 'Desconto Educadora (40%)' );
			case 'autoridade':
				return array( 'percentual' => 0.30, 'label' => 'Desconto Autoridade (30%)' );
			default:
				return array( 'percentual' => 0, 'label' => '' );
		}
	}

	/**
	 * Apply discounted fees to the cart.
	 *
	 * @param WC_Cart $cart
	 */
	public function apply_custom_discounts( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

		$categoria = $this->detect_user_category();
		$discount = $this->get_discount_for_category( $categoria );

		// Fallback: check WordPress roles if no category detected
		if ( $discount['percentual'] === 0 && is_user_logged_in() ) {
			$user = wp_get_current_user();
			if ( in_array( 'educadora', (array) $user->roles ) ) {
				$discount = $this->get_discount_for_category( 'educadora' );
			} elseif ( in_array( 'autoridade', (array) $user->roles ) ) {
				$discount = $this->get_discount_for_category( 'autoridade' );
			}
		}

		if ( $discount['percentual'] > 0 ) {
			$subtotal = $cart->get_subtotal();
			$valor_desconto = $subtotal * $discount['percentual'];
			$cart->add_fee( __( $discount['label'], 'woocommerce' ), -$valor_desconto );
		}
	}

	/**
	 * Assign role when a new user is created (e.g. My Account registration).
	 *
	 * @param int $customer_id
	 */
	public function assign_role_on_registration( $customer_id ) {
		if ( ! $customer_id ) return;

		$this->log( "Processando Registro de Novo Cliente: ID $customer_id" );

		$categoria = $this->detect_user_category();

		if ( ! empty( $categoria ) ) {
			$this->set_user_role_by_category( $customer_id, $categoria );
		}
	}

	/**
	 * Assign the correct role to a user after checkout.
	 *
	 * @param int $order_id
	 * @param array $posted_data
	 * @param WC_Order $order
	 */
	public function assign_role_on_checkout( $order_id, $posted_data, $order ) {
		$user_id = $order->get_user_id();
		if ( ! $user_id ) return;

		$this->log( "Processando Pedido para Role: ID $user_id" );

		$categoria = $this->detect_user_category();

		// Also check order meta (Checkout Field Editor saves here)
		if ( empty( $categoria ) ) {
			foreach ( $this->possible_keys as $key ) {
				$val = $order->get_meta( $key );
				if ( ! empty( $val ) ) {
					$categoria = strtolower( trim( sanitize_text_field( $val ) ) );
					$this->log( "Detectado via Order Meta: $key = $categoria" );
					break;
				}
			}
		}

		if ( ! empty( $categoria ) ) {
			$this->set_user_role_by_category( $user_id, $categoria );
		}
	}

	/**
	 * Internal helper to set user role and persist meta.
	 */
	private function set_user_role_by_category( $user_id, $categoria ) {
		$discount = $this->get_discount_for_category( $categoria );
		
		if ( $discount['percentual'] > 0 ) {
			$user = new WP_User( $user_id );
			$user->set_role( $categoria ); // 'educadora' or 'autoridade'
			// Persist meta for future lookups
			update_user_meta( $user_id, 'billing_usuario', $categoria );
			update_user_meta( $user_id, 'user_registration_radio_1771803100', $categoria );
			
			// Flag as educator for high-performance dashboard if role is educadora
			if ( $categoria === 'educadora' ) {
				update_user_meta( $user_id, '_lms_is_educator', 'yes' );
			}
		}
	}
}
