<?php

class Form {
	public $form_options = [];
	public $form_values = [];

	private $fields = [];
	private $reserved_options = ['name', 'label', 'type', 'items', 'value', 'class', 'multiple', 'checked'];

	public function fields() {
		if (is_array($this->form_options) === false || count($this->form_options) < 1)
			return false;

		foreach ($this->form_options as $name => $option) {
			if (is_string($option)) {
				$options[str_repeat(' ', $name)] = $option;
				continue;
			}

			if (isset($this->form_values[$name]) && empty($this->form_values[$name]) === false) {
				$option_value = $this->form_values[$name];

				if (is_string($option_value))
					$option_value = htmlentities($option_value);
			} else
				$option_value = '';

			$attributes = '';
			foreach($option as $key => $value)
				if (in_array($key, $this->reserved_options) === false)
					$attributes .= ' ' . $key . '="' . $value . '"';

			if (isset($option['type']) === false)
				$option['type'] = null;
			
			switch ($option['type']) {
				default:
					$options[$option['label']] = '';
					break;

				case 'text':
					$options[$option['label']] = '<input name="' . $name . '" type="text" value="' . $option_value . '" class="form-control"' . $attributes . '>';
					break;

				case 'password':
					$options[$option['label']] = '<input name="' . $name . '" type="password" value="" class="form-control"' . $attributes . '>';
					break;

				case 'combo':
					$options[$option['label']] = '<select name="' . $name . '" class="form-control"' . $attributes . '>';

					if (isset($option['items']) && is_array($option['items']))
						foreach ($option['items'] as $value => $label)
							$options[$option['label']] .= '<option value="' . $value . '"' . ($value == $option_value ? ' selected' : null) . '>' . $label . '</option>';

					$options[$option['label']] .= '</select>';
					break;

				case 'textarea':
					$options[$option['label']] = '<textarea name="' . $name . '" class="form-control"' . $attributes . '>' . $option_value . '</textarea>';
					break;

				case 'checkbox':
					if (isset($option['items']) && is_array($option['items'])) {
						$options[$option['label']] = '';

						if (is_string($option_value))
							$option_value = explode(',', $option_value);

						foreach ($option['items'] as $value => $label)
							$options[$option['label']] .= '<label class="form-checkbox"><input name="' . $name . '[]" type="checkbox" value="' . $value . '"' . (is_array($option_value) && in_array($value, $option_value) ? ' checked' : null) . $attributes . '> ' . $label . '</label>';
					}
					break;

				case 'radio':
					if (isset($option['items']) && is_array($option['items'])) {
						$options[$option['label']] = '';

						foreach ($option['items'] as $value => $label)
							$options[$option['label']] .= '<label class="form-radio"><input name="' . $name . '" type="radio" value="' . $value . '"' . ($value == $option_value ? ' checked' : null) . $attributes . '> ' . $label . '</label>';
					}
					break;

				case 'color':
					$options[$option['label']] = '<input name="' . $name . '" type="color" value="' . $option_value . '" class="form-control"' . $attributes . '>';
					break;

				case 'number':
					$options[$option['label']] = '<input name="' . $name . '" type="number" value="' . $option_value . '" class="form-control"' . $attributes . '>';
					break;

				case 'optgroup':
					$options[$option['label']] = '<select name="' . $name . '" class="form-control"' . $attributes . '>';

					if (isset($option['items']) && is_array($option['items']))
						foreach ($option['items'] as $group_name => $values) {
							$options[$option['label']] .= ' <optgroup label="' . $group_name . '">';

							if (is_array($values))
								foreach ($values as $value => $label)
									$options[$option['label']] .= '<option value="' . $value . '"' . ($value == $option_value ? ' selected' : null) . '>' . $label . '</option>';
						}

					$options[$option['label']] .= '</select>';
					break;

				case 'list':
					if (is_string($option_value))
						$option_value = explode(',', $option_value);
					
					$options[$option['label']] = '<select multiple name="' . $name . '[]" class="form-control"' . $attributes . '>';

					if (isset($option['items']) && is_array($option['items']))
						foreach ($option['items'] as $value => $label)
							$options[$option['label']] .= '<option value="' . $value . '"' . (is_array($option_value) && in_array($value, $option_value) ? ' selected' : null) . '>' . $label . '</option>';

					$options[$option['label']] .= '</select>';
					break;

				case 'file':
					$options[$option['label']] = '<input name="' . $name . '" type="file" value="' . $option_value . '"' . $attributes . '>';
					break;
			}
		}

		if (isset($options)) {
			$this->fields = $options;

			return $options;
		}
		
		return [];
	}

	public function make($method = 'post', $action = null, $class = [], $append = null, $prepend = null) {
		if (count($this->fields) < 1)
			$this->fields();

		$data = '<form role="form" action="' . $action . '" method="post" class="' . @$class['form'] . '">';

		if (empty($prepend) === false)
			$data .= $prepend;

		$i = 0;
		$field_names = array_keys($this->form_options);

		foreach ($this->fields as $label => $element) {
			if (isset($class['element']))
				$element = '<div class="' . $class['element'] . '">' . $element . '</div>';

			$data .= '<div class="form-group">
				<label for="' . $field_names[$i] . '" class="' . @$class['label'] . '">' . $label . '</label>
				' . $element . '
			</div>';

			$i += 1;
		}

		if (empty($append) === false)
			$data .= $append;

		$data .= '</form>';

		return $data;
	}

	public function button($name, $value, $attributes = []) {
		$data = '<button name="' . $name . '"';

		foreach ($attributes as $attr_name => $attr_value)
			$data .= ' ' . $attr_name . '="' . $attr_value . '"';

		$data .= '>' . $value . '</button>';

		return $data;
	}
}

?>