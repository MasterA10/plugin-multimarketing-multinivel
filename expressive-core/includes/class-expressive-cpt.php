<?php

class Expressive_CPT {

	public function register_custom_post_types() {
		// Courses
		register_post_type( 'lms_course', array(
			'labels' => array(
				'name'          => 'Cursos',
				'singular_name' => 'Curso',
			),
			'public'      => true,
			'has_archive' => true,
			'supports'    => array( 'title', 'thumbnail', 'excerpt', 'editor', 'comments' ),
			'menu_icon'   => 'dashicons-welcome-learn-more',
			'show_in_rest' => true,
			'show_in_menu' => false, // Hidden from sidebar, managed via Elite LMS
		) );

		// Lessons
		register_post_type( 'lms_lesson', array(
			'labels' => array(
				'name'          => 'Aulas',
				'singular_name' => 'Aula',
			),
			'public'      => true,
			'has_archive' => false,
			'supports'    => array( 'title', 'thumbnail', 'editor', 'comments' ),
			'menu_icon'   => 'dashicons-video-alt3',
			'show_in_rest' => true,
			'show_in_menu' => false, // Hidden from sidebar
		) );

		// Modules
		register_post_type( 'lms_module', array(
			'labels' => array(
				'name'          => 'Módulos',
				'singular_name' => 'Módulo',
			),
			'public'      => true,
			'has_archive' => false,
			'supports'    => array( 'title', 'thumbnail', 'page-attributes' ),
			'menu_icon'   => 'dashicons-category',
			'show_in_rest' => true,
			'show_in_menu' => false, // Hidden from sidebar
		) );

		// Lives
		register_post_type( 'lms_live', array(
			'labels' => array(
				'name'          => 'Calendário de Lives',
				'singular_name' => 'Live',
			),
			'public'      => true,
			'has_archive' => true,
			'supports'    => array( 'title', 'thumbnail', 'editor', 'comments' ),
			'menu_icon'   => 'dashicons-calendar-alt',
			'show_in_rest' => true,
			'show_in_menu' => false, // Hidden from sidebar
		) );
	}

	public function add_lesson_meta_boxes() {
		// Lesson details
		add_meta_box(
			'lms_lesson_details',
			'Detalhes da Aula',
			array( $this, 'render_lesson_meta_box' ),
			'lms_lesson',
			'normal',
			'high'
		);

		// RBAC Visibility (Universal)
		$screens = array( 'lms_course', 'lms_lesson', 'lms_live' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				'lms_visibility_meta',
				'Nível de Acesso (Visibilidade)',
				array( $this, 'render_visibility_meta_box' ),
				$screen,
				'side',
				'high'
			);
		}
	}

	public function render_visibility_meta_box( $post ) {
		$visibility = get_post_meta( $post->ID, '_lms_visibility_role', true ) ?: 'all';
		wp_nonce_field( 'lms_visibility_meta_box_nonce', 'lms_visibility_meta_box_nonce' );
		?>
		<p>
			<label for="lms_visibility_role">Defina quem pode acessar este conteúdo:</label><br><br>
			<select name="lms_visibility_role" id="lms_visibility_role" class="widefat">
				<option value="all" <?php selected( $visibility, 'all' ); ?>>Aberto (Todos os Membros)</option>
				<option value="educadora" <?php selected( $visibility, 'educadora' ); ?>>Apenas Educadoras</option>
				<option value="autoridade" <?php selected( $visibility, 'autoridade' ); ?>>Apenas Autoridades</option>
			</select>
		</p>
		<p class="description">Visitantes e membros que não se encaixam no requisito não conseguirão ver sequer a existência da página de conteúdo ou miniatura dentro da área de alunos.</p>
		<?php
	}

	public function render_lesson_meta_box( $post ) {
		$youtube_id = get_post_meta( $post->ID, '_lms_youtube_id', true );
		$course_id  = get_post_meta( $post->ID, '_lms_course_id', true );
		$professor  = get_post_meta( $post->ID, '_lms_professor_name', true );
		$date       = get_post_meta( $post->ID, '_lms_lesson_date', true );
		
		wp_nonce_field( 'lms_lesson_meta_box_nonce', 'lms_lesson_meta_box_nonce' );
		?>
		<p>
			<label for="lms_youtube_id">ID do Vídeo no YouTube:</label><br>
			<input type="text" name="lms_youtube_id" id="lms_youtube_id" value="<?php echo esc_attr( $youtube_id ); ?>" class="regular-text">
		</p>
		<p>
			<label for="lms_professor_name">Nome do Professor:</label><br>
			<input type="text" name="lms_professor_name" id="lms_professor_name" value="<?php echo esc_attr( $professor ); ?>" class="regular-text">
		</p>
		<p>
			<label for="lms_lesson_date">Data da Aula (para o Calendário):</label><br>
			<input type="date" name="lms_lesson_date" id="lms_lesson_date" value="<?php echo esc_attr( $date ); ?>" class="regular-text">
		</p>
		<p>
			<label for="lms_course_id">Vínculo com o Curso (Selecione):</label><br>
			<select name="lms_course_id" id="lms_course_id" class="postform">
				<option value="">-- Selecione um Curso --</option>
				<?php
				$courses = get_posts( array( 'post_type' => 'lms_course', 'posts_per_page' => -1 ) );
				foreach ( $courses as $course ) :
					?>
					<option value="<?php echo $course->ID; ?>" <?php selected( $course_id, $course->ID ); ?>>
						<?php echo esc_html( $course->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
		<div class="lms-materials-manager">
			<h3 style="margin-bottom: 5px;">Materiais de Apoio (PDF, Apostilas, E-books)</h3>
			<p class="description">Adicione arquivos que estarão disponíveis para download na página da aula.</p>
			
			<div id="lms-files-list" style="margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
				<?php
				$files = get_post_meta( $post->ID, '_lms_files_data', true );
				if ( ! is_array( $files ) ) $files = array();

				foreach ( $files as $index => $file ) :
					?>
					<div class="lms-file-item" style="display: flex; align-items: center; gap: 10px; background: #f9f9f9; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
						<span class="dashicons dashicons-media-document" style="color: #666;"></span>
						<input type="text" name="lms_files[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $file['name'] ); ?>" placeholder="Título do Material" style="flex: 1;">
						<input type="hidden" name="lms_files[<?php echo $index; ?>][id]" value="<?php echo esc_attr( $file['id'] ); ?>">
						<span class="description" style="font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px;"><?php echo basename( get_attached_file( $file['id'] ) ); ?></span>
						<button type="button" class="button button-link-delete lms-remove-file" style="color: #a00;">Remover</button>
					</div>
				<?php endforeach; ?>
			</div>

			<p style="margin-top: 20px;">
				<button type="button" id="lms-add-file-btn" class="button button-secondary">
					<span class="dashicons dashicons-plus-alt" style="margin-top: 4px; font-size: 16px;"></span> Adicionar Novo Material
				</button>
			</p>

			<input type="hidden" id="lms-files-next-index" value="<?php echo count( $files ); ?>">
		</div>

		<script>
		jQuery(document).ready(function($) {
			var frame;
			$('#lms-add-file-btn').on('click', function(e) {
				e.preventDefault();
				if (frame) { frame.open(); return; }
				frame = wp.media({
					title: 'Selecionar Materiais de Apoio',
					button: { text: 'Adicionar à Aula' },
					multiple: true
				});
				frame.on('select', function() {
					var selections = frame.state().get('selection');
					var nextIndex = parseInt($('#lms-files-next-index').val());
					
					selections.map(function(attachment) {
						attachment = attachment.toJSON();
						var html = `
							<div class="lms-file-item" style="display: flex; align-items: center; gap: 10px; background: #f9f9f9; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
								<span class="dashicons dashicons-media-document" style="color: #666;"></span>
								<input type="text" name="lms_files[${nextIndex}][name]" value="${attachment.title}" placeholder="Título do Material" style="flex: 1;">
								<input type="hidden" name="lms_files[${nextIndex}][id]" value="${attachment.id}">
								<span class="description" style="font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px;">${attachment.filename}</span>
								<button type="button" class="button button-link-delete lms-remove-file" style="color: #a00;">Remover</button>
							</div>
						`;
						$('#lms-files-list').append(html);
						nextIndex++;
					});
					$('#lms-files-next-index').val(nextIndex);
				});
				frame.open();
			});

			$(document).on('click', '.lms-remove-file', function(e) {
				e.preventDefault();
				$(this).closest('.lms-file-item').remove();
			});
		});
		</script>
		<?php
	}

	public function save_lesson_meta_data( $post_id ) {
		if ( ! isset( $_POST['lms_lesson_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['lms_lesson_meta_box_nonce'], 'lms_lesson_meta_box_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['lms_youtube_id'] ) ) {
			update_post_meta( $post_id, '_lms_youtube_id', sanitize_text_field( $_POST['lms_youtube_id'] ) );
		}

		if ( isset( $_POST['lms_professor_name'] ) ) {
			update_post_meta( $post_id, '_lms_professor_name', sanitize_text_field( $_POST['lms_professor_name'] ) );
		}

		if ( isset( $_POST['lms_lesson_date'] ) ) {
			update_post_meta( $post_id, '_lms_lesson_date', sanitize_text_field( $_POST['lms_lesson_date'] ) );
		}

		if ( isset( $_POST['lms_course_id'] ) ) {
			update_post_meta( $post_id, '_lms_course_id', sanitize_text_field( $_POST['lms_course_id'] ) );
		}

		if ( isset( $_POST['lms_files'] ) && is_array( $_POST['lms_files'] ) ) {
			$files_data = array();
			foreach ( $_POST['lms_files'] as $file ) {
				if ( ! empty( $file['id'] ) ) {
					$files_data[] = array(
						'id'   => intval( $file['id'] ),
						'name' => sanitize_text_field( $file['name'] )
					);
				}
			}
			update_post_meta( $post_id, '_lms_files_data', $files_data );
		} else {
			delete_post_meta( $post_id, '_lms_files_data' );
		}
	}

	public function save_visibility_meta_data( $post_id ) {
		if ( ! isset( $_POST['lms_visibility_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['lms_visibility_meta_box_nonce'], 'lms_visibility_meta_box_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['lms_visibility_role'] ) ) {
			update_post_meta( $post_id, '_lms_visibility_role', sanitize_text_field( $_POST['lms_visibility_role'] ) );
		}
	}

}
