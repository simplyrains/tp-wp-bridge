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
	 */	/**
	 * The api_domain of this plugin. (localhost:8001 or touchedition.com)
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $api_domain    The current api_domain of this plugin.
	 */
	private $api_domain;
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
  	$options = get_option($this->plugin_name);
  	if(isset($options['local']) && $options['local']){
			$this->api_domain = 'http://localhost:8001';
  	}else{
  		$this->api_domain = 'https://www.touchedition.com';	
  	}
	}

// PART X: Constants
	const TP_INIT_SITE = 'TP_INIT_SITE';
	const TP_SYNC_POSTS = 'TP_SYNC_POSTS';
	const TP_DELETE_POSTS = 'TP_DELETE_POSTS';
	const TP_SYNC_CATEGORIES = 'TP_SYNC_CATEGORIES';
	const TP_DELETE_CATEGORIES = 'TP_DELETE_CATEGORIES';

// PART A: Basic Settings
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tp-bridge-admin.css', array(), $this->version, 'all' );
	}
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tp-bridge-admin.js', array( 'jquery' ), $this->version, false );
	}
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
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

// PART B: Plugin Settings page
	/**
	 * Render the settings page for this plugin. (wp-admin/options-general.php?page=tp-bridge)
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
  	$options = get_option($this->plugin_name);
    $valid = $options;
    //Cleanup
    $valid['tp_redirect_feed'] = (isset($input['tp_redirect_feed']) && !empty($input['tp_redirect_feed'])) ? 1: 0;
    $valid['tp_enabled'] = (isset($input['tp_enabled']) && !empty($input['tp_enabled'])) ? 1: 0;
    $valid['tp_site_private_key'] = isset($input['tp_site_private_key']) ? $input['tp_site_private_key'] : '';

  	$is_new_site_key = !isset($options['tp_site_private_key']) || strlen($options['tp_site_private_key'])==0 || 
  		$options['tp_site_private_key'] != $valid['tp_site_private_key'];

  	if($is_new_site_key || !isset($options['init_success']) || !$options['init_success']){
	    //make a request to Touchedition, confirm the key, and get the tp_te_url
	    $site_data = array(
				'url' => get_site_url(),
				'categories' => array_values(get_categories())
			);
			$response = $this->send_request_to_tp(self::TP_INIT_SITE, $site_data, $valid['tp_site_private_key']);
		  $response_body = wp_remote_retrieve_body( $response );
		  $response_code = wp_remote_retrieve_response_code( $response );
		  $response_json = json_decode($response_body);

		  if($response_code == 200 && property_exists($response_json, 'domainPath')){
			  $te_url = $response_json->domainPath;
	      $valid['tp_te_url'] = $te_url;
		  	$valid['init_success'] = true;
		  	$valid['tp_enabled'] = 1;
		  }else{
		  	// TE Request returns with an error
		  	// Disable all settings
		  	$valid['tp_redirect_feed'] = 0;
		  	$valid['tp_enabled'] = 0;
		  	$valid['tp_site_private_key'] = '';
		  	$valid['tp_te_url'] = '';
		  	$valid['init_success'] = false;
		  }
  	}
    return $valid;
 }

// PART C: Post Page modification
	/**
	 * Add TP-Reference Column to the post page. (wp-admin/edit.php)
	 *
	 * @since    1.0.0
	 */
 	public function add_tp_reference_head($defaults) {
	    $defaults['tp_reference'] = 'TP Status';
	    return $defaults;
	}
	public function add_tp_reference_body($column_name, $post_ID) {
	    if ($column_name == 'tp_reference') {
	    	$tp_reference = get_post_meta($post_ID, 'tp_reference', true);
        if ($tp_reference) {
            echo '<b>' . $tp_reference . '</b>';
        }
	    }
	}
	public function add_bulk_action_update_tp() 
	{
	    global $typenow; if( $typenow != 'post' ) return; // if used on edit.php screen
	    ?>
	    <script type="text/javascript">
	        jQuery(document).ready(function($) {
	            $('<option>').val('update_tp').text('Update TP Article').appendTo("select[name='action']");
	        });
	    </script>
	    <?php
	}
	public function process_bulk_action_update_tp() 
	{
    # Array with the selected Post IDs
    // todo: implement this and send data
    $post_ids = $_REQUEST['post'];
		$tp_query = array(
			'post__in' => $post_ids,
			'posts_per_page' => -1
		);
		$response_json = $this->sync_posts_with_tp($tp_query);
	}

// PART D: Syncing with Touchedition

	// helper function to add te-site-id and authentication
	public function send_request_to_tp($type, $body, $tp_site_private_key = null){
		$x = $tp_site_private_key;
    if(!isset($tp_site_private_key)){
    	$options = get_option($this->plugin_name);
	    $tp_site_private_key = $options['tp_site_private_key'];
    }
    // wp_die( '<pre>' . print_r(array($x, $tp_site_private_key), true ) . '</pre>' ); 

		$method = 'POST';
		$data = array(
			'type' => $type,
			'data' => $body
		);
		$url = $this->api_domain . '/api/tp/wordpress';
		// $url = $this->api_domain . '/api/tp/logger';
		if($type == self::TP_INIT_SITE){
			$url = $this->api_domain . '/api/tp/wordpress/init';
		}
		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				// TODO: update this
				'te-site-key' => $tp_site_private_key
			),
			'timeout' => 60,
			'body' => json_encode( $data ),
			'method' => $method,
		);
		return wp_remote_post( esc_url_raw($url), $args );
	}

	// Send wp posts to TP (use sync_post_with_tp)
	// For testing purpose
	public function send_tp_data(){
		$postid_to_send = intval( $_POST['id'] );

		$tp_query = array(
			'posts_per_page' => 20,
		);

		if(isset($postid_to_send)){
			$tp_query = array(
				'p' => intval($postid_to_send)
			);
		}

		$response_json = $this->sync_posts_with_tp($tp_query);
		wp_send_json($response_json);
	}
	public function send_tp_data_javascript() { ?>
		<script type="text/javascript" >
		jQuery(document).ready(function($) {

			var data = {
				'action': 'send_tp_data',
				// 'id': 10
			};

			var x = jQuery('#adminmenu');
			x.append('<li><a href="#" id="myButton">CLICKKK</a></li>');
			jQuery("#myButton").click(function () {
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {
					console.log(response);
					alert('Got response from server');
				});
			});
		});
		</script> <?php
	}

	// helper function to send wp posts to TP
	// - called by sync_posts_with_tp and post_updated_cb
	// - $post_query will be used to query with WP'squery_posts, which uses WP's query format
	//   further reading: http://codex.wordpress.org/Class_Reference/WP_Query#Post_.26_Page_Parameters
	public function sync_posts_with_tp($post_query = array()){
		query_posts($post_query);
		$jsonpost = array();

		// loop
		if( have_posts() ):
		  while( have_posts() ): the_post();
				$thispost = array();
				// construct our array for json
				// apply_filters to content to process shortcodes, etc
				$thispost["id"] = get_the_ID();
				$thispost["title"] = get_the_title();
				$thispost["excerpt"] = get_the_excerpt();
				$thispost["permalink"] = apply_filters('the_permalink', get_permalink());
				global $more;
				$more = -1;
				$thispost["content"] = apply_filters('the_content', get_the_content("", false));
				// TODO: use this
				// $thispost["tags"] = get_tags();
				$thispost["categories"] = get_the_category();
				$thispost["author"] = get_the_author();
				$thispost["type"] = get_post_type();
				$thispost["status"] = get_post_status();
				// would rather do iso 8601, but not supported in gwt (yet)
				$thispost["date"] = get_the_time("U");
				if(has_post_thumbnail()){
					$tn_id = get_post_thumbnail_id();
					$img = wp_get_attachment_image_src( $tn_id, 'full' );
					$thispost["thumbnail"] = $img;
				}
				array_push($jsonpost, $thispost);
		  endwhile;
		endif;

		$response = $this->send_request_to_tp(self::TP_SYNC_POSTS, $jsonpost);
	  $response_code = wp_remote_retrieve_response_code( $response );
	  $response_body = wp_remote_retrieve_body( $response );
	  $response_json = json_decode($response_body);

    // wp_kses: escape dangerous string (security purpose)
    $allowed = array( 
        'a' => array( // on allow a tags
            'href' => array() // and those anchors can only have href attribute
        )
    );
	  // modify post mentioned in $response json by adding tp_reference field to it
    // wp_die( '<pre>' . print_r($response_json, true ) . '</pre>' ); 

	  if($response_code == 200){
			foreach ($response_json as $tp_response) {
				if(property_exists($tp_response, 'success') && $tp_response->success){
					update_post_meta( intval($tp_response->id), 'tp_reference', wp_kses( $tp_response->tp_reference, $allowed ) );
				}
			}
		}
		return $response_json;
	}

	// Sync post data with TP
	// Triggered automatically when post_update
	public function post_updated_cb($post_id){
		$tp_query = array(
			'p' => $post_id
		);
		$this->sync_posts_with_tp($tp_query);
	}

	public function trashed_post_cb($post_id){
		$response = $this->send_request_to_tp(self::TP_DELETE_POSTS, array($post_id));
	}

	public function create_category_cb($category_id){
		$response = $this->send_request_to_tp(self::TP_SYNC_CATEGORIES, array(get_category($category_id)));
	}

	public function delete_category_cb($category_id){
		$response = $this->send_request_to_tp(self::TP_DELETE_CATEGORIES, array($category_id));
	}

	public function edit_category_cb($category_id){
		$response = $this->send_request_to_tp(self::TP_SYNC_CATEGORIES, array(get_category($category_id)));
	}
}
