<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://dominai.cloud
 * @since      1.0.0
 *
 * @package    Multinivel_marketing
 * @subpackage Multinivel_marketing/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Multinivel_marketing
 * @subpackage Multinivel_marketing/admin
 * @author     Alex Alves <nasalexalves@gmail.com>
 */
class Multinivel_marketing_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/multinivel_marketing-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/multinivel_marketing-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the admin menu.
	 */
	public function add_admin_menu() {
		add_menu_page(
			'Elite Members',
			'Elite Members',
			'manage_options',
			'mlm-settings',
			array( $this, 'render_settings_page' ),
			'dashicons-vault',
			25
		);

		add_submenu_page(
			'mlm-settings',
			'Assinantes',
			'Assinantes',
			'manage_options',
			'mlm-subscribers',
			array( $this, 'render_subscribers_page' )
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap mlm-admin-wrap">
			<h1 class="mlm-admin-title">Configurações Elite Members</h1>
			<p>Bem-vindo ao painel de gerenciamento da sua área de membros luxuosa.</p>

			<div class="mlm-admin-card" style="background:#fff; padding:20px; border-radius:10px; border-left:4px solid #d4af37; max-width: 600px;">
				<h2>Resumo do Sistema</h2>
				<ul>
					<li><strong>Dashboard:</strong> <a href="<?php echo site_url('/area-de-membros/'); ?>" target="_blank">Ver Mapa do Aluno</a></li>
					<li><strong>Login:</strong> <a href="<?php echo site_url('/login-aluno/'); ?>" target="_blank">Ver Página de Login</a></li>
				</ul>
				<hr>
				<p>Para adicionar conteúdos, vá em "Cursos" e "Aulas" no menu lateral.</p>
				<p>Para gerenciar acessos, acesse o submenu <strong><a href="admin.php?page=mlm-subscribers">Assinantes</a></strong>.</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the Subscribers (Assinantes) page & handle submission.
	 */
	public function render_subscribers_page() {
		// Processar ações de suspensão/reativação
		if ( isset( $_POST['mlm_action'] ) && isset( $_POST['user_id'] ) && current_user_can( 'manage_options' ) ) {
			check_admin_referer( 'mlm_toggle_subscription' );
			$user_id = intval( $_POST['user_id'] );
			
			if ( $_POST['mlm_action'] === 'suspend' ) {
				Expressive_Access::update_access_status( $user_id, 'blocked' );
				echo '<div class="notice notice-warning is-dismissible"><p>Acesso suspenso para o usuário ID ' . $user_id . '.</p></div>';
			} elseif ( $_POST['mlm_action'] === 'activate' ) {
				Expressive_Access::update_access_status( $user_id, 'active' );
				echo '<div class="notice notice-success is-dismissible"><p>Acesso ativado para o usuário ID ' . $user_id . '.</p></div>';
			}
		}

		// Buscar usuários (incluindo administradores para controle total)
		$users = get_users( array(
			'role__in' => array( 'educadora', 'autoridade', 'administrator' )
		) );

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Gerenciar Assinantes Elite</h1>
			<hr class="wp-header-end">
			<p>Abaixo estão todas as Educadoras e Autoridades conectadas ao sistema. Você pode suspender o acesso daqueles que não estiverem com a mensalidade em dia.</p>
			
			<table class="wp-list-table widefat fixed striped table-view-list users">
				<thead>
					<tr>
						<th class="manage-column">Nome</th>
						<th class="manage-column">E-mail</th>
						<th class="manage-column">Função</th>
						<th class="manage-column">Indicado por</th>
						<th class="manage-column">Status da Assinatura</th>
						<th class="manage-column">Ações</th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php if ( empty( $users ) ) : ?>
						<tr class="no-items"><td class="colspanchange" colspan="6">Nenhum assinante encontrado.</td></tr>
					<?php else : ?>
						<?php foreach ( $users as $u ) : 
							$roles = (array) $u->roles;
							$is_admin = in_array( 'administrator', $roles );

							// Identificação amigável de papel
							if ( $is_admin ) {
								$role_name = '<span style="background: #111; color: #d4af37; padding: 3px 8px; border-radius: 5px; font-size: 10px; font-weight: bold; border: 1px solid #d4af37;">Administrador</span>';
							} elseif ( in_array( 'educadora', $roles ) ) {
								$role_name = 'Educadora';
							} else {
								$role_name = 'Autoridade';
							}

							// Resolver nome do indicador (Busca Híbrida)
							global $wpdb;
							$table_referrals = $wpdb->prefix . 'lms_referrals';
							
							// 1. Tentar buscar na tabela oficial do plugin
							$db_educator_id = $wpdb->get_var($wpdb->prepare(
								"SELECT educator_id FROM $table_referrals WHERE authority_id = %d LIMIT 1",
								$u->ID
							));

							$referrer = '-';
							if ( $db_educator_id ) {
								$ref_obj = get_userdata( $db_educator_id );
								$referrer = $ref_obj ? $ref_obj->display_name : '-';
							} else {
								// 2. Fallback: buscar no usermeta legada
								$referrer_login = get_user_meta( $u->ID, '_exp_referred_by', true );
								if ( $referrer_login ) {
									$ref_obj = get_user_by( 'login', $referrer_login );
									$referrer = $ref_obj ? $ref_obj->display_name : $referrer_login;
								}
							}
							
							$access_checker = new Expressive_Access();
							$is_active = $access_checker->has_active_subscription( $u->ID );
						?>
						<tr>
							<td class="username column-username has-row-actions column-primary">
								<strong><?php echo esc_html( $u->display_name ); ?></strong>
							</td>
							<td class="email column-email">
								<a href="mailto:<?php echo esc_attr( $u->user_email ); ?>"><?php echo esc_html( $u->user_email ); ?></a>
							</td>
							<td class="role column-role"><?php echo $role_name; ?></td>
							<td class="role column-role"><?php echo esc_html( $referrer ); ?></td>
							<td class="role column-role">
								<?php if ( $is_admin ) : ?>
									<span style="color: #d4af37; font-weight: bold;">💎 Acesso Vitalício</span>
								<?php elseif ( ! $is_active ) : ?>
									<span style="color: #d63638; font-weight: bold;">⛔ Suspenso / Vencido</span>
								<?php else: ?>
									<span style="color: #00a32a; font-weight: bold;">✅ Ativo</span>
								<?php endif; ?>
							</td>
							<td class="role column-role">
								<?php if ( ! $is_admin ) : ?>
									<form method="post" style="display:inline-block;">
										<?php wp_nonce_field( 'mlm_toggle_subscription' ); ?>
										<input type="hidden" name="user_id" value="<?php echo $u->ID; ?>">
										<?php if ( ! $is_active ) : ?>
											<input type="hidden" name="mlm_action" value="activate">
											<button type="submit" class="button button-primary">Reativar Acesso</button>
										<?php else: ?>
											<input type="hidden" name="mlm_action" value="suspend">
											<button type="submit" class="button" style="color: #d63638; border-color: #d63638;">Suspender Acesso</button>
										<?php endif; ?>
									</form>
								<?php else : ?>
									<span class="description">Imunidade Ativa</span>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

}
