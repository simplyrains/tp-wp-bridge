<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.touchedition.com
 * @since      1.0.0
 *
 * @package    Tp_Bridge
 * @subpackage Tp_Bridge/includes
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
 * @package    Tp_Bridge
 * @subpackage Tp_Bridge/includes
 * @author     Sarin Achawaranont <sarin_a@cleverse.com>
 */
class Tp_Bridge {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tp_Bridge_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		$this->plugin_name = 'tp-bridge';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tp_Bridge_Loader. Orchestrates the hooks of the plugin.
	 * - Tp_Bridge_i18n. Defines internationalization functionality.
	 * - Tp_Bridge_Admin. Defines all hooks for the admin area.
	 * - Tp_Bridge_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tp-bridge-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tp-bridge-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tp-bridge-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tp-bridge-public.php';

		$this->loader = new Tp_Bridge_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tp_Bridge_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tp_Bridge_i18n();

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

		$plugin_admin = new Tp_Bridge_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add menu item
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// Add Settings link to the plugin
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );
		
		// Save/Update our plugin options
		$this->loader->add_action('admin_init', $plugin_admin, 'options_update');

		// Show tp_reference field in admin page
		$this->loader->add_filter('manage_posts_columns', $plugin_admin, 'add_tp_reference_head');
		$this->loader->add_action( 'manage_posts_custom_column', $plugin_admin, 'add_tp_reference_body', 10, 2 );

 		// Show tp_reference field in admin page
		$this->loader->add_action( 'admin_footer-edit.php', $plugin_admin, 'add_bulk_action_update_tp');
		$this->loader->add_action( 'admin_action_update_tp', $plugin_admin, 'process_bulk_action_update_tp');

 		// add ajax backend function to admin-ajax
		$this->loader->add_action('wp_ajax_send_tp_data', $plugin_admin, 'send_tp_data');
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'send_tp_data_javascript' ); // Write our JS below here

		// callback when edit/trash post
		$this->loader->add_action( 'post_updated', $plugin_admin, 'post_updated_cb' );
		// callback when trash post
		$this->loader->add_action( 'trashed_post', $plugin_admin, 'trashed_post_cb' );

		// create category
		$this->loader->add_action( 'create_category', $plugin_admin, 'create_category_cb', 10, 1 );
		// delete category
		$this->loader->add_action( 'delete_category', $plugin_admin, 'delete_category_cb', 10, 1 );
		// edit category
		$this->loader->add_action( 'edit_category', $plugin_admin, 'edit_category_cb', 10, 1 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tp_Bridge_Public( $this->get_plugin_name(), $this->get_version() );

		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

    //Actions
    $this->loader->add_action( 'template_redirect', $plugin_public, 'wp_tp_redirect' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_public, 'cd_meta_box_add' );
		$this->loader->add_action( 'save_post', $plugin_public, 'cd_meta_box_save' );

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
	 * @return    Tp_Bridge_Loader    Orchestrates the hooks of the plugin.
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

}
