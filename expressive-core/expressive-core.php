<?php
/**
 * Expressive Core LMS - Internal Module
 *
 * O esqueleto de performance para a sua plataforma de membros e gestão de aulas.
 *
 * @package    Multinivel_Marketing
 * @subpackage Expressive_Core
 * @version    1.0.0
 * @author     Alex Alves
 * @link       https://dominai.cloud
 * @license    GPL v2 or later
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'EXPRESSIVE_CORE_VERSION', '1.0.0' );
define( 'EXPRESSIVE_CORE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Activation Hook
 */
function activate_expressive_core() {
	require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-activator.php';
	Expressive_Activator::activate();
}

/**
 * Initialization
 */
require_once EXPRESSIVE_CORE_PATH . 'includes/class-expressive-core.php';

function run_expressive_core() {
	$plugin = new Expressive_Core();
	$plugin->run();
}
run_expressive_core();
