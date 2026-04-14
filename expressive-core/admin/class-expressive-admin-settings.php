<?php

class Expressive_Admin_Settings {

	public function register_admin_menu() {
		// 1. Top Level Menu (Home)
		add_menu_page(
			'Elite LMS',
			'Elite LMS',
			'manage_options',
			'expressive-lms',
			array( $this, 'render_admin_hub' ),
			'dashicons-performance',
			25
		);

		// 2. Submenus (All custom-routed)
		add_submenu_page(
			'expressive-lms',
			'Painel de Controle',
			'Painel Geral',
			'manage_options',
			'expressive-lms',
			array( $this, 'render_admin_hub' )
		);

		add_submenu_page(
			'expressive-lms',
			'Gerenciar Cursos',
			'Cursos & Aulas',
			'manage_options',
			'elite-content',
			array( $this, 'render_admin_hub' )
		);

		add_submenu_page(
			'expressive-lms',
			'Calendário de Mentorias',
			'Mentorias (Lives)',
			'manage_options',
			'elite-calendar',
			array( $this, 'render_admin_hub' )
		);

		add_submenu_page(
			'expressive-lms',
			'Configurações do Ciclo',
			'Marco Zero / Gamif.',
			'manage_options',
			'elite-settings',
			array( $this, 'render_admin_hub' )
		);
		
		add_submenu_page(
			'expressive-lms',
			'Gerenciador de API',
			'Elite API Manager',
			'manage_options',
			'elite-api',
			array( $this, 'render_admin_hub' )
		);

		// 3. Conditional Commission Dashboard
		if ( get_option( 'lms_show_commissions', 'yes' ) === 'yes' ) {
			add_submenu_page(
				'expressive-lms',
				'Comissões Elite',
				'💰 Comissões Elite',
				'manage_options',
				'elite-commissions',
				array( $this, 'render_admin_hub' )
			);
		}

		add_submenu_page(
			'expressive-lms',
			'Gestão de Benefícios',
			'💎 Benefícios Elite',
			'manage_options',
			'elite-benefits',
			array( $this, 'render_admin_hub' )
		);

		add_submenu_page(
			'expressive-lms',
			'Logs de Debug',
			'📋 Logs de Debug',
			'manage_options',
			'elite-logs',
			array( $this, 'render_admin_hub' )
		);
	}

	public function render_admin_hub() {
		// Enqueue Tailwind for this dashboard only
		add_action( 'admin_footer', function() {
			echo '<script src="https://cdn.tailwindcss.com"></script>';
			echo '<script>
				tailwind.config = {
					theme: {
						extend: {
							colors: {
								gold: { 400: "#F2D480", 500: "#D4AF37", 600: "#AA8C2C" }
							}
						}
					}
				}
			</script>';
		} );

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'expressive-lms';
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
		$template = '';

		// Route to Editor/Curriculum if action is set
		if ( $action === 'edit' || $action === 'new' ) {
			$template = EXPRESSIVE_CORE_PATH . 'admin/templates/edit-content.php';
		} elseif ( $action === 'curriculum' ) {
			$template = EXPRESSIVE_CORE_PATH . 'admin/templates/manage-curriculum.php';
		} else {
			switch ( $page ) {
				case 'elite-content':
					$template = EXPRESSIVE_CORE_PATH . 'admin/templates/manage-content.php';
					break;
				case 'elite-calendar':
					$template = EXPRESSIVE_CORE_PATH . 'admin/templates/manage-content.php';
					break;
				case 'elite-settings':
					$template = EXPRESSIVE_CORE_PATH . 'admin/templates/manage-settings.php';
					break;
				case 'elite-api':
					$template = EXPRESSIVE_CORE_PATH . 'admin/templates/api-management.php';
					break;
				case 'elite-commissions':
					$template = EXPRESSIVE_CORE_PATH . 'admin/templates/commission-dashboard.php';
					break;
				case 'elite-benefits':
					$template = EXPRESSIVE_CORE_PATH . 'admin/templates/benefits-management.php';
					break;
				case 'elite-logs':
					$template = EXPRESSIVE_CORE_PATH . 'admin/templates/view-logs.php';
					break;
				default:
					$template = EXPRESSIVE_CORE_PATH . 'admin/templates/dashboard-admin.php';
					break;
			}
		}

		if ( file_exists( $template ) ) {
			include $template;
		} else {
			echo '<div class="wrap"><h1>Ocorreu um erro</h1><p>Template não encontrado: ' . esc_html( $template ) . '</p></div>';
		}
	}

	/**
	 * Handle POST from Elite Editor
	 */
	public function handle_elite_editor_save() {
		if ( ! isset( $_POST['lms_nonce'] ) || ! wp_verify_nonce( $_POST['lms_nonce'], 'lms_save_elite_content_nonce' ) ) {
			wp_die( 'Erro de segurança.' );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Acesso negado.' );
		}

		$post_id   = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : 'lms_course';
		$redirect  = isset( $_POST['redirect_page'] ) ? sanitize_text_field( $_POST['redirect_page'] ) : 'elite-content';

		$post_data = array(
			'post_title'   => sanitize_text_field( $_POST['post_title'] ),
			'post_content' => wp_kses_post( $_POST['post_content'] ),
			'post_status'  => sanitize_text_field( $_POST['post_status'] ),
			'post_type'    => $post_type,
		);

		if ( $post_id > 0 ) {
			$post_data['ID'] = $post_id;
			wp_update_post( $post_data );
		} else {
			$post_id = wp_insert_post( $post_data );
		}

		// Meta Data
		if ( isset( $_POST['lms_youtube_id'] ) ) {
			$video_id = $this->extract_youtube_id( $_POST['lms_youtube_id'] );
			update_post_meta( $post_id, '_lms_youtube_id', $video_id );
		}
		
		if ( isset( $_POST['lms_professor_name'] ) ) {
			update_post_meta( $post_id, '_lms_professor_name', sanitize_text_field( $_POST['lms_professor_name'] ) );
		}

		if ( $post_type === 'lms_lesson' ) {
			if ( isset( $_POST['lms_module_id'] ) && !empty( $_POST['lms_module_id'] ) ) {
				$module_id = intval( $_POST['lms_module_id'] );
				update_post_meta( $post_id, '_lms_module_id', $module_id );
				
				// Auto-fetch course ID from module to keep direct relationship
				$parent_course_id = get_post_meta( $module_id, '_lms_course_id', true );
				if ( $parent_course_id ) {
					update_post_meta( $post_id, '_lms_course_id', $parent_course_id );
				} else {
					update_post_meta( $post_id, '_lms_course_id', '' );
				}
			}

			if ( isset( $_POST['lms_lesson_date'] ) ) {
				update_post_meta( $post_id, '_lms_lesson_date', sanitize_text_field( $_POST['lms_lesson_date'] ) );
			}

		} elseif ( $post_type === 'lms_live' ) {
			if ( isset( $_POST['lms_live_date'] ) ) {
				update_post_meta( $post_id, '_lms_live_date', sanitize_text_field( $_POST['lms_live_date'] ) );
			}
			if ( isset( $_POST['lms_live_time'] ) ) {
				update_post_meta( $post_id, '_lms_live_time', sanitize_text_field( $_POST['lms_live_time'] ) );
			}
		} else {
			if ( isset( $_POST['lms_course_id'] ) ) {
				update_post_meta( $post_id, '_lms_course_id', sanitize_text_field( $_POST['lms_course_id'] ) );
			}
		}

		if ( isset( $_POST['lms_duration'] ) ) {
			update_post_meta( $post_id, '_lms_duration', intval( $_POST['lms_duration'] ) );
		}
		
		if ( isset( $_POST['lms_visibility_role'] ) ) {
			update_post_meta( $post_id, '_lms_visibility_role', sanitize_text_field( $_POST['lms_visibility_role'] ) );
		}

		// Supporting Materials (Files)
		if ( isset( $_POST['lms_files'] ) && is_array( $_POST['lms_files'] ) ) {
			$files_data = array();
			foreach ( $_POST['lms_files'] as $file ) {
				$files_data[] = array(
					'name' => sanitize_text_field( $file['name'] ),
					'id'   => intval( $file['id'] )
				);
			}
			update_post_meta( $post_id, '_lms_files_data', $files_data );
		} else {
			// If we are editing a lesson and no files came through, clear the meta
			if ( $post_type === 'lms_lesson' ) {
				delete_post_meta( $post_id, '_lms_files_data' );
			}
		}

		if ( isset( $_POST['_thumbnail_id'] ) ) {
			set_post_thumbnail( $post_id, intval( $_POST['_thumbnail_id'] ) );
		}

		// Recalculate Module Duration if needed
		if ( $post_type === 'lms_lesson' && isset( $module_id ) ) {
			$this->recalculate_module_duration( $module_id );
		} elseif ( $post_type === 'lms_module' ) {
			$this->recalculate_module_duration( $post_id );
		}

		wp_redirect( admin_url( 'admin.php?page=' . $redirect . '&message=saved' ) );
		exit;
	}

	/**
	 * Recalculate total duration of a module based on its lessons.
	 */
	public function recalculate_module_duration( $module_id ) {
		if ( ! $module_id ) return;

		global $wpdb;
		$lessons = get_posts( array(
			'post_type'  => 'lms_lesson',
			'meta_key'   => '_lms_module_id',
			'meta_value' => $module_id,
			'posts_per_page' => -1,
		) );

		$total_duration = 0;
		foreach ( $lessons as $lesson ) {
			$total_duration += intval( get_post_meta( $lesson->ID, '_lms_duration', true ) );
		}

		update_post_meta( $module_id, '_lms_duration', $total_duration );
	}

	public function render_cycle_settings() {
		if ( isset( $_POST['lms_reset_cycle'] ) && check_admin_referer( 'lms_reset_cycle_nonce' ) ) {
			$this->reset_annual_cycle();
			echo '<div class="updated"><p>Ciclo resetado com sucesso! Ranking Marco Zero inicializado.</p></div>';
		}

		if ( isset( $_POST['lms_save_settings'] ) && check_admin_referer( 'lms_save_cycle_nonce' ) ) {
			if ( isset( $_POST['cycle_date'] ) ) {
				update_option( 'lms_cycle_end_date', sanitize_text_field( $_POST['cycle_date'] ) );
			}
			$show_commissions = isset( $_POST['lms_show_total_commissions'] ) ? 'yes' : 'no';
			update_option( 'lms_show_total_commissions', $show_commissions );
			echo '<div class="updated"><p>Configurações salvas com sucesso!</p></div>';
		}

		$current_end_date = get_option( 'lms_cycle_end_date', '2026-12-31' );
		$show_commissions = get_option( 'lms_show_total_commissions', 'yes' );
		?>
		<div class="wrap">
			<h1>Configurações do Ciclo Anual (Marco Zero)</h1>
			<p>Defina a data de encerramento do ciclo para a premiação dos Top 3 e reinício das contagens.</p>
			
			<form method="post" action="">
				<?php wp_nonce_field( 'lms_save_cycle_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="cycle_date">Data de Encerramento:</label></th>
						<td><input name="cycle_date" type="date" id="cycle_date" value="<?php echo esc_attr( $current_end_date ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row">Visibilidade do Dashboard:</th>
						<td>
							<label for="lms_show_total_commissions">
								<input name="lms_show_total_commissions" type="checkbox" id="lms_show_total_commissions" value="yes" <?php checked( $show_commissions, 'yes' ); ?>>
								Exibir "Comissões Totais" para Membros
							</label>
							<p class="description">Se desabilitado, o card de comissões totais será ocultado no dashboard do aluno.</p>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Salvar Configurações', 'primary', 'lms_save_settings' ); ?>
			</form>

			<hr>

			<h2>Ações Críticas</h2>
			<p style="color: #d63638;"><strong>Atenção:</strong> Resetar o ciclo irá zerar as contagens de indicações para o ranking atual. Certifique-se de que a premiação foi realizada.</p>
			<form method="post" action="" onsubmit="return confirm('Tem certeza que deseja resetar o ciclo? Esta ação não pode ser desfeita.');">
				<?php wp_nonce_field( 'lms_reset_cycle_nonce' ); ?>
				<input type="submit" name="lms_reset_cycle" class="button button-secondary" value="Reiniciar Ciclo (Marco Zero)">
			</form>
		</div>
		<?php
	}

	private function reset_annual_cycle() {
		global $wpdb;
		$table_referrals = $wpdb->prefix . 'lms_referrals';
		
		// 1. Clear the referrals table (or archive them)
		$wpdb->query( "TRUNCATE TABLE $table_referrals" );
		
		// 2. Reset user ranks metadata
		$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key IN ('_lms_rank_level', '_lms_rank_name', '_lms_completed_lessons')" );
		
		// 3. Optional: Log the reset event
	}

	/**
	 * Helper: Extract YouTube ID from URL or Raw ID
	 */
	private function extract_youtube_id( $input ) {
		$input = trim( $input );
		
		// Regex for common YouTube URL patterns
		$pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
		
		if ( preg_match( $pattern, $input, $matches ) ) {
			return $matches[1];
		}
		
		// Fallback for raw ID if it matches 11 char standard
		if ( preg_match( '/^[a-zA-Z0-9_-]{11}$/', $input ) ) {
			return $input;
		}

		return sanitize_text_field( $input );
	}

	/**
	 * Handle Elite Content Deletion
	 */
	public function handle_elite_content_delete() {
		check_admin_referer( 'lms_delete_content_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Acesso negado.' );
		}

		$post_id  = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
		$redirect = isset( $_GET['redirect'] ) ? sanitize_text_field( $_GET['redirect'] ) : 'elite-content';

		if ( $post_id > 0 ) {
			// Delete permanently
			wp_delete_post( $post_id, true );
		}

		wp_redirect( admin_url( 'admin.php?page=' . $redirect . '&message=deleted' ) );
		exit;
	}

	/**
	 * AJAX: Update Module and Lesson Order
	 */
	public function ajax_update_module_and_lesson_order() {
		check_ajax_referer( 'lms_save_elite_content_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permissão negada.' );
		}

		$module_order = isset( $_POST['module_order'] ) ? $_POST['module_order'] : array();
		$lesson_order = isset( $_POST['lesson_order'] ) ? $_POST['lesson_order'] : array();

		$processed = 0;

		// Update Modules
		if ( ! empty( $module_order ) ) {
			foreach ( $module_order as $index => $module_id ) {
				$module_id = intval( $module_id );
				if ( $module_id > 0 ) {
					wp_update_post( array(
						'ID'         => $module_id,
						'menu_order' => $index,
					) );
					clean_post_cache( $module_id );
					$processed++;
				}
			}
		}

		// Update Lessons
		if ( ! empty( $lesson_order ) ) {
			foreach ( $lesson_order as $index => $lesson_id ) {
				$lesson_id = intval( $lesson_id );
				if ( $lesson_id > 0 ) {
					wp_update_post( array(
						'ID'         => $lesson_id,
						'menu_order' => $index,
					) );
					clean_post_cache( $lesson_id );
					$processed++;
				}
			}
		}

		if ( $processed > 0 ) {
			wp_send_json_success( 'Ordem da matriz global atualizada com sucesso.' );
		} else {
			wp_send_json_error( 'Nenhum dado válido de ordenação recebido.' );
		}
	}

}
