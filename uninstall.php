<?php
/**
 * uninstalling simple access control
 * remove all settings and meta data seved by simple access control 
 */
if(! defined('WP_UNINSTALL_PLUGIN'))exit(); // get out if not called by wordpress uninstall
global $wpdb;
$table = $wpdb->prefix . 's_promo_hits';
$wpdb->query("drop table $table");
 