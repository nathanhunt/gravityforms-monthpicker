<?php
/*
Plugin Name: Gravity Forms Addon - Monthpicker
Plugin URI: http://dresser.io
Description: A Gravity Forms Addon that provides Month/Year only datepicker
Version: 0.1
Author: dressler-llc
Author URI: http://dressler.io
Text Domain: gfa-monthpicker
Domain Path: /languages
*/
/*
Copyright (c) 2015 Dressler LLC, New York

This file is part of Gravity Forms Monthpicker

Gravity Forms Monthpicker is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Gravity Forms Monthpicker is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Gravity Forms Monthpicker. If not, see <http://www.gnu.org/licenses/>.
*/
define('GF_MONTHPICKER', 'dfa-monthpicker');

add_action('plugins_loaded', 'gfmonthpicker_register_field_type');

function gfmonthpicker_register_field_type() {
	if (!class_exists('GF_Fields')) return;
	include dirname(__FILE__).'/class-gf-field-monthpicker.php';
	GF_Fields::register(new GF_Field_Monthpicker());
}

add_action('gform_field_standard_settings', 'gfmonthpicker_add_additional_setting');

function gfmonthpicker_add_additional_setting($pos) {
	if ($pos != 1200) return;
	?>
	<li class="datemonthpicker_format_setting field_setting">
		<label for="field_datemonthpicker_format">
			<?php _e( 'Date Format', 'gravityforms' ); ?>
		</label>
		<select id="field_datemonthpicker_format" onchange="SetDateFormat(jQuery(this).val());">
			<option value="moy">mm/yyyy</option>
			<option value="fmoy">MM yyyy</option>
		</select>
	</li>
	<script>
	(function ($) {
		$(document).bind('gform_load_field_settings', function (e, field, form) {
			if (field['type'] == 'mmyy') {
				$('#field_datemonthpicker_format').val(field['dateFormat'] == "" ? "moy" : field['dateFormat']);
			}
		});
	})(jQuery);
	</script>
	<?php
}

add_action('gform_enqueue_scripts', 'gfmonthpicker_enqueue_scripts');

function gfmonthpicker_enqueue_scripts($form, $ajax = false) {
	if (!gfmonthpicker_has_datepicker_field($form)) return;

	wp_enqueue_style('jquery-ui-datepicker', plugins_url('jquery-ui.datepicker.min.css', __FILE__), null, '0.1');
	wp_enqueue_style('gform_monthpicker_css', plugins_url('monthpicker.css', __FILE__), null, '0.1');
	wp_enqueue_script('gform_monthpicker_init', plugins_url('monthpicker.min.js', __FILE__), array( 'jquery', 'jquery-ui-button', 'jquery-ui-datepicker', 'gform_gravityforms' ), '0.1', true);
}

function gfmonthpicker_has_datepicker_field($form) {
	if ( is_array($form['fields']) ) {
		foreach ( $form['fields'] as $field )
			if (RGFormsModel::get_input_type( $field ) == 'monthpicker') return true;
	}

	return false;
}