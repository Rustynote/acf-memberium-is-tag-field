<?php

/**
 * Plugin Name:       Advanced Custom Fields: Memberium Tag Field
 * Plugin URI:        https://github.com/Rustynote/acf-memberium-is-tag-field
 * Description:       Create a select field for InfusionSoft tag ID powered by Memberium2.
 * Version:           1.0.0
 * Author:            Jaroslav Suhanek
 * Author URI:        https://wparcanum.com/
 * Requires at least: 4.6
 * Tested up to:      4.8
 * Requires PHP: 	  5.4
 * Stable tag:		  4.3
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */

// Gandalf it if accessed directly.
if(!defined('WPINC')) {
	die;
}

load_plugin_textdomain('acf-memberium-is-tag', false, dirname(plugin_basename(__FILE__)).'/lang/');

if(!function_exists('include_field_types_memberium_is_tag')) {
	function include_field_types_memberium_is_tag($version) {
		include_once 'acf-memberium_is_tag-v5.php';
	}
	add_action('acf/include_field_types', 'include_field_types_memberium_is_tag');
}


if(!class_exists('acf_plugin_memberium_is_tag')) {

	class acf_plugin_memberium_is_tag {
		// vars
		var $settings;

		function __construct() {
			$this->settings = array(
				'version'	=> '1.0.0',
				'url'		=> plugin_dir_url( __FILE__ ),
				'path'		=> plugin_dir_path( __FILE__ )
			);

			// include field
			add_action('acf/include_field_types', 	array($this, 'include_field')); // v5
		}

		function include_field($version = false) {
			load_plugin_textdomain('acf-memberium-is-tag', false, dirname(plugin_basename(__FILE__)).'/lang/');

			include_once 'acf-memberium_is_tag-v5.php';

			new acf_field_memberium_is_tag($this->settings);
		}
	}
	new acf_plugin_memberium_is_tag();

}
