<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.touchedition.com
 * @since      1.0.0
 *
 * @package    Tp_Bridge
 * @subpackage Tp_Bridge/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tp_Bridge
 * @subpackage Tp_Bridge/public
 * @author     Sarin Achawaranont <sarin_a@cleverse.com>
 */
class Tp_Bridge_Public {

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
    $this->wp_tp_options = get_option($this->plugin_name);

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
		 * defined in Tp_Bridge_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tp_Bridge_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tp-bridge-public.css', array(), $this->version, 'all' );

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
		 * defined in Tp_Bridge_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tp_Bridge_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tp-bridge-public.js', array( 'jquery' ), $this->version, false );

	}


  // Script
  public function wp_tp_redirect() {
  	if(isset($_GET['force_wp']) && $_GET['force_wp']){
  		return;
  	}
    if($this->wp_tp_options['tp_te_url'] && $this->wp_tp_options['tp_enabled'] ){
    	$tp_reference = get_post_meta(get_the_ID(), 'tp_reference', true);
    	if(wp_is_mobile()){

    		// Redirect Post
	 			if(is_single() && isset($tp_reference)){
	        wp_redirect( $this->wp_tp_options['tp_te_url'].'/post/'.$tp_reference );
	        exit();
	    	}	

	    	// Redirect Achieve/ Home Page
	    	else if(is_front_page() && $this->wp_tp_options['tp_redirect_feed']){
	        wp_redirect( $this->wp_tp_options['tp_te_url'] );
	        exit();
	    	}
    	}
    }
  }   

	public function cd_meta_box_add()
	{
    add_meta_box( 'tp-tp_reference', 'Touchedition Articles ID', 'cd_meta_box_cb', 'post', 'advanced', 'low' );
    function cd_meta_box_cb($post) {
			$values = get_post_custom( $post->ID );
			$text = isset( $values['tp_reference'] ) ? esc_attr( $values['tp_reference'][0] ) : "";

	    wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
	    echo '<label for="tp_reference">Text Label</label>';
	    echo '<input type="text" name="tp_reference" id="tp_reference" value="'.$text.'"/>';
		}
	}

	public function cd_meta_box_save( $post_id )
	{
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
			return;
		}

		if(!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'my_meta_box_nonce')){
			return;
		}

		if(!current_user_can('edit_posts')){
			return;
		}

    // now we can actually save the data
    $allowed = array( 
        'a' => array( // on allow a tags
            'href' => array() // and those anchors can only have href attribute
        )
    );
     
    // Make sure your data is set before trying to save it
    if( isset( $_POST['tp_reference'] ) )
        update_post_meta( $post_id, 'tp_reference', wp_kses( $_POST['tp_reference'], $allowed ) );
         

	}

}
