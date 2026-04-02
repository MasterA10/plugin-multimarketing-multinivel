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

		// Progress hooks
		add_action( 'wp_ajax_lms_mark_lesson_complete', array( $this, 'ajax_mark_lesson_complete' ) );
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
									<span class="dashicons dashicons-trash text-sm"></span>
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
										<span class="dashicons dashicons-trash text-sm"></span>
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
		
		$uploaded_file = $_FILES['avatar'];
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			$file_path = $movefile['file'];
			$file_url  = $movefile['url'];

			// Generate Elite Thumbnail (128x128) for performance
			$image = wp_get_image_editor( $file_path );
			if ( ! is_wp_error( $image ) ) {
				$image->resize( 128, 128, true );
				$thumb_suffix = 'elite-thumb';
				$filename = $image->generate_filename( $thumb_suffix, null, null );
				$saved = $image->save( $filename );
				
				if ( ! is_wp_error( $saved ) ) {
					$file_url = str_replace( basename( $file_path ), basename( $saved['path'] ), $file_url );
				}
			}

			update_user_meta( $user_id, '_lms_custom_avatar', $file_url );
			wp_send_json_success( array( 'url' => $file_url, 'message' => 'Avatar Elite otimizado!' ) );
		} else {
			$error_msg = isset($movefile['error']) ? $movefile['error'] : 'Falha desconhecida no upload.';
			wp_send_json_error( $error_msg );
		}
	}

}
