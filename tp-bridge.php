<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.touchedition.com
 * @since             1.0.0
 * @package           Tp_Bridge
 *
 * @wordpress-plugin
 * Plugin Name:       TP Bridge
 * Plugin URI:        https://github.com/simplyrains/tp-wp-bridge
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Sarin Achawaranont
 * Author URI:        http://www.touchedition.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tp-bridge
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tp-bridge-activator.php
 */
function activate_tp_bridge() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tp-bridge-activator.php';
	Tp_Bridge_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tp-bridge-deactivator.php
 */
function deactivate_tp_bridge() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tp-bridge-deactivator.php';
	Tp_Bridge_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tp_bridge' );
register_deactivation_hook( __FILE__, 'deactivate_tp_bridge' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tp-bridge.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tp_bridge() {

	$plugin = new Tp_Bridge();
	$plugin->run();

}
run_tp_bridge();
