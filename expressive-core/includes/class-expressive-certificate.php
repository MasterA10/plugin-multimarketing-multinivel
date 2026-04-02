<?php

class Expressive_Certificate {

	public function register_hooks() {
		add_shortcode( 'lms_certificate_button', array( $this, 'render_certificate_button' ) );
		add_action( 'template_redirect', array( $this, 'handle_certificate_view' ) );
	}

	/**
	 * Check if user is eligible for certificate (>75% completion).
	 */
	public function is_eligible( $user_id, $course_id ) {
		// Logic: Count total lessons in course vs completed lessons by user in that course
		$total_lessons = $this->get_total_lessons_in_course( $course_id );
		if ( $total_lessons === 0 ) return false;

		$completed_count = $this->get_completed_lessons_count( $user_id, $course_id );
		$percentage = ( $completed_count / $total_lessons ) * 100;

		return ( $percentage >= 75 );
	}

	/**
	 * Render the "Baixar Certificado" button.
	 */
	public function render_certificate_button( $atts ) {
		$atts = shortcode_atts( array( 'course_id' => 0 ), $atts );
		$course_id = intval( $atts['course_id'] );
		$user_id = get_current_user_id();

		// Calculate progress directly for the button to ensure matching UI
		$completed_lessons = get_user_meta( $user_id, '_lms_completed_lessons', true ) ?: [];
		$total_lessons = $this->get_total_lessons_in_course( $course_id );
		
		// Map completed lessons to this specific course
		global $wpdb;
		$course_completed_count = 0;
		if ( ! empty( $completed_lessons ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $completed_lessons ), '%d' ) );
			$course_completed_count = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} 
				 WHERE meta_key = '_lms_course_id' AND meta_value = %d 
				 AND post_id IN ($placeholders)",
				array_merge( [ $course_id ], $completed_lessons )
			) );
		}

		$progress_pct = $total_lessons > 0 ? ( $course_completed_count / $total_lessons ) * 100 : 0;

		if ( $progress_pct < 75 ) {
			return '<p style="color: #666; font-size: 0.8rem; text-align: center; font-weight: 500;">Complete pelo menos 75% da jornada para liberar seu certificado.</p>';
		}

		$url = add_query_arg( array(
			'lms_action' => 'view_certificate',
			'course_id'  => $course_id,
			'nonce'      => wp_create_nonce( 'lms_view_cert' )
		), site_url() );

		$output = '<p style="color: #D4AF37; font-size: 0.9rem; text-align: center; font-weight: 800; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.1em;">Parabéns! Você superou os 75% da jornada.</p>';
		$output .= '<a href="' . esc_url( $url ) . '" target="_blank" class="elite-cert-btn" style="display: block; width: 100%; text-align: center; background: #D4AF37; color: #000; padding: 18px 24px; border-radius: 12px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; text-decoration: none; box-shadow: 0 10px 25px rgba(212, 175, 55, 0.2); transition: all 0.3s ease;">🏆 Baixar Certificado de Especialista</a>';
		
		return $output;
	}

	/**
	 * Handle the certificate view page (Luxury HTML Template).
	 */
	public function handle_certificate_view() {
		if ( ! isset( $_GET['lms_action'] ) || $_GET['lms_action'] !== 'view_certificate' ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['nonce'], 'lms_view_cert' ) ) {
			wp_die( 'Acesso negado.' );
		}

		$course_id = intval( $_GET['course_id'] );
		$user_id = get_current_user_id();

		if ( ! $this->is_eligible( $user_id, $course_id ) ) {
			wp_die( 'Você ainda não concluiu 75% deste curso.' );
		}

		$user_data = get_userdata( $user_id );
		$course_title = get_the_title( $course_id );
		$date = date( 'd/m/Y' );

		// Display Luxury HTML
		include EXPRESSIVE_CORE_PATH . 'templates/page-certificate-luxury.php';
		exit;
	}

	/**
	 * Handle Global Certificate (75% platform progress)
	 */
	public function handle_global_certificate_view() {
		if ( ! is_user_logged_in() ) {
			wp_die( 'Por favor, realize o login para ver seu certificado.' );
		}

		$user_id = get_current_user_id();
		$user_data = get_userdata( $user_id );

		// Calculate Global Presence
		$completed_lesson_ids = get_user_meta( $user_id, '_lms_completed_lessons', true ) ?: [];
		$total_watched = count($completed_lesson_ids);
		$total_platform = wp_count_posts( 'lms_lesson' )->publish;
		$pct = $total_platform > 0 ? round( ($total_watched / $total_platform) * 100 ) : 0;

		if ( $pct < 75 ) {
			wp_die( 'Sua jornada ainda não atingiu 75% da plataforma para liberar o Certificado de Elite.' );
		}

		$course_title = "FORMAÇÃO ELITE SPECIALIST";
		$date = date( 'd/m/Y' );

		// Display Luxury HTML
		include EXPRESSIVE_CORE_PATH . 'templates/page-certificate-luxury.php';
		exit;
	}

	private function get_total_lessons_in_course( $course_id ) {
		$args = array(
			'post_type'  => 'lms_lesson',
			'meta_key'   => '_lms_course_id',
			'meta_value' => $course_id,
			'posts_per_page' => -1,
			'fields'     => 'ids'
		);
		$query = new WP_Query( $args );
		return count( $query->posts );
	}

	private function get_completed_lessons_count( $user_id, $course_id ) {
		$completed_lessons = get_user_meta( $user_id, '_lms_completed_lessons', true ) ?: [];
		if ( empty( $completed_lessons ) ) {
			return 0;
		}

		global $wpdb;
		$placeholders = implode( ',', array_fill( 0, count( $completed_lessons ), '%d' ) );
		
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			 WHERE meta_key = '_lms_course_id' AND meta_value = %d 
			 AND post_id IN ($placeholders)",
			array_merge( [ $course_id ], $completed_lessons )
		) );
	}

}
