<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://dominai.cloud
 * @since      1.0.0
 *
 * @package    Multinivel_marketing
 * @subpackage Multinivel_marketing/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Multinivel_marketing
 * @subpackage Multinivel_marketing/public
 * @author     Alex Alves <nasalexalves@gmail.com>
 */
class Multinivel_marketing_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Multinivel_marketing_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Multinivel_marketing_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Playfair+Display:wght@700&display=swap', array(), $this->version );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/multinivel_marketing-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Multinivel_marketing_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Multinivel_marketing_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/multinivel_marketing-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Member Dashboard Shortcode Callback.
	 */
	public function render_dashboard() {
		if ( ! is_user_logged_in() ) {
			return $this->render_login_form( 'Por favor, faça login para acessar a área de membros.' );
		}

		$current_user = wp_get_current_user();
		
		ob_start();
		?>
		<div class="mlm-container mlm-dashboard">
			<header class="mlm-header">
				<h1 class="mlm-title-serif">Bem-vindo, <?php echo esc_html( $current_user->display_name ); ?></h1>
				<p class="mlm-subtitle">Seus cursos e progresso em um só lugar.</p>
			</header>

			<div class="mlm-grid">
				<?php
				$courses = new WP_Query( array( 'post_type' => 'mlm_course', 'posts_per_page' => 6 ) );
				if ( $courses->have_posts() ) :
					while ( $courses->have_posts() ) : $courses->the_post();
						?>
						<div class="mlm-card">
							<div class="mlm-card-image">
								<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium' ); endif; ?>
							</div>
							<div class="mlm-card-content">
								<h2 class="mlm-card-title-serif"><?php the_title(); ?></h2>
								<p><?php echo wp_trim_words( get_the_excerpt(), 15 ); ?></p>
								<a href="<?php the_permalink(); ?>" class="mlm-btn-gold">Acessar Curso</a>
							</div>
						</div>
						<?php
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p>Nenhum curso disponível no momento.</p>';
				endif;
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Login Shortcode Callback.
	 */
	public function render_login() {
		if ( is_user_logged_in() ) {
			return '<p>Você já está logado. <a href="' . site_url( '/area-de-membros/' ) . '">Ir para o Dashboard</a></p>';
		}
		return $this->render_login_form();
	}

	private function render_login_form( $message = '' ) {
		ob_start();
		?>
		<div class="mlm-container mlm-login-box">
			<div class="mlm-login-wrapper">
				<h1 class="mlm-title-serif">Área de Alunos</h1>
				<?php if ( $message ) : ?>
					<p class="mlm-alert"><?php echo esc_html( $message ); ?></p>
				<?php endif; ?>
				<?php
				wp_login_form( array(
					'redirect' => site_url( '/area-de-membros/' ),
					'label_username' => 'E-mail ou Usuário',
					'label_password' => 'Senha',
					'label_log_in'   => 'Entrar no Portal Elite',
					'remember'       => true,
				) );
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

}
