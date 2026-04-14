<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://dominai.cloud
 * @since      1.0.0
 *
 * @package    Multinivel_marketing
 * @subpackage Multinivel_marketing/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Multinivel_marketing
 * @subpackage Multinivel_marketing/includes
 * @author     Alex Alves <nasalexalves@gmail.com>
 */
class Multinivel_marketing {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Multinivel_marketing_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'MULTINIVEL_MARKETING_VERSION' ) ) {
			$this->version = MULTINIVEL_MARKETING_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'multinivel_marketing';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->loader->add_action( 'init', $this, 'register_custom_post_types' );
		$this->loader->add_action( 'init', $this, 'register_taxonomies' );

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Multinivel_marketing_Loader. Orchestrates the hooks of the plugin.
	 * - Multinivel_marketing_i18n. Defines internationalization functionality.
	 * - Multinivel_marketing_Admin. Defines all hooks for the admin area.
	 * - Multinivel_marketing_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-multinivel_marketing-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-multinivel_marketing-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-multinivel_marketing-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-multinivel_marketing-public.php';

		/**
		 * The class responsible for handling WooCommerce role-based discounts.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-multinivel_marketing-wc-discounts.php';

		$this->loader = new Multinivel_marketing_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Multinivel_marketing_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Multinivel_marketing_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Multinivel_marketing_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Multinivel_marketing_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Register Shortcodes
		add_shortcode( 'mlm_dashboard', array( $plugin_public, 'render_dashboard' ) );
		add_shortcode( 'mlm_login', array( $plugin_public, 'render_login' ) );

		// WooCommerce Discounts
		$plugin_discounts = new Multinivel_marketing_WC_Discounts();
		$this->loader->add_action( 'init', $plugin_discounts, 'ensure_roles_exist' );
		
		if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
			// Removed to fix duplicate discount application with Expressive_Engine
			// $this->loader->add_action( 'woocommerce_cart_calculate_fees', $plugin_discounts, 'apply_custom_discounts', 20, 1 );
			$this->loader->add_action( 'woocommerce_created_customer', $plugin_discounts, 'assign_role_on_registration', 10, 1 );
			$this->loader->add_action( 'woocommerce_checkout_order_processed', $plugin_discounts, 'assign_role_on_checkout', 10, 3 );
		}

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Multinivel_marketing_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_custom_post_types() {
		// Courses
		register_post_type( 'mlm_course', array(
			'labels' => array(
				'name'          => 'Cursos',
				'singular_name' => 'Curso',
			),
			'public'      => true,
			'has_archive' => true,
			'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'   => 'dashicons-welcome-learn-more',
			'show_in_rest' => true,
		) );

		// Lessons
		register_post_type( 'mlm_lesson', array(
			'labels' => array(
				'name'          => 'Aulas',
				'singular_name' => 'Aula',
			),
			'public'      => true,
			'has_archive' => false,
			'supports'    => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon'   => 'dashicons-video-alt3',
			'show_in_rest' => true,
		) );
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies() {
		register_taxonomy( 'mlm_module', array( 'mlm_lesson' ), array(
			'labels' => array(
				'name'          => 'Módulos',
				'singular_name' => 'Módulo',
			),
			'hierarchical' => true,
			'show_ui'      => true,
			'show_in_rest' => true,
		) );
	}

}
