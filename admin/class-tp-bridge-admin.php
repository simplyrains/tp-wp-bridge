<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.touchedition.com
 * @since      1.0.0
 *
 * @package    Tp_Bridge
 * @subpackage Tp_Bridge/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tp_Bridge
 * @subpackage Tp_Bridge/admin
 * @author     Sarin Achawaranont <sarin_a@cleverse.com>
 */
class Tp_Bridge_Admin {

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
		 * defined in Tp_Bridge_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tp_Bridge_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tp-bridge-admin.css', array(), $this->version, 'all' );

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
		 * defined in Tp_Bridge_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tp_Bridge_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tp-bridge-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	 
	public function add_plugin_admin_menu() {

	    /*
	     * Add a settings page for this plugin to the Settings menu.
	     *
	     * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
	     *
	     *        Administration Menus: http://codex.wordpress.org/Administration_Menus
	     *
	     */
	    add_options_page( 'TP Bridge Settings', 'TP Bridge', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page'));
	}

	 /**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	 
	public function add_action_links( $links ) {
	    /*
	    *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
	    */
	   $settings_link = array(
	    '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
	   );
	   return array_merge(  $settings_link, $links );

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	 
	public function display_plugin_setup_page() {
	    include_once( 'partials/tp-bridge-admin-display.php' );
	}

	public function options_update() {
		register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
	}

	// Validate Settings
	public function validate($input) {
    // All checkboxes inputs        
    $valid = array();
    //Cleanup
    $valid['tp_redirect_feed'] = (isset($input['tp_redirect_feed']) && !empty($input['tp_redirect_feed'])) ? 1: 0;
    $valid['tp_enabled'] = (isset($input['tp_enabled']) && !empty($input['tp_enabled'])) ? 1: 0;
    $valid['tp_te_url'] = esc_url($input['tp_te_url']);
    
    return $valid;
 }

	// ADD NEW COLUMN
	public function add_tp_reference_head($defaults) {
	    $defaults['tp_reference'] = 'TP Status';
	    return $defaults;
	}
	 
	// SHOW THE FEATURED IMAGE
	public function add_tp_reference_body($column_name, $post_ID) {
	    if ($column_name == 'tp_reference') {
	    	$tp_reference = get_post_meta($post_ID, 'tp_reference', true);
        if ($tp_reference) {
            echo '<b>' . $tp_reference . '</b>';
        }
	    }
	}

	public function send_tp_data(){
		global $wpdb; // this is how you get access to the database
		$whatever = intval( $_POST['whatever'] );
		$whatever += 10;
    echo $whatever;
		wp_die(); // this is required to terminate immediately and return a proper response
	}

	public function send_tp_data_javascript() { ?>
		<script type="text/javascript" >
		console.log('x');
		jQuery(document).ready(function($) {

			var data = {
				'action': 'send_tp_data',
				'whatever': 1234
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {
				console.log(response);
				alert('Got this from the server: ' + response);
			});
		});
		</script> <?php
	}


}
