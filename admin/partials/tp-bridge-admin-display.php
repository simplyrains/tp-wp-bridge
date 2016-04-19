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

    $tp_enabled = $options['tp_enabled'];
    $tp_redirect_feed = $options['tp_redirect_feed'];

    $tp_te_url = $options['tp_te_url'];
?>

<?php
    settings_fields($this->plugin_name);
    do_settings_sections($this->plugin_name);
?>

<fieldset>
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


<fieldset>
    <p>Touchedition site URL: </p>
    <legend class="screen-reader-text"><span><?php _e('i.e. http://www.touchedition.com', $this->plugin_name); ?></span></legend>
    <input type="url" class="regular-text" id="<?php echo $this->plugin_name; ?>-tp_te_url" name="<?php echo $this->plugin_name; ?>[tp_te_url]" value="<?php if(!empty($tp_te_url)) echo $tp_te_url; ?>"/>
</fieldset>

<?php submit_button('Save all changes', 'primary','submit', TRUE); ?>
