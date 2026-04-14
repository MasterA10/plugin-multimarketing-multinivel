<?php

class Expressive_Core {

	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->plugin_name = 'expressive-core';
		$this->version = EXPRESSIVE_CORE_VERSION;
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		// CRITICAL: Register cron intervals EARLY so WP-Cron recognizes them before init
		add_filter( 'cron_schedules', array( $this, 'register_custom_cron_intervals' ) );
	}

	private function load_dependencies() {
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-logger.php';
		Expressive_Logger::init();

		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-cpt.php';
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-access.php';
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-public.php';
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-engine.php';
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-calendar.php';
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-referral.php';
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-gamification.php';
		require_once EXPRESSIVE_CORE_PATH . 'admin/class-expressive-admin-settings.php';
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-certificate.php';
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-auth.php';
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-external-api.php';
		require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-woo-audit.php';
	}

	private function define_admin_hooks() {
		$cpt       = new Expressive_CPT();
		$access    = new Expressive_Access();
		$engine    = new Expressive_Engine();
		$referral  = new Expressive_Referral();
		$gamify    = new Expressive_Gamification();
		$settings  = new Expressive_Admin_Settings();
		$cert      = new Expressive_Certificate();
		$woo_audit = new Expressive_Woo_Audit();

		$woo_audit->register_hooks();

		add_action( 'init', array( $cpt, 'register_custom_post_types' ) );
		add_action( 'add_meta_boxes', array( $cpt, 'add_lesson_meta_boxes' ) );
		add_action( 'save_post', array( $cpt, 'save_lesson_meta_data' ) );
		add_action( 'save_post', array( $cpt, 'save_visibility_meta_data' ) );
		add_action( 'admin_menu', array( $settings, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_post_lms_save_elite_content', array( $settings, 'handle_elite_editor_save' ) );
		add_action( 'admin_post_lms_delete_elite_content', array( $settings, 'handle_elite_content_delete' ) );
		add_action( 'wp_ajax_lms_update_module_and_lesson_order', array( $settings, 'ajax_update_module_and_lesson_order' ) );

		// Middleware & Engine
		add_action( 'template_redirect', array( $access, 'protect_content_middleware' ) );
		add_action( 'init', array( $engine, 'handle_safe_download' ) );
		$engine->register_ajax_hooks();
		$referral->register_hooks();
		$gamify->register_hooks();
		$cert->register_hooks();

		// Template Overrides
		add_filter( 'template_include', array( $this, 'template_loader' ) );
		
		// General Init
		add_action( 'init', array( $this, 'init' ), 20 );
	}

	private function define_public_hooks() {
		$public   = new Expressive_Public();
		$calendar = new Expressive_Calendar();
		$auth     = new Expressive_Auth();
 
		add_action( 'init', array( $public, 'register_shortcodes' ) );
		add_action( 'init', array( $calendar, 'register_calendar_shortcode' ) );
		$auth->register_hooks();
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		
		// Universal Elite Avatar Filter
		add_filter( 'get_avatar', array( $this, 'elite_avatar_filter' ), 10, 5 );

		// Auto-block new users for Elite Area
		add_action( 'user_register', array( $this, 'auto_block_new_user' ) );
	}

	public function enqueue_admin_assets( $hook ) {
		// Only load on our custom pages to be efficient
		if ( strpos( $hook, 'expressive-lms' ) === false && 
			 strpos( $hook, 'elite-content' ) === false && 
			 strpos( $hook, 'elite-calendar' ) === false && 
			 strpos( $hook, 'elite-settings' ) === false ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'elite-admin-font', 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700&family=Playfair+Display:ital,wght@1,400;1,700&display=swap' );
	}

	public function enqueue_public_assets() {
		// Native WP Icons
		wp_enqueue_style( 'dashicons' );

		// Global Styling
		wp_enqueue_style( 'expressive-lms-style', plugin_dir_url( __FILE__ ) . '../public/css/lms-style.css', array(), $this->version );

		// LMS Engine
		wp_enqueue_script( 'expressive-lms-engine', plugin_dir_url( __FILE__ ) . '../public/js/lms-engine.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( 'expressive-lms-engine', 'lms_vars', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'lms_engine_nonce' ),
		) );

		// Referral Tracker (Global)
		wp_enqueue_script( 'expressive-referral-tracker', plugin_dir_url( __FILE__ ) . '../public/js/lms-referral-tracker.js', array(), $this->version, true );
	}

	public function template_loader( $template ) {
		if ( is_singular( 'lms_lesson' ) ) {
			$new_template = EXPRESSIVE_CORE_PATH . 'templates/single-lms_lesson.php';
			if ( file_exists( $new_template ) ) {
				return $new_template;
			}
		}

		if ( is_singular( 'lms_course' ) ) {
			$new_template = EXPRESSIVE_CORE_PATH . 'templates/single-lms_course.php';
			if ( file_exists( $new_template ) ) {
				return $new_template;
			}
		}

		if ( is_singular( 'lms_live' ) ) {
			$new_template = EXPRESSIVE_CORE_PATH . 'templates/single-lms_live.php';
			if ( file_exists( $new_template ) ) {
				return $new_template;
			}
		}

		// Handle Special LMS Pages (Dashboard, Login)
		if ( is_page() ) {
			$page_type = get_post_meta( get_the_ID(), '_lms_page_type', true );
			// Elite Educator Dashboard
			if ( is_page('dashboard-educador') ) {
				if ( ! is_user_logged_in() ) {
					auth_redirect();
				}
				// Admin Overdrive: Admins can always see the educator dashboard
				if ( ! current_user_can( 'manage_options' ) && ! get_user_meta( get_current_user_id(), '_lms_is_educator', true ) ) {
					wp_safe_redirect( home_url( '/area-de-membros/' ) );
					exit;
				}
				return EXPRESSIVE_CORE_PATH . 'templates/page-educator-dashboard.php';
			}
			if ( $page_type ) {
				$custom_template = '';
				switch ( $page_type ) {
					case 'login':
						$custom_template = EXPRESSIVE_CORE_PATH . 'templates/page-login.php';
						break;
					case 'area-de-membros':
						$custom_template = EXPRESSIVE_CORE_PATH . 'templates/page-member-dashboard.php';
						break;
					case 'dashboard-educador':
						$custom_template = EXPRESSIVE_CORE_PATH . 'templates/page-educator-dashboard.php';
						break;
					case 'adquirir-acesso':
						$custom_template = EXPRESSIVE_CORE_PATH . 'templates/page-purchase-access.php';
						break;
				}

				if ( $custom_template && file_exists( $custom_template ) ) {
					return $custom_template;
				}
			}
		}

		// Handle Certificate Global Route (User custom request)
		if ( strpos( $_SERVER['REQUEST_URI'], 'certificado-elite' ) !== false ) {
			$cert = new Expressive_Certificate();
			$cert->handle_global_certificate_view();
			exit;
		}

		return $template;
	}

	public function init() {
		// Flush rules once to fix broken links requested by user
		if ( get_option( 'lms_needs_flush_v2' ) !== 'no' ) {
			flush_rewrite_rules();
			update_option( 'lms_needs_flush_v2', 'no' );
		}

		// --- WP CRON: Sincronização Periódica da API ---
		add_action( 'lms_api_periodic_sync_task', array( 'Expressive_External_API', 'sync_all_users_status' ) );

		$configured_interval = max( 180, intval( get_option( 'lms_api_sync_interval', 3 ) ) * 60 );
		$next_scheduled = wp_next_scheduled( 'lms_api_periodic_sync_task' );

		if ( ! $next_scheduled ) {
			// Nenhum evento agendado — cria pela primeira vez
			wp_schedule_event( time(), 'lms_custom_sync', 'lms_api_periodic_sync_task' );
		} else {
			// Verifica se o intervalo mudou nas configurações
			$stored_interval = get_option( 'lms_api_cron_interval_seconds', 0 );
			if ( (int) $stored_interval !== $configured_interval ) {
				// Intervalo mudou — reagenda com o novo valor
				wp_clear_scheduled_hook( 'lms_api_periodic_sync_task' );
				wp_schedule_event( time(), 'lms_custom_sync', 'lms_api_periodic_sync_task' );
				update_option( 'lms_api_cron_interval_seconds', $configured_interval );
			}
		}
	}

	public function register_custom_cron_intervals( $schedules ) {
		$interval_minutes = intval( get_option( 'lms_api_sync_interval', 3 ) );
		$interval_seconds = max( 180, $interval_minutes * 60 ); // Min 3 mins

		$schedules['lms_custom_sync'] = array(
			'interval' => $interval_seconds,
			'display'  => sprintf( 'Elite API Sync (Cada %d min)', $interval_minutes )
		);
		return $schedules;
	}

	public function run() {
		// Kicking off the plugin
	}

	/**
	 * Universal Avatar Filter: Force custom photo if exists
	 */
	public function elite_avatar_filter( $avatar, $id_or_email = null, $size = 96, $default = '', $alt = '' ) {
		$user_id = 0;

		if ( is_numeric( $id_or_email ) ) {
			$user_id = (int) $id_or_email;
		} elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ) {
			$user_id = $user->ID;
		} elseif ( is_object( $id_or_email ) ) {
			if ( $id_or_email instanceof WP_User ) {
				$user_id = $id_or_email->ID;
			} elseif ( $id_or_email instanceof WP_Comment ) {
				$user_id = (int) $id_or_email->user_id;
			} elseif ( $id_or_email instanceof WP_Post ) {
				$user_id = (int) $id_or_email->post_author;
			} elseif ( isset( $id_or_email->user_id ) ) {
				$user_id = (int) $id_or_email->user_id;
			}
		}

		if ( $user_id ) {
			$custom_avatar_url = get_user_meta( $user_id, '_lms_custom_avatar', true );
			if ( $custom_avatar_url ) {
				$avatar = sprintf(
					'<img alt="%s" src="%s" class="avatar avatar-%d user-%d-avatar photo" height="%d" width="%d" style="object-fit:cover; border-radius:inherit;">',
					esc_attr( $alt ),
					esc_url( $custom_avatar_url ),
					(int) $size,
					(int) $user_id,
					(int) $size,
					(int) $size
				);
			}
		}

		return $avatar;
	}

	public static function get_elite_avatar( $user_id, $size = 96, $extra_classes = '' ) {
		// Since we now have the universal filter, this just wraps get_avatar
		return get_avatar( $user_id, $size, '', '', array( 'class' => $extra_classes ) );
	}

	/**
	 * Automatically block new users from Elite Area by default.
	 * Synchronizes both LMS Manual Status and API Manager Status.
	 */
	public function auto_block_new_user( $user_id ) {
		// Unified Block via Expressive_Access
		Expressive_Access::update_access_status( $user_id, 'blocked' );
		
		// Optional: Initialize API check timestamp
		update_user_meta( $user_id, '_lms_elite_api_last_check', time() );

		Expressive_Logger::info( 'AUTH', "Novo usuário bloqueado e sincronizado via Central de Acesso", array( 'user_id' => $user_id ) );
	}

}
