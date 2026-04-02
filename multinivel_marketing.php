<?php
/**
 * Plugin Name:       Área de Membros Elite - Gold Edition
 * Plugin URI:        https://dominai.cloud
 * Description:       Um plugin luxuoso de criação e gerenciamento de área de membros e gestão de aulas no WordPress.
 * Version:           1.0.0
 * Author:            Alex Alves
 * Author URI:        https://dominai.cloud/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       multinivel_marketing
 * Domain Path:       /languages
 *
 * @link              https://dominai.cloud
 * @since             1.0.0
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MULTINIVEL_MARKETING_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-multinivel_marketing-activator.php
 */
function activate_multinivel_marketing() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-multinivel_marketing-activator.php';
	Multinivel_marketing_Activator::activate();

	// Load and activate Expressive Core LMS Module
	require_once plugin_dir_path( __FILE__ ) . 'expressive-core/expressive-core.php';
	if ( function_exists( 'activate_expressive_core' ) ) {
		activate_expressive_core();
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-multinivel_marketing-deactivator.php
 */
function deactivate_multinivel_marketing() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-multinivel_marketing-deactivator.php';
	Multinivel_marketing_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_multinivel_marketing' );
register_deactivation_hook( __FILE__, 'deactivate_multinivel_marketing' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-multinivel_marketing.php';

/**
 * Load the Expressive Core LMS Engine.
 */
require_once plugin_dir_path( __FILE__ ) . 'expressive-core/expressive-core.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_multinivel_marketing() {

	$plugin = new Multinivel_marketing();
	$plugin->run();

}
run_multinivel_marketing();
