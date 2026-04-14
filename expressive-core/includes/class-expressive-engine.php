<?php

class Expressive_Engine {

	public function register_ajax_hooks() {
		// Legacy Chat hooks (Optional: Keep for fallback or delete if unused)
		add_action( 'wp_ajax_lms_send_chat_message', array( $this, 'ajax_send_chat_message' ) );
		add_action( 'wp_ajax_lms_fetch_chat_messages', array( $this, 'ajax_fetch_chat_messages' ) );
		add_action( 'wp_ajax_lms_upload_avatar', array( $this, 'ajax_upload_avatar' ) );
		
		// Force Open Comments for LMS
		add_filter( 'comments_open', array( $this, 'force_open_lms_comments' ), 10, 2 );
		
		// ELITE CHAT (Standard WP Comments)
		add_action( 'wp_ajax_lms_elite_comment_submit', array( $this, 'ajax_elite_comment_submit' ) );
		add_action( 'wp_ajax_lms_elite_comment_fetch', array( $this, 'ajax_elite_comment_fetch' ) );
		add_action( 'wp_ajax_lms_elite_comment_delete', array( $this, 'ajax_elite_comment_delete' ) );
		add_action( 'wp_ajax_lms_remove_avatar', array( $this, 'ajax_remove_avatar' ) );

		// Progress hooks
		add_action( 'wp_ajax_lms_mark_lesson_complete', array( $this, 'ajax_mark_lesson_complete' ) );

		// Elite API Sync & Manual Control
		add_action( 'wp_ajax_lms_sync_all_api_status', array( $this, 'ajax_sync_all_api_status' ) );
		add_action( 'wp_ajax_lms_set_manual_status', array( $this, 'ajax_set_manual_status' ) );
		
		// Benefits Management
		add_action( 'wp_ajax_lms_toggle_discount_eligibility', array( $this, 'ajax_toggle_discount_eligibility' ) );
		add_action( 'wp_ajax_lms_bulk_discount_control', array( $this, 'ajax_bulk_discount_control' ) );

		// WooCommerce Checkout Discounts
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'apply_lms_discounts' ), 20, 1 );
	}

	/**
	 * Force comments open on all LMS post types
	 */
	public function force_open_lms_comments( $open, $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( in_array( $post_type, array( 'lms_lesson', 'lms_course', 'lms_live' ), true ) ) {
			return true;
		}
		return $open;
	}

	/**
	 * Send a chat message.
	 */
	public function ajax_send_chat_message() {
		check_ajax_referer( 'lms_engine_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Usuário não logado.' );
		}

		$lesson_id = intval( $_POST['lesson_id'] );
		$message   = sanitize_textarea_field( $_POST['message'] );
		$user_id   = get_current_user_id();

		if ( empty( $message ) ) {
			wp_send_json_error( 'Mensagem vazia.' );
		}

		global $wpdb;
		$table_chat = $wpdb->prefix . 'lms_chat_messages';
		
		$result = $wpdb->insert(
			$table_chat,
			array(
				'lesson_id' => $lesson_id,
				'user_id'   => $user_id,
				'message'   => $message,
			),
			array( '%d', '%d', '%s' )
		);

		if ( $result ) {
			wp_send_json_success( 'Mensagem enviada.' );
		} else {
			wp_send_json_error( 'Erro ao salvar no banco.' );
		}
	}

	/**
	 * Fetch chat messages (legacy polling).
	 */
	public function ajax_fetch_chat_messages() {
		check_ajax_referer( 'lms_engine_nonce', 'nonce' );

		$lesson_id = intval( $_GET['lesson_id'] ?? $_POST['lesson_id'] ?? 0 );
		$last_id   = intval( $_GET['last_id'] ?? $_POST['last_id'] ?? 0 );

		global $wpdb;
		$table_chat = $wpdb->prefix . 'lms_chat_messages';
		$messages = $wpdb->get_results( $wpdb->prepare(
			"SELECT m.*, u.display_name FROM $table_chat m LEFT JOIN {$wpdb->users} u ON m.user_id = u.ID WHERE m.lesson_id = %d AND m.id > %d ORDER BY m.id ASC LIMIT 50",
			$lesson_id, $last_id
		) );

		wp_send_json_success( array( 'messages' => $messages ) );
	}

	/**
	 * Elite AJAX Comment Submission
	 */
	public function ajax_elite_comment_submit() {
		check_ajax_referer( 'lms_engine_nonce', 'nonce' );
		
		if ( ! is_user_logged_in() ) wp_send_json_error( 'Login necessário.' );

		$post_id = intval( $_POST['post_id'] );
		$comment_content = sanitize_textarea_field( $_POST['comment'] );
		$user = wp_get_current_user();

		if ( empty( $comment_content ) ) wp_send_json_error( 'A mensagem não pode estar vazia.' );

		$commentdata = array(
			'comment_post_ID'      => $post_id,
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_content'      => $comment_content,
			'user_id'              => $user->ID,
			'comment_approved'     => 1,
		);

		$comment_id = wp_new_comment( $commentdata );

		if ( $comment_id ) {
			$comment = get_comment( $comment_id );
			ob_start();
			?>
			<div class="flex gap-5 p-6 bg-gold-500/5 rounded-2xl border border-gold-500/20 animate-pulse-gold" data-comment-id="<?php echo $comment_id; ?>">
				<div class="w-10 h-10 rounded-full overflow-hidden bg-zinc-800 border border-white/10 flex-shrink-0">
					<?php echo get_avatar($comment, 40); ?>
				</div>
				<div class="flex-1">
					<div class="flex items-center justify-between mb-2">
						<h5 class="text-xs font-bold text-white uppercase tracking-wider"><?php echo esc_html($comment->comment_author); ?></h5>
						<div class="flex items-center gap-3">
							<span class="text-[9px] text-zinc-500 uppercase tracking-widest">Agora</span>
							<?php if ( current_user_can( 'manage_options' ) ) : ?>
								<button class="delete-elite-comment text-red-500/50 hover:text-red-500 transition-colors" data-comment-id="<?php echo $comment_id; ?>" title="Apagar Insight">
									<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
								</button>
							<?php endif; ?>
						</div>
					</div>
					<div class="text-zinc-400 text-sm leading-relaxed">
						<?php echo wpautop(esc_html($comment->comment_content)); ?>
					</div>
				</div>
			</div>
			<?php
			$html = ob_get_clean();
			wp_send_json_success( array( 'html' => $html, 'comment_id' => $comment_id ) );
		}

		wp_send_json_error( 'Falha ao processar comentário.' );
	}

	/**
	 * Elite AJAX Comment Fetch (Polling)
	 */
	public function ajax_elite_comment_fetch() {
		check_ajax_referer( 'lms_engine_nonce', 'nonce' );
		
		$post_id = intval( $_GET['post_id'] );
		$last_id = intval( $_GET['last_id'] );

		$comments = get_comments( array(
			'post_id' => $post_id,
			'status'  => 'approve',
			'ID__not_in' => array(), // not useful here
			'type'    => 'comment',
			'parent'  => 0,
		) );

		// Filter for newer ones manually for precision
		$new_comments = array();
		foreach ( $comments as $c ) {
			if ( intval($c->comment_ID) > $last_id ) {
				ob_start();
				?>
				<div class="flex gap-5 p-6 bg-white/5 rounded-2xl border border-white/5 animate-fade-in" data-comment-id="<?php echo $c->comment_ID; ?>">
					<div class="w-10 h-10 rounded-full overflow-hidden bg-zinc-800 border border-white/10 flex-shrink-0">
						<?php echo get_avatar($c, 40); ?>
					</div>
					<div class="flex-1">
						<div class="flex items-center justify-between mb-2">
							<h5 class="text-xs font-bold text-white uppercase tracking-wider"><?php echo esc_html($c->comment_author); ?></h5>
							<div class="flex items-center gap-3">
								<span class="text-[9px] text-zinc-500 uppercase tracking-widest"><?php echo get_comment_date('H:i', $c); ?></span>
								<?php if ( current_user_can( 'manage_options' ) ) : ?>
									<button class="delete-elite-comment text-red-500/50 hover:text-red-500 transition-colors" data-comment-id="<?php echo $c->comment_ID; ?>" title="Apagar Insight">
										<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
									</button>
								<?php endif; ?>
							</div>
						</div>
						<div class="text-zinc-400 text-sm leading-relaxed">
							<?php echo wpautop(esc_html($c->comment_content)); ?>
						</div>
					</div>
				</div>
				<?php
				$new_comments[] = array(
					'id'   => $c->comment_ID,
					'html' => ob_get_clean()
				);
			}
		}

		wp_send_json_success( array( 'comments' => array_reverse($new_comments) ) );
	}

	/**
	 * Delete a comment (Admin only).
	 */
	public function ajax_elite_comment_delete() {
		check_ajax_referer( 'lms_engine_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Acesso negado: Somente administradores podem apagar insights.' );
		}

		$comment_id = intval( $_POST['comment_id'] );
		$deleted = wp_delete_comment( $comment_id, true ); // Forced permanent delete as requested

		if ( $deleted ) {
			wp_send_json_success( 'Insight apagado com sucesso.' );
		} else {
			wp_send_json_error( 'Falha ao apagar o insight.' );
		}
	}

	/**
	 * Mark lesson as complete.
	 */
	public function ajax_mark_lesson_complete() {
		check_ajax_referer( 'lms_engine_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Login necessário.' );
		}

		$lesson_id = intval( $_POST['lesson_id'] );
		$user_id   = get_current_user_id();
		
		$completed_lessons = get_user_meta( $user_id, '_lms_completed_lessons', true );
		if ( ! is_array( $completed_lessons ) ) {
			$completed_lessons = array();
		}

		global $wpdb;
		$table_progress = $wpdb->prefix . 'mlm_user_progress';

		if ( in_array( $lesson_id, $completed_lessons ) ) {
			// Unmark as completed
			$completed_lessons = array_diff( $completed_lessons, array( $lesson_id ) );
			update_user_meta( $user_id, '_lms_completed_lessons', $completed_lessons );
			Expressive_Logger::info( 'ENGINE', "Aula DESMARCADA como concluída", array( 'user_id' => $user_id, 'lesson_id' => $lesson_id ) );
			
			// Update custom progress table to 'active' or 'started'
			$wpdb->update(
				$table_progress,
				array( 'status' => 'active', 'finished_at' => null ),
				array( 'user_id' => $user_id, 'item_id' => $lesson_id, 'item_type' => 'lesson' ),
				array( '%s', '%s' ),
				array( '%d', '%d', '%s' )
			);

			wp_send_json_success( 'Aula desmarcada como concluída.' );
		} else {
			// Mark as completed
			$completed_lessons[] = $lesson_id;
			update_user_meta( $user_id, '_lms_completed_lessons', $completed_lessons );
			
			$wpdb->replace(
				$table_progress,
				array(
					'user_id'     => $user_id,
					'item_id'     => $lesson_id,
					'item_type'   => 'lesson',
					'status'      => 'completed',
					'finished_at' => current_time( 'mysql' ),
				),
				array( '%d', '%d', '%s', '%s', '%s' )
			);

			Expressive_Logger::info( 'ENGINE', "Aula marcada como concluída", array( 'user_id' => $user_id, 'lesson_id' => $lesson_id ) );
			wp_send_json_success( 'Aula concluída com sucesso!' );
		}
	}

	/**
	 * AJAX Tool: Custom Avatar Upload
	 */
	public function ajax_upload_avatar() {
		check_ajax_referer( 'lms_engine_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Acesso negado.' );
		}

		if ( empty( $_FILES['avatar'] ) ) {
			wp_send_json_error( 'Nenhum arquivo enviado.' );
		}

		$user_id = get_current_user_id();
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		
		// Validate file type
		$uploaded_file = $_FILES['avatar'];
		$allowed_types = array( 'image/jpeg', 'image/png', 'image/webp' );
		if ( ! in_array( $uploaded_file['type'], $allowed_types ) ) {
			wp_send_json_error( 'Formato não suportado. Use JPG, PNG ou WebP.' );
		}

		// Max 2MB
		if ( $uploaded_file['size'] > 2 * 1024 * 1024 ) {
			wp_send_json_error( 'Imagem muito grande. Máximo 2MB.' );
		}

		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			$file_path = $movefile['file'];
			$file_url  = $movefile['url'];

			// Store old path for cleanup
			$old_avatar_url = get_user_meta( $user_id, '_lms_custom_avatar', true );
			$upload_dir = wp_upload_dir();
			$old_path = $old_avatar_url ? str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $old_avatar_url ) : '';

			// Generate optimized thumbnail (256x256 crop) and delete the original
			$image = wp_get_image_editor( $file_path );
			if ( ! is_wp_error( $image ) ) {
				$image->resize( 256, 256, true );
				$image->set_quality( 82 );
				$thumb_suffix = 'elite-avatar-' . $user_id;

				// Generate unique optimized filename using base name and user-specific suffix
				$filename = $image->generate_filename( $thumb_suffix, null, 'jpg' );
				$saved = $image->save( $filename, 'image/jpeg' );
				
				if ( ! is_wp_error( $saved ) ) {
					$new_file_url = str_replace( basename( $file_path ), basename( $saved['path'] ), $file_url );
					$new_file_path = $saved['path'];

					// Delete original large uploaded file
					@unlink( $file_path );

					// Delete old avatar only if it's a DIFFERENT file
					if ( $old_path && $old_path !== $new_file_path && file_exists( $old_path ) ) {
						@unlink( $old_path );
					}
					
					$file_url = $new_file_url;
				}
			}

			update_user_meta( $user_id, '_lms_custom_avatar', $file_url );
			Expressive_Logger::info( 'ENGINE', "Avatar atualizado com sucesso", array( 'user_id' => $user_id, 'url' => $file_url ) );
			wp_send_json_success( array( 'url' => $file_url, 'message' => 'Avatar Elite otimizado!' ) );
		} else {
			$error_msg = isset($movefile['error']) ? $movefile['error'] : 'Falha desconhecida no upload.';
			Expressive_Logger::error( 'ENGINE', "Falha no upload de avatar", array( 'user_id' => $user_id, 'error' => $error_msg ) );
			wp_send_json_error( $error_msg );
		}
	}

	public function ajax_remove_avatar() {
		check_ajax_referer( 'lms_engine_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Acesso negado.' );
		}

		$user_id = get_current_user_id();
		$old_avatar_url = get_user_meta( $user_id, '_lms_custom_avatar', true );

		if ( $old_avatar_url ) {
			$upload_dir = wp_upload_dir();
			$old_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $old_avatar_url );
			if ( file_exists( $old_path ) ) {
				@unlink( $old_path );
			}
		}

		delete_user_meta( $user_id, '_lms_custom_avatar' );
		
		$default_avatar = Expressive_Core::get_elite_avatar( $user_id, 128, 'w-full h-full object-cover' );
		wp_send_json_success( array( 'html' => $default_avatar, 'message' => 'Avatar removido!' ) );
	}

	/**
	 * Apply role-based discounts on WooCommerce checkout.
	 */
	public function apply_lms_discounts( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
		
		$user_id = get_current_user_id();
		$discount_percent = 0;
		$label = '';
		$detected_category = '';

		// 1. Detect category for LOGGED IN users
		if ( $user_id ) {
			if ( Expressive_Referral::is_educator( $user_id ) ) {
				$detected_category = 'educadora';
			} elseif ( Expressive_Referral::is_authority( $user_id ) ) {
				$detected_category = 'autoridade';
			}
		}

		// 2. Detect category for GUESTS/NEW REGISTRANTS (from Checkout POST data)
		if ( ! $detected_category ) {
			$possible_keys = array( 'billing_usuario', 'user_registration_radio_1771803100' );
			$source_data = $_POST;

			// Handle AJAX checkout refresh where fields are in 'post_data' string
			if ( isset( $_POST['post_data'] ) ) {
				parse_str( $_POST['post_data'], $post_data );
				$source_data = array_merge( $source_data, $post_data );
			}

			foreach ( $possible_keys as $key ) {
				if ( isset( $source_data[ $key ] ) && ! empty( $source_data[ $key ] ) ) {
					$detected_category = strtolower( trim( sanitize_text_field( $source_data[ $key ] ) ) );
					break;
				}
			}
		}

		// 3. Set discount based on detected category
		if ( $detected_category === 'educadora' ) {
			$discount_percent = 0.40;
			$label = 'Desconto Elite: Educadora (40%)';
		} elseif ( $detected_category === 'autoridade' ) {
			$discount_percent = 0.30;
			$label = 'Desconto Elite: Autoridade (30%)';
		}

		// 3.1 Verify Eligibility (Only for logged-in users)
		if ( $user_id && $discount_percent > 0 ) {
			$is_eligible = get_user_meta( $user_id, '_lms_discount_eligible', true ) === 'yes';
			if ( ! $is_eligible ) {
				$discount_percent = 0;
				Expressive_Logger::debug( 'DISCOUNT', "Desconto ignorado: Usuário categorizado mas SEM elegibilidade ativa no gestor.", array( 'user_id' => $user_id, 'category' => $detected_category ) );
			}
		}

		// 4. Apply to cart
		if ( $discount_percent > 0 ) {
			$excluded_ids_raw = get_option( 'lms_excluded_discount_products', '' );
			$excluded_ids = array_filter( array_map( 'intval', explode( ',', $excluded_ids_raw ) ) );
			
			$discountable_subtotal = 0;
			$total_subtotal = $cart->get_subtotal();

			// Calculate discountable base (excluding forbidden IDs)
			foreach ( $cart->get_cart() as $cart_item ) {
				$product_id = $cart_item['product_id'];
				if ( ! in_array( $product_id, $excluded_ids ) ) {
					$discountable_subtotal += $cart_item['line_total'];
				}
			}

			if ( $discountable_subtotal > 0 ) {
				$discount_amount = ( $discountable_subtotal * $discount_percent ) * -1;
				$cart->add_fee( $label, $discount_amount );
				
				Expressive_Logger::info( 'DISCOUNT', "Desconto aplicado", array( 
					'user_id'     => $user_id, 
					'category'    => $detected_category, 
					'percent'     => ($discount_percent * 100) . '%', 
					'full_subtotal' => $total_subtotal,
					'base_subtotal' => $discountable_subtotal,
					'discount'    => $discount_amount,
					'excluded_ids' => $excluded_ids
				) );
			} else {
				Expressive_Logger::debug( 'DISCOUNT', "Desconto não aplicado: Todos os produtos do carrinho estão excluídos.", array( 'excluded_ids' => $excluded_ids ) );
			}
		}
	}

	/**
	 * Sync all users from external API.
	 */
	public function ajax_sync_all_api_status() {
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Não autorizado.' );
		
		$success = Expressive_External_API::sync_all_users_status();
		
		if ( $success ) {
			wp_send_json_success( 'Sincronização concluída!' );
		} else {
			wp_send_json_error( 'Erro ao sincronizar. Verifique as configurações da API.' );
		}
	}

	/**
	 * Set manual access status for a user.
	 * Directly sets: none, blocked, or unblocked
	 */
	public function ajax_set_manual_status() {
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Não autorizado.' );
		
		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$new_status = isset( $_POST['new_status'] ) ? sanitize_text_field( $_POST['new_status'] ) : '';

		if ( ! $user_id || ! in_array( $new_status, array( 'none', 'blocked', 'unblocked' ) ) ) {
			wp_send_json_error( 'Parâmetros inválidos.' );
		}

		// Synchronize using the Central Access Manager
		Expressive_Access::update_access_status( $user_id, $new_status );

		Expressive_Logger::info( 'ENGINE', "Status manual alterado por admin", array( 'target_user' => $user_id, 'new_status' => $new_status, 'admin_id' => get_current_user_id() ) );
		wp_send_json_success( 'Status atualizado com sucesso.' );
	}

	/**
	 * Secure File Download Proxy (Link Masking)
	 */
	public function handle_safe_download() {
		if ( ! isset( $_GET['lms_download'] ) ) return;

		$file_id = intval( $_GET['lms_download'] );
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			wp_die( 'Acesso negado. Por favor, faça login para baixar materiais.' );
		}

		// Check Access
		$access = new Expressive_Access();
		if ( ! $access->has_active_subscription( $user_id ) ) {
			Expressive_Logger::warning( 'SECURE_DOWN', "Tentativa de download sem assinatura ativa", array( 'user_id' => $user_id, 'file_id' => $file_id ) );
			wp_die( 'Seu acesso aos materiais de elite está bloqueado até a confirmação do pagamento.' );
		}

		$file_path = get_attached_file( $file_id );
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			wp_die( 'Material não encontrado ou removido do servidor.' );
		}

		$mime_type = get_post_mime_type( $file_id );
		$file_name = get_the_title( $file_id ) ?: basename( $file_path );
		
		// Ensure file name has extension if missing from title
		$ext = pathinfo( $file_path, PATHINFO_EXTENSION );
		if ( strpos( $file_name, '.' . $ext ) === false ) {
			$file_name .= '.' . $ext;
		}

		Expressive_Logger::info( 'SECURE_DOWN', "Download servido com sucesso", array( 'user_id' => $user_id, 'file_id' => $file_id, 'filename' => $file_name ) );

		// Clear buffer to prevent corrupted files
		if ( ob_get_level() ) ob_end_clean();

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: ' . $mime_type );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		
		readfile( $file_path );
		exit;
	}

	/**
	 * Toggle individual discount eligibility.
	 */
	public function ajax_toggle_discount_eligibility() {
		check_ajax_referer( 'benefits_mgmt_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Não autorizado.' );

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$status  = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'no';

		if ( ! $user_id ) wp_send_json_error( 'ID de usuário inválido.' );

		update_user_meta( $user_id, '_lms_discount_eligible', $status );
		
		Expressive_Logger::info( 'BENEFITS', "Elegibilidade de desconto alterada", array( 'target_user' => $user_id, 'new_status' => $status, 'admin_id' => get_current_user_id() ) );
		wp_send_json_success( 'Status atualizado!' );
	}

	/**
	 * Bulk control for discount eligibility.
	 */
	public function ajax_bulk_discount_control() {
		check_ajax_referer( 'benefits_mgmt_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Não autorizado.' );

		$type = isset( $_POST['bulk_type'] ) ? sanitize_text_field( $_POST['bulk_type'] ) : '';
		
		$args = array( 'fields' => 'ID' );
		$status = 'yes';
		$message = '';

		switch ( $type ) {
			case 'enable_educators':
				$args['role'] = 'educadora';
				$status = 'yes';
				$message = 'Descontos liberados para todas as Educadoras.';
				break;
			case 'enable_authorities':
				$args['role'] = 'autoridade';
				$status = 'yes';
				$message = 'Descontos liberados para todas as Autoridades.';
				break;
			case 'enable_all':
				$args['role__in'] = array( 'educadora', 'autoridade' );
				$status = 'yes';
				$message = 'Benefícios liberados para toda a rede Elite.';
				break;
			case 'disable_all':
				$args['role__in'] = array( 'educadora', 'autoridade' );
				$status = 'no';
				$message = 'Todos os benefícios foram revogados com sucesso.';
				break;
			default:
				wp_send_json_error( 'Ação inválida.' );
		}

		$users = get_users( $args );
		foreach ( $users as $u_id ) {
			update_user_meta( $u_id, '_lms_discount_eligible', $status );
		}

		Expressive_Logger::info( 'BENEFITS', "Ação em massa executada", array( 'type' => $type, 'count' => count($users), 'admin_id' => get_current_user_id() ) );
		wp_send_json_success( $message . ' (Afetou ' . count($users) . ' usuários)' );
	}

}
