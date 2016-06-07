<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.touchedition.com
 * @since      1.0.0
 *
 * @package    Tp_Bridge
 * @subpackage Tp_Bridge/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<h2 class="nav-tab-wrapper">TP Bridge</h2>

<form method="post" name="cleanup_options" action="options.php">

<?php
    //Grab all options
    $options = get_option($this->plugin_name);

    $tp_enabled = isset($options['tp_enabled'])  ? $options['tp_enabled']:false;
    $tp_redirect_feed = isset($options['tp_redirect_feed']) ? $options['tp_redirect_feed']:false;
    $tp_te_url = isset($options['tp_te_url']) ? $options['tp_te_url']:''; // not used at the moment
    $tp_site_private_key = isset($options['tp_site_private_key']) ? $options['tp_site_private_key']:'';
    settings_fields($this->plugin_name);
    do_settings_sections($this->plugin_name);
    if(isset($options['local'])&& $options['local']){
        var_dump($options);
    }
?>
    <fieldset>
        <p>Touchedition Site Key: </p>
        <input type="text" class="regular-text" id="<?php echo $this->plugin_name; ?>-tp_site_private_key" name="<?php echo $this->plugin_name; ?>[tp_site_private_key]" value="<?php if(!empty($tp_site_private_key)) echo $tp_site_private_key; ?>"/>
    </fieldset>

<?php
    if(isset($tp_te_url) && $tp_te_url!= ''){ 
?>
    <fieldset>
        <p>Touchedition Site: </p>
        <input type="url" class="regular-text" id="<?php echo $this->plugin_name; ?>-tp_te_url" disabled value="<?php if(!empty($tp_te_url)) echo $tp_te_url; ?>"/>
    </fieldset>

    <fieldset style="margin-top: 20px">
        <legend class="screen-reader-text"><span><?php _e('Enable TP Bridge', $this->plugin_name); ?></span></legend>
        <label for="<?php echo $this->plugin_name; ?>-tp_enabled">
            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-tp_enabled" name="<?php echo $this->plugin_name; ?>[tp_enabled]" value="1" <?php checked($tp_enabled, 1); ?>  />
            <span><?php esc_attr_e('Enable TP Bridge', $this->plugin_name); ?></span>
        </label>
    </fieldset>

    <fieldset>
        <legend class="screen-reader-text"><span><?php _e('Enable TP Redirect for front page', $this->plugin_name); ?></span></legend>
        <label for="<?php echo $this->plugin_name; ?>-tp_redirect_feed">
            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-tp_redirect_feed" name="<?php echo $this->plugin_name; ?>[tp_redirect_feed]" value="1" <?php checked($tp_redirect_feed, 1); ?>  />
            <span><?php esc_attr_e('Enable TP Redirect for front page', $this->plugin_name); ?></span>
        </label>
    </fieldset>
<?php
        submit_button('Update Settings', 'primary','submit', TRUE);
    } 
    else{
        submit_button('Link with Touchedition', 'primary','submit', TRUE);
    }
?>