<?php

class Expressive_Public {

	public function register_shortcodes() {
		add_shortcode( 'expressive_educator_dashboard', array( $this, 'render_educator_dashboard' ) );
		add_shortcode( 'expressive_login', array( $this, 'render_login_form' ) );
		add_shortcode( 'expressive_dashboard', array( $this, 'render_member_dashboard' ) );
		add_shortcode( 'lms_leaderboard', array( $this, 'render_leaderboard' ) );
		add_shortcode( 'pmu_academy_team', array( $this, 'render_academy_team' ) );

		// Checkout hooks
		add_action( 'woocommerce_before_checkout_form', array( $this, 'display_referrer_at_checkout_hook' ) );
		add_filter( 'the_content', array( $this, 'maybe_prepend_referral_notice' ) );
	}

	public function render_login_form() {
		if ( is_user_logged_in() ) {
			return '<p>Você já está logado. <a href="' . site_url( '/area-de-membros/' ) . '">Ir para a Área de Membros</a></p>';
		}
		
		ob_start();
		wp_login_form( array( 'redirect' => site_url( '/area-de-membros/' ) ) );
		return ob_get_clean();
	}

	public function render_member_dashboard() {
		if ( ! is_user_logged_in() ) {
			return '<p>Por favor, faça <a href="' . site_url( '/login/' ) . '">login</a> para acessar.</p>';
		}

		$output = '<div class="expressive-dashboard-container">';
		$output .= '<h2>Bem-vindo à sua Área de Elite</h2>';
		
		if ( isset( $_GET['restricted'] ) ) {
			$output .= '<div class="expressive-notice-warning" style="background: rgba(212, 175, 55, 0.1); border: 1px solid #D4AF37; padding: 15px; margin-bottom: 20px; color: #D4AF37;">';
			$output .= '<strong>Acesso Restrito:</strong> Esta aula exige uma assinatura ativa. Escolha um plano abaixo para liberar o acesso.';
			$output .= '</div>';
		}

		$output .= '<p>Explore seus cursos e conteúdos exclusivos abaixo.</p>';
		// Grid of courses will come here
		
		// Educator Integration
		$user_id = get_current_user_id();
		if ( current_user_can( 'edit_posts' ) || get_user_meta( $user_id, '_lms_is_educator', true ) || in_array( 'educadora', (array) wp_get_current_user()->roles ) ) {
			$output .= '<hr style="border: 0; border-top: 1px solid #333; margin: 40px 0;">';
			$output .= $this->get_educator_dashboard_html( $user_id );
		}

		$output .= '</div>';

		return $output;
	}

	public function render_educator_dashboard() {
		$user_id = get_current_user_id();
		if ( ! current_user_can( 'edit_posts' ) && ! get_user_meta( $user_id, '_lms_is_educator', true ) && ! in_array( 'educadora', (array) wp_get_current_user()->roles ) ) {
			return '<p>Acesso restrito a educadores.</p>';
		}

		return $this->get_educator_dashboard_html( $user_id );
	}

	/**
	 * Internal helper to generate the educator dashboard HTML for both 
	 * the member area and the standalone shortcode.
	 */
	private function get_educator_dashboard_html( $user_id ) {
		$rank_name = get_user_meta( $user_id, '_lms_rank_name', true ) ?: 'Bronze';
		$rank_level = get_user_meta( $user_id, '_lms_rank_level', true ) ?: 1;
		$ref_count = $this->get_referral_count( $user_id );
		
		// Next Rank Math
		$next_level = ($rank_level < 5) ? $rank_level + 1 : 5;
		$sales_for_current_threshold = ($rank_level - 1) * 10;
		$sales_for_next_threshold = $rank_level * 10;
		$progress_towards_next = min( 100, (($ref_count - $sales_for_current_threshold) / 10) * 100 );
		$needed_for_next = max( 0, $sales_for_next_threshold - $ref_count );

		$output = '<div class="expressive-educator-dashboard">';
		$output .= '<h2>Painel do Educador</h2>';
		
		// Progress Bar Section
		if ( $rank_level < 5 ) {
			$output .= '<div class="rank-progress-wrapper">';
			$output .= '  <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">';
			$output .= '    <span style="color: #fff; font-weight: bold;">Nível atual: ' . $rank_name . '</span>';
			$output .= '    <span style="color: #D4AF37;">Mais ' . $needed_for_next . ' vendas para o próximo nível</span>';
			$output .= '  </div>';
			$output .= '  <div class="progress-bar-bg">';
			$output .= '    <div class="progress-bar-fill" style="width: ' . $progress_towards_next . '%;"></div>';
			$output .= '  </div>';
			$output .= '</div>';
		}

		$output .= '<div class="educator-stats">';
		$output .= '  <div class="stat-card">';
		$output .= '    <span>Total de Autoridades</span>';
		$output .= '    <div class="stat-value">' . $this->get_referral_count($user_id) . '</div>';
		$output .= '  </div>';
		$output .= '</div>';

		// Vendas Realizadas Table
		$referral_sys = new Expressive_Referral();
		$referrals = $referral_sys->get_educator_referrals( $user_id );
		
		if ( ! empty( $referrals ) ) {
			$output .= '<div class="expressive-sales-history" style="margin-top: 30px;">';
			$output .= '  <h3 style="color: #D4AF37; border-bottom: 1px solid #333; padding-bottom: 10px;">Suas Vendas (Indicações)</h3>';
			$output .= '  <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9em;">';
			$output .= '    <thead>';
			$output .= '      <tr style="border-bottom: 1px solid #444; color: #D4AF37; text-align: left;">';
			$output .= '        <th style="padding: 10px;">Data</th>';
			$output .= '        <th style="padding: 10px;">Autoridade</th>';
			$output .= '        <th style="padding: 10px;">Valor</th>';
			$output .= '      </tr>';
			$output .= '    </thead>';
			$output .= '    <tbody>';
			
			foreach ( $referrals as $ref ) {
				$authority = get_userdata( $ref->authority_id );
				$auth_name = $authority ? $authority->display_name : 'Usuário Removido';
				$date = date_i18n( get_option( 'date_format' ), strtotime( $ref->created_at ) );
				$total = wc_price( $ref->order_total );
				
				$output .= '<tr style="border-bottom: 1px solid #333;">';
				$output .= '  <td style="padding: 10px;">' . $date . '</td>';
				$output .= '  <td style="padding: 10px;">' . esc_html( $auth_name ) . '</td>';
				$output .= '  <td style="padding: 10px; color: #D4AF37; font-weight: bold;">' . $total . '</td>';
				$output .= '</tr>';
			}
			
			$output .= '    </tbody>';
			$output .= '  </table>';
			$output .= '</div>';
		}

		// Referral Link Section
		$ref_link = home_url( '?ref=' . wp_get_current_user()->user_login );
		$output .= '<div class="referral-link-section" style="background: rgba(212, 175, 55, 0.05); border: 1px dashed #D4AF37; padding: 20px; border-radius: 8px; margin: 20px 0;">';
		$output .= '  <h3 style="color: #D4AF37; margin-top: 0;">Seu Link de Indicação</h3>';
		$output .= '  <p style="font-size: 0.9em; margin-bottom: 10px;">Compartilhe este link para registrar novas autoridades sob sua rede:</p>';
		$output .= '  <div style="display: flex; gap: 10px;">';
		$output .= '    <input type="text" value="' . esc_url( $ref_link ) . '" readonly style="flex: 1; background: #1a1a1a; border: 1px solid #333; color: #fff; padding: 10px; border-radius: 4px;">';
		$output .= '    <button onclick="navigator.clipboard.writeText(\'' . esc_url( $ref_link ) . '\'); alert(\'Link copiado!\');" class="btn-gold" style="padding: 10px 20px;">Copiar</button>';
		$output .= '  </div>';
		$output .= '</div>';

		$output .= '<a href="' . admin_url( 'post-new.php?post_type=lms_lesson' ) . '" class="btn-gold"> + Nova Aula</a>';
		$output .= '</div>';

		return $output;
	}

	public function render_leaderboard() {
		global $wpdb;
		$table_referrals = $wpdb->prefix . 'lms_referrals';
		$table_users = $wpdb->prefix . 'users';

		$results = $wpdb->get_results(
			"SELECT educator_id, COUNT(*) as count 
			 FROM $table_referrals 
			 GROUP BY educator_id 
			 ORDER BY count DESC 
			 LIMIT 10"
		);

		$output = '<div class="expressive-leaderboard">';
		$output .= '<h2>Ranking de Elite - Hall da Fama</h2>';
		$output .= '<table>';
		$output .= '  <thead><tr><th>#</th><th>Educador</th><th>Nível</th><th>Autoridades</th></tr></thead>';
		$output .= '  <tbody>';

		$rank = 1;
		foreach ( $results as $row ) {
			$user = get_userdata( $row->educator_id );
			$rank_name = get_user_meta( $row->educator_id, '_lms_rank_name', true ) ?: 'Bronze';
			$output .= '<tr>';
			$output .= '  <td style="text-align: center;">' . $rank++ . '</td>';
			$output .= '  <td>' . esc_html( $user->display_name ) . '</td>';
			$output .= '  <td>' . $rank_name . '</td>';
			$output .= '  <td style="text-align: center;">' . $row->count . '</td>';
			$output .= '</tr>';
		}
		
		$output .= '  </tbody>';
		$output .= '</table>';
		$output .= '</div>';

		return $output;
	}

	private function get_referral_count($user_id) {
		global $wpdb;
		$table_referrals = $wpdb->prefix . 'lms_referrals';
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_referrals WHERE educator_id = %d", $user_id ) );
	}

	/**
	 * Prepend referral notice to the checkout content (Broadest compatibility).
	 */
	public function maybe_prepend_referral_notice( $content ) {
		if ( ! is_checkout() ) {
			return $content;
		}

		$notice = $this->get_referral_notice_html();
		if ( $notice ) {
			return $notice . $content;
		}

		return $content;
	}

	/**
	 * Display the referrer's name at the checkout page (Standard Hook).
	 */
	public function display_referrer_at_checkout_hook() {
		// Prevent double display if the_content filter is already doing it
		if ( ! did_filter( 'the_content' ) ) {
			echo $this->get_referral_notice_html();
		}
	}

	/**
	 * Build the notice HTML based on cookie data.
	 */
	private function get_referral_notice_html() {
		$referral_sys = new Expressive_Referral();
		$referrer_name = $referral_sys->get_referrer_name_from_cookie();

		if ( $referrer_name ) {
			ob_start();
			?>
			<div class="woocommerce-info expressive-referral-notice" style="border-top-color: #D4AF37; margin-bottom: 25px; background-color: #1a1a1a; color: #fff; padding: 20px; border-radius: 4px; border-left: 4px solid #D4AF37;">
				<span style="color: #D4AF37; font-size: 1.2em; margin-right: 10px;">✨</span> 
				<?php echo sprintf( 'Você está sendo indicado por: <strong style="color: #D4AF37;">%s</strong>', esc_html( $referrer_name ) ); ?>
			</div>
			<?php
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Shortcode to render the Academy Team
	 */
	public function render_academy_team() {
		$template_path = EXPRESSIVE_CORE_PATH . 'templates/page-academy-team.php';
		
		if ( ! file_exists( $template_path ) ) {
			return '<p>Template da academia não encontrado.</p>';
		}

		ob_start();
		
		// Set a flag to tell the template NOT to call get_header/get_footer if included via shortcode
		$is_shortcode = true;
		include $template_path;
		
		return ob_get_clean();
	}

}
