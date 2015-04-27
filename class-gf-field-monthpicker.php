<?php
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

class GF_Field_Monthpicker extends GF_Field {
	public static $monthMapFull = array(
		'January', 'February', 'March', 'April',
		'May', 'June', 'July', 'August',
		'September', 'October', 'November', 'December'
	);

	public $type = 'monthpicker';

	public function get_form_editor_field_title() {
		return __( 'Date (Monthpicker)', GF_MONTHPICKER);
	}

	function get_form_editor_field_settings() {
		return array(
			'conditional_logic_field_setting',
			'prepopulate_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'rules_setting',
			'visibility_setting',
			'duplicate_setting',
			'datemmyy_format_setting',
			'default_value_setting',
			'placeholder_setting',
			'description_setting',
			'css_class_setting',
			'size_setting'
		);
	}

	function is_conditional_logic_supported() {
		return true;
	}

	public function parse_date( $values, $format ) {
		$date_info = array();

		if ( is_array( $values ) ) {
			$date_info['month'] = rgar( $values, 0 );
			$date_info['year']  = rgar( $values, 1 );
			return $date_info;
		}

		switch ($format) {
			case 'fmoy':
				if (preg_match('/^([A-Za-z0-9]+)[- . \/]+(\d{4})$/', $values, $matches)) {
					$date_info['month'] = array_flip(self::$monthMapFull)[(int)$matches[1]] + 1;
					$date_info['year']  = $matches[2];
				} else if (preg_match('/^(\d{4})[- . \/]+(\d{1,2})$/', $values, $matches)) {
					$date_info['month'] = $matches[2];
					$date_info['year']  = $matches[1];
				}
				break;
			default:
				if ( preg_match( '/^([A-Za-z0-9]+)[- . \/]+(\d{4})$/', $values, $matches ) ) {
					$date_info['month'] = $matches[1];
					$date_info['year']  = $matches[2];
				}
				break;
		}

		return $date_info;
	}

	public function date_display($date, $format) {
		$date = $this->parse_date( $date, $format );
		if ( empty( $date ) ) {
			return $value;
		}

		switch ( $format ) {
			case 'fmoy' :
				return sprintf('%s %d', self::$monthMapFull[(int)$date['month']-1], $date['year']);
			default :
				return sprintf('%02d/%d', $date['month'], $date['year']);
		}
	}

	public function validate( $value, $form ) {
		if ( is_array( $value ) && rgempty( 0, $value ) && rgempty( 1, $value ) && rgempty( 2, $value ) ) {
			$value = null;
		}

		if ( ! empty( $value ) ) {
			$format = empty( $this->dateFormat ) ? 'moy' : $this->dateFormat;
			$date   = $this->parse_date( $value, $format );

			if ( empty( $date ) || ! $this->checkdate( $date['month'], $date['year'] ) ) {
				$this->failed_validation = true;
				$format_name             = '';
				switch ( $format ) {
					case 'fmoy' :
						$format_name = 'MM yyyy';
						break;
					default:
						$format_name = 'mm/yyyy';
						break;
				}
				$message                  = $this->dateType == sprintf( __( 'Please enter a valid date in the format (%s).', 'gfa-mmyy' ), $format_name );
				$this->validation_message = empty( $this->errorMessage ) ? $message : $this->errorMessage;
			}
		}
	}

	public function is_value_submission_empty( $form_id ){
		$value = rgpost( 'input_' . $this->id );
		if ( is_array( $value ) ) {
			// Date field and date drop-downs
			foreach ( $value as $input ) {
				if ( strlen( trim( $input ) ) <= 0 ) {
					return true;
				}
			}

			return false;
		} else {

			// Date picker
			return strlen( trim( $value ) ) <= 0;
		}
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		$picker_value = '';
		if (is_array($value)) $value = array_values( $value );
		$picker_value = $this->date_display($value, $this->dateFormat);

		$format    = empty( $this->dateFormat ) ? 'moy' : esc_attr( $this->dateFormat );

		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$form_id  = $form['id'];
		$id       = intval( $this->id );
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$logic_event = ! $is_form_editor && ! $is_entry_detail ? $this->get_conditional_logic_event( 'keyup' ) : '';
		$disabled_text = $is_form_editor ? "disabled='disabled'" : '';
		$class_suffix  = $is_entry_detail ? '_admin' : '';
		$class         = $this->size . $class_suffix;

		$date_picker_placeholder = $this->get_field_placeholder_attribute();

		if ( $is_form_editor ) {
			$class        = esc_attr( $class );
			return "<div class='ginput_container' id='gfield_input_datepicker_monthpicker' >".
				"<input name='ginput_datepicker_monthpicker' type='text' class='{$class}' {$date_picker_placeholder} {$disabled_text} {$logic_event} value='{$picker_value}'/>".
				"</div>";
		} else {

			$date_type = $this->dateType;
			$picker_value = esc_attr( $picker_value );
			$tabindex     = $this->get_tabindex();
			$class        = esc_attr( $class );
			$type         = $this->dateFormat === 'fmoy' ?  'month' : 'text';

			return "<div class='ginput_container'>".
				"<input name='input_{$id}' id='{$field_id}' type='{$type}' value='{$picker_value}' class='gfmonthpicker {$class} {$format}' {$tabindex} {$disabled_text} {$logic_event} {$date_picker_placeholder}/>".
				"</div>";
		}
	}

	public function checkdate( $month, $year ) {
		if ( empty( $month ) || ! is_numeric( $month ) || empty( $year ) || ! is_numeric( $year ) || strlen( $year ) != 4 ) {
			return false;
		}

		return checkdate( $month, 1, $year );
	}

	public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {
		return $this->date_display( $value, $this->dateFormat );
	}


	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {

		return $this->date_display( $value, $this->dateFormat );
	}

	public function get_value_merge_tag( $value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format ) {
		$format_modifier = empty( $modifier ) ? $this->dateFormat : $modifier;

		return $this->date_display( $value, $format_modifier );
	}

	public function get_value_save_entry( $value, $form, $input_name, $lead_id, $lead ) {
		$format    = empty( $this->dateFormat ) ? 'moy' : $this->dateFormat;
		$date_info = $this->parse_date( $value, $format );
		if ( ! empty( $date_info ) && ! GFCommon::is_empty_array( $date_info ) ) {
			$value = $this->date_display( $value, $format );
		} else {
			$value = '';
		}
		return $value;
	}

	public function get_entry_inputs() {
		return null;
	}

	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title()
		);
	}
}