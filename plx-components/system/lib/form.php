<?php
	class Form extends Core
	{
		public $type;
		public $attributes;
		public $fields;
		public $values;
		public $action;
		public $bypass;

		function __construct(array $fields, $values = '')
		{
			if (isset($fields['type'])) {
				$this->type = $fields['type'];
				unset($fields['type']);
			}
			if ($this->type == 'preferences') {
				$this->attributes['class'][] = 'plexusPreferencesForm'; 
			} elseif ($this->type == 'widget') {
				$this->attributes['class'][] = 'plexusWidgetForm';
				if (!isset($_GET['embed'])) {
					$fields[] = array(
						'type' => 'widget',
						'name' => 'status',
						'required' => TRUE
					);
					$fields[] = array(
						'type' => 'checkbox',
						'name' => 'exclude',
						'required' => FALSE,
						'options' => array(
							'label' => §('Disable Widget on this page')
						)
					);
					$fields[] = array(
						'type' => 'string',
						'name' => 'order',
						'required' => TRUE,
						'options' => array(
							'label' => §('Order')
						)
					);
				}
			} elseif (!isset($fields['advancedOff']) && $this->access->granted('system.edit.advanced')) {
				$fields[] = array(
					'type' => 'number',
					'name' => 'parent',
					'required' => TRUE,
					'options' => array(
						'label' => §('Parent'),
						'caption' => §('Change this to the id of another object to move it hierarchical below.'),
						'advanced' => TRUE
					)
				);
				$fields[] = array(
					'type' => 'string',
					'name' => 'type',
					'required' => TRUE,
					'options' => array(
						'label' => §('Type'),
						'caption' => §('Changing this may cause loss of data.'),
						'advanced' => TRUE
					)
				);
				$fields[] = array(
					'type' => 'string',
					'name' => 'address',
					'required' => FALSE,
					'options' => array(
						'label' => §('Address'),
						'advanced' => TRUE
					)
				);
				$fields[] = array(
					'type' => 'string',
					'name' => 'language',
					'required' => FALSE,
					'options' => array(
						'label' => §('Language'),
						'advanced' => TRUE
					)
				);
				$fields[] = array(
					'type' => 'number',
					'name' => 'translation',
					'required' => FALSE,
					'options' => array(
						'label' => §('Translation of'),
						'advanced' => TRUE
					)
				);
				$fields[] = array(
					'type' => 'custom',
					'name' => 'trackbacks',
					'required' => FALSE,
					'options' => array(
						'label' => §('Trackbacks'),
						'advanced' => true,
						'call' => 'trackbacks',
						'actor' => &$this 
					)
				);
			}
			$this->fields = $fields;
			$this->values = (object) $values;
			return $this;
		}

		function __toString()
		{
			return $this->get();
		}

		function get()
		{
			$form = '';
			$attributes = '';
			$advanced = '';
			$checks = '';

			if (!empty($this->bypass)) {
				$form .= $this->bypass;
			}

			// disable checkboxes above the save button
			$checksOff = FALSE;
			if (!empty($this->fields['checksOff'])) {
				$checksOff = TRUE;
			}
			unset($this->fields['checksOff']);

			if (isset($this->attributes)) {
				$attributes = $this->attributes;
			}
			$attributes['class'][] = 'plexusForm';
			if (isset($_GET['ajax'])) {
				$attributes['class'][] = 'plexusFormAjax';
			}

			if (empty($this->action)) {
				$action = $this->a->current();
			} else {
				$action = $this->action;
			}

			$advancedOff = FALSE;
			if (!empty($this->fields['advancedOff'])) {
				$advancedOff = TRUE;
			}
			unset($this->fields['advancedOff']);

			$saveButtonLabel = §('Save');
			if (!empty($this->fields['saveButtonLabel'])) {
				$saveButtonLabel = $this->fields['saveButtonLabel'];
			}
			unset($this->fields['saveButtonLabel']);


			$this->fields = $this->o->notify('system.form.addField', $this->fields);
//echo µ($this->values);
			foreach ($this->fields as $field) {
				if (isset($field['required']) && $field['required'] === -1 || (!empty($field['hide']))) {
					continue;
				}
				$field = (object) $field;
				if (isset($this->values->{$field->name})) {
					$field->value = $this->values->{$field->name};
				} else {
					$field->value = '';
				}
				$field->options = (object) @$field->options;
				switch ($field->type) {
					case 'string':
					break;
					
					case 'password':
						$field->type = 'string';
						$field->password = TRUE;
					break;

					case 'number':
						$field->type = 'string';
					break;

					case 'captcha':
						if (!empty($field->value) && isset($_SESSION['captcha'][$field->name]->hold)) {
							@$field->captcha->string = $_SESSION['captcha'][$field->name]->string; 
						} else {
							@$field->captcha->string = substr(sha1(md5(time())), 5, 5);
							$_SESSION['captcha'][$field->name] = $field->captcha;
						}
					break;

					case 'wysiwyg':
						$field->value = $this->tools->detectStoragePaths($field->value);
						$field->value = $this->tools->detectProblems($field->value);
						if (empty($field->options->cols)) {
							$field->options->cols = '50';
						}
						if (empty($field->options->rows)) {
							$field->options->rows = '15';
						}
						$field->id = 'wysiwyg-'.$field->name.'-'.time();
					break;

					case 'text':
						if (empty($field->options->cols)) {
							$field->options->cols = '50';
						}
						if (empty($field->options->rows)) {
							$field->options->rows = '7';
						}
					break;

					case 'date':
						$field->format = §('Y-m-d');
						$field->datetype = 'date';
						$field->datetype = 'string';
						$this->t->cut('form.php', 'datepicker', array('field' => $field));
					break;

					case 'time':
						$field->format = §('H:i');
						$field->type = 'date';
						$field->datetype = 'time';
						$field->datetype = 'string';
					break;

					case 'datetime':
						$field->format = §('Y-m-d H:i');
						$field->type = 'date';
						$field->datetype = 'datetime';
						$field->datetype = 'string';
					break;
					
					case 'file':
						$attributes['enctype'] = 'multipart/form-data';
						$ext = explode('.', $field->value);
						$ext = array_pop($ext);
						$field->isImage = false;
						if (in_array($ext, array('jpg', 'jpeg', 'pjpeg', 'png', 'gif'))) {
							$field->originalSrc = $this->getStorage($field->options->target).'/'.$field->value;
							$field->enlargedSrc = $this->imageScaleLink($field->originalSrc, $this->getOption('content.fullsize'));
							$field->src = $this->imageScaleLink($field->originalSrc, $this->getOption('content.width'));
							$field->isImage = true;
							$field->image = $field;
						}
						if (!empty($field->value)) {
							$field->id = $this->values->id;
						}
					break;

					case 'select':
						if (is_string($field->options->values)) {
							if (isset($field->options->call) && !isset($field->options->actor)) {
								eval('$field->options->values = '.$field->options->call.';');
							} elseif (!isset($field->options->actor)) {
								eval('$field->options->values = '.$values.';');
							} else {
								$field->options->values = $field->options->actor->{$field->options->values}();
							}
						}
						$options = array();
						foreach ($field->options->values as $option => $value) {
							$options[] = (object) array(
								'value' => $option,
								'label' => $value
							);
						}
						$field->options->values = $options;
					break;

					case 'radio':
					case 'status':
					case 'widget':
						if ($field->type == 'status') {
							$field->options->values = array(
								'0' => §('Draft'),
								'1' => §('Published'),
								'2' => §('Published hidden')
							);
							$field->type = 'radio';
						}
						if ($field->type == 'widget') {
							$field->options->values = array(
								'-55' => §('Show widget on every page'),
								'-66' => §('Only show widget on this page'),
								'-77' => §('Show widget on this page, and all its subpages')
							);
							$field->name = 'status';
							$field->type = 'radio';
						}

						$count = 0;
						foreach ($field->options->values as $option => $value) {
							$count++;
							$option = (object) array(
								'value' => $option,
								'label' => $value,
								'count' => $count
							);
							$field->options->options[] = $option;
						}
					break;

					case 'checkbox':
					break;

					case 'custom':
						if (isset($field->options->actor) && isset($field->options->call)) {
							$custom = $field->options->actor->{$field->options->call}($action, $attributes, $field, $this->fields);

							if (!empty($field->options->checks)) {
								$checks .= $custom;
							} elseif (!empty($field->options->advanced)) {
								$advanced .= $custom;
							} else {
								$form .= $custom;
							}

							continue;
						}
					break;
				}

				if (isset($field->options->suggest)) {
					$sug = '<div class="suggestions"><strong>'.§('Suggestions').':</strong> ';
					if (is_string($field->options->suggest)) {
						eval('$field->options->suggest = '.$field->options->suggest.';');
					}
					if (is_object($field->options->suggest)) {
						eval('$field->options->suggest = $field->options->suggest->'.$field->options->suggestCall.'();');
					}
					foreach ($field->options->suggest as $name => $count) {
						$sug .= '<span class="link suggest" rel="'.$field->name.'">'.$name.'</span> ';
					}
					ob_start();
?>
<script type="text/javascript">
	jQuery('#<?=$field->type.ucfirst($field->name)?> .suggest').click(function() {
		var term = jQuery(this).html();
		if (jQuery('#<?=$field->name?>').val() == '') {
			jQuery('#<?=$field->name?>').val(term);
		} else {
			terms = jQuery('#<?=$field->name?>').val();
			terms = terms.split(',');
	        for (var i=0; i<terms.length; i++) {
	        	var	str = jQuery.trim(terms[i]);
	            if (term.toUpperCase() == str.toUpperCase()) {
	                return;
	            }
	        }

	        terms[terms.length] = term;

	        var value = '';
	        for (var j=0; j<terms.length; j++) {
	            if (terms[j] != '') {
	                value += ', ' + jQuery.trim(terms[j]);
	            }
	        }

			jQuery('#<?=$field->name?>').val(value.substr(2));
		}
	});
</script>
</div>
<?php
					$sug .= ob_get_clean();
				}

				if (isset($field->options->label)) {
					if ($field->required) {
						$field->options->label .= '*';
					}
				}

				if ($field->type != 'custom') {
					if (!empty($field->options->checks)) {
						$checks .= Template::instance()->get('form-'.$field->type.'.php', array(
							'field' => $field,
							'caption' => @$field->options->caption
						));
					} elseif (!empty($field->options->advanced)) {
						$advanced .= Template::instance()->get('form-'.$field->type.'.php', array(
							'field' => $field,
							'caption' => @$field->options->caption
						));
					} else {
						$form .= Template::instance()->get('form-'.$field->type.'.php', array(
							'field' => $field,
							'caption' => @$field->options->caption
						));
					}
				}
			}

			if (!empty($attributes)) {
				if (!empty($attributes['class'])) {
					$attributes['class'] = implode(' ', $attributes['class']);
				}
				
				$collect = '';
				foreach ($attributes as $name => $value) {
					$collect .= ' '.$name.'="'.htmlspecialchars($value).'"';
				}
				$attributes = $collect;
			}

			$showRemoveButton = false;
			if (!empty($this->values->id) && $this->access->granted('system.delete')) {
				$showRemoveButton = true;
			}
			$showFormAdvancedControls = false;
			if (!empty($advanced) && !$advancedOff && $this->access->granted('system.edit.advanced')) {
				$showFormAdvancedControls = false;
			}
			$showChecks = false;
			if (!empty($checks) && !$checksOff) {
				$showChecks = true;
			}

			return Template::instance()->get('form.php', array(
				'form' => $form,
				'attributes' => $attributes,
				'action' => $action,
				'saveButtonLabel' => $saveButtonLabel,
				'showRemoveButton' => $showRemoveButton,
				'showFormAdvancedControls' => $showFormAdvancedControls,
				'advanced' => $advanced,
				'showChecks' => $showChecks,
				'checks' => $checks
			));
		}

		function number2string($n, $l = 0)
		{
			$numbers = array('Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelfe', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
			if ($l) {
				return strtolower(§($numbers[$n]));
			} else {
				return §($numbers[$n]);
			}
		}

		function trackbacks($action, $attributes, $field, $fields)
		{
			$trackbacks = array();
			if (!empty($field->value)) {
				if (is_string($field->value)) {
					$trackbacks = json_decode($field->value);
				} else {
					$trackbacks = $field->value;
				}
			}

			ob_start();
?>
	<div class="formField formFieldString" id="stringTrackback">
		<label class="formFieldStringLabel"><?=§('Add/Edit Trackbacks')?></label>

<? if (isset($trackbacks->link)) : foreach (@$trackbacks->link as $key => $trackback) : ?>
		<div class="trackbackLink">
			<?=$trackback?> <span onclick="jQuery(this).parent().remove();">[x]</span>
			<input type="hidden" name="trackbacks[link][]" value="<?=$trackback?>" />
			<input type="hidden" name="trackbacks[status][]" value="<?=$trackbacks->status[$key]?>" />
			<div class="clear"></div>
		</div>
<? endforeach; endif; ?>

		<div id="trackbackInputs">
			<div class="trackbackInput">
				<div class="fieldStringWrap">
					<input type="text" class="fieldString" name="trackbacks[link][]" />
					<input type="hidden" name="trackbacks[status][]" value="0" />
				</div>
				<button type="button" onclick="jQuery(this).parent().remove();">[x]</button>
				<div class="clear"></div>
			</div>
		</div>

<div id="blankTrackbackInputField" style="display: none;">
			<div class="trackbackInput">
				<div class="fieldStringWrap">
					<input type="text" class="fieldString" name="trackbacks[link][]" />
					<input type="hidden" name="trackbacks[status][]" value="0" />
				</div>
				<button type="button" onclick="jQuery(this).parent().remove();">[x]</button>
				<div class="clear"></div>
			</div>
</div>

		<button type="button" onclick="jQuery('#trackbackInputs').append(jQuery('#blankTrackbackInputField').html())"><?=§('+ Add Field')?></button>
	</div>
<?php
			return ob_get_clean();
		}
	}
?>
