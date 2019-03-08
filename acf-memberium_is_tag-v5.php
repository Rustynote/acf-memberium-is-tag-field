<?php

// Gandalf it if accessed directly.
if(!defined('WPINC')) {
	die;
}

// check if class already exists
if(!class_exists('acf_field_memberium_is_tag')) {

	class acf_field_memberium_is_tag extends acf_field_select {


		/*
		*  __construct
		*
		*  This function will setup the field type data
		*
		*  @type	function
		*  @date	5/03/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/

		function __construct($settings) {
			$this->settings = $settings;

	    	parent::__construct();
		}

		function initialize() {
			// vars
			$this->name     = 'memberium_is_tag';
			$this->label    = __('Memberium Infusion Tag', 'acf-memberium-is-tag');
			$this->category = 'choice';
			$this->defaults = array(
				'multiple' 		=> 0,
				'allow_null' 	=> 0,
				'default_value'	=> '',
				'return_format'	=> 'comma'
			);

			// ajax
			add_action('wp_ajax_acf/fields/memberium_is_tag/query', array($this, 'ajax_query'));
			// add_action('wp_ajax_nopriv_acf/fields/select/query', array($this, 'ajax_query'));

		}


		/*
		*  render_field_settings()
		*
		*  Create extra settings for your field. These are visible when editing a field
		*
		*  @type	action
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	$field (array) the $field being edited
		*  @return	n/a
		*/

		function render_field_settings($field) {

			$field['default_value'] = acf_encode_choices($field['default_value'], false);

			// default_value
			acf_render_field_setting($field, array(
				'label'			=> __('Default Value', 'acf-memberium-is-tag'),
				'instructions'	=> __('Enter each default value on a new line', 'acf-memberium-is-tag'),
				'name'			=> 'default_value',
				'type'			=> 'textarea',
			));

			// allow_null
			acf_render_field_setting($field, array(
				'label'			=> __('Allow Null?', 'acf-memberium-is-tag'),
				'instructions'	=> '',
				'name'			=> 'allow_null',
				'type'			=> 'true_false',
				'ui'			=> 1,
			));

			// multiple
			acf_render_field_setting($field, array(
				'label'			=> __('Select multiple values?', 'acf-memberium-is-tag'),
				'instructions'	=> '',
				'name'			=> 'multiple',
				'type'			=> 'true_false',
				'ui'			=> 1,
			));

			// return_format
			acf_render_field_setting($field, array(
				'label'			=> __('Return Format', 'acf-memberium-is-tag'),
				'instructions'	=> __('Specify the value returned', 'acf-memberium-is-tag'),
				'type'			=> 'select',
				'name'			=> 'return_format',
				'choices'		=> array(
					'comma'			=> __('Comma separated string', 'acf-memberium-is-tag'),
					'array'			=> __('Array', 'acf-memberium-is-tag'),
				)
			));
		}



		/*
		*  render_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param	$field (array) the $field being rendered
		*
		*  @type	action
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	$field (array) the $field being edited
		*  @return	n/a
		*/

		function render_field($field) {
			// convert
			$value = acf_get_array($field['value']);
			$choices = [];

			// placeholder
			if(empty($field['placeholder'])) {
				$field['placeholder'] = _x('Select', 'verb', 'acf');
			}

			// add empty value (allows '' to be selected)
			if(empty($value)) {
				$value = array('');
			}

			// prepend empty choice
			// - only for single selects
			// - have tried array_merge but this causes keys to re-index if is numeric (post ID's)
			if($field['allow_null'] && !$field['multiple']) {
				$choices = array('' => "- {$field['placeholder']} -") + $choices;
			}

			// vars
			$select = array(
				'id'				=> $field['id'],
				'class'				=> $field['class'],
				'name'				=> $field['name'],
				'data-ui'			=> true,
				'data-ajax'			=> true,
				'data-multiple'		=> $field['multiple'],
				'data-placeholder'	=> $field['placeholder'],
				'data-allow_null'	=> $field['allow_null']
			);

			// multiple
			if($field['multiple']) {
				$select['multiple']  = 'multiple';
				$select['size']      = 5;
				$select['name']     .= '[]';
			}

			// special atts
			if(!empty($field['readonly'])) $select['readonly'] = 'readonly';
			if(!empty($field['disabled'])) $select['disabled'] = 'disabled';
			if(!empty($field['ajax_action'])) $select['data-ajax_action'] = $field['ajax_action'];

			// hidden input is needed to allow validation to see <select> element with no selected value

			acf_hidden_input(array(
				'id'	=> $field['id'] . '-input',
				'name'	=> $field['name']
			));

			if(!empty($value) && is_array($value)) {
				foreach($value as $id) {
					if(empty($id)) {
						continue;
					}

					$choices[$id] = ($this->if_memberium_tags_exists() ? $this->get_memberium_tag_name($id) : $id);
				}
			}
			// append
			$select['value'] = $value;
			$select['choices'] = $choices;

			// render
			acf_select_input($select);
		}


		function get_ajax_query($options = array()) {
	   		// defaults
	   		$options = acf_parse_args($options, array(
				'post_id'		=> 0,
				's'				=> '',
				'field_key'		=> '',
				'paged'			=> 1
			));

			// Check if table exists
			if(!$this->if_memberium_tags_exists()) {
				return false;
			}

			// vars
	   		$results = array();
	   		$s = null;

	   		// search
			if($options['s'] !== '') {
				// strip slashes (search may be integer)
				$s = strval($options['s']);
				$s = wp_unslash($s);
			}

			$results = $this->get_memberium_tags($s);

			// vars
			$response = array(
				'results'	=> $results
			);

			// return
			return $response;
		}

		/**
		 * Check if memberium tags table exists
		 *
		 * @since 1.0.0
		 */
		function if_memberium_tags_exists() {
			global $wpdb;

			$exists = $wpdb->get_row('SELECT 1 FROM memberium_tags LIMIT 1;');
			if($exists) {
				return true;
			}

			return false;
		}

		/**
		 * Summary.
		 *
		 * @since 0.1.0
		 * @var type $var Description.
		 * @return
		 */
		function get_memberium_tags($search) {
			global $wpdb;

			$query = $wpdb->prepare('SELECT * FROM memberium_tags WHERE id LIKE %s OR name LIKE %s;', "%{$search}%", "%{$search}%");
			$tags = $wpdb->get_results($query);

			$results = [];
			if($tags) {
				foreach($tags as $tag) {
					$text = "{$tag->name} ({$tag->id})";
					$results[] = array(
						'id'	=> $tag->id,
						'text'	=> $text
					);
				}
			}

			return $results;
		}

		function get_memberium_tag($id) {
			global $wpdb;

			$query = $wpdb->prepare('SELECT * FROM memberium_tags WHERE id = %d', $id);
			$tag = $wpdb->get_row($query);
			if($tag) {
				return $tag;
			}

			return false;
		}

		function get_memberium_tag_name($id) {
			$tag = $this->get_memberium_tag($id);
			if($tag) {
				return "{$tag->name} ({$tag->id})";
			}

			return $id;
		}

		function input_admin_enqueue_scripts() {
			// vars
			$url = $this->settings['url'];
			$version = $this->settings['version'];

			// register & include JS
			wp_register_script('acf-memberium-is-tag', "{$url}assets/js/input.js", array('acf-input'), $version);
			wp_enqueue_script('acf-memberium-is-tag');

			parent::input_admin_enqueue_scripts();
		}

		function format_value($value, $post_id, $field) {
			// array
			if(acf_is_array($value)) {
				if($field['return_format'] == 'comma') {
					$return_value = implode(',', $value);
				} else {
					$return_value = [];
					foreach($value as $i => $v) {
						$return_value[] = $v;
					}
				}
			} else {
				$return_value = $value;
			}

			// return
			return $return_value;
		}

	}
}
