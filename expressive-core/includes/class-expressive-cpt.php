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
		add_meta_box(
			'lms_lesson_details',
			'Detalhes da Aula',
			array( $this, 'render_lesson_meta_box' ),
			'lms_lesson',
			'normal',
			'high'
		);
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
	}

}
