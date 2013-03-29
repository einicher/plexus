<?php
	class Widget extends Core
	{
		public $name = 'WIDGET_NAME';
		public $description = 'WIDGET_DESCRIPTION';
		public $version = 0.1;
		public $link = 'WIDGET_LINK';

		public $author = 'AUTHOR_NAME';
		public $authorMail = 'AUTHOR_EMAIL';
		public $authorLink = 'AUTHOR_LINK';
		
		public $data; // your data will be serialized and stored here
		public $dock; // dock name where the widget is added to
		public $page; // id of the page of which add/edit was called

		public $embedable = TRUE;
		public $show = TRUE; // tell dock to show or not

		final function __construct($dock = 'undocked', $page = '', $data = '')
		{
			if (is_string($data)) {
				$data = json_decode($data);
			}
			$this->construct();
			$this->status = $data->status = empty($data->status) ? -55 : $data->status;
			$this->order = $data->order = empty($data->order) ? 0 : $data->order;
			$this->dock = $dock;
			$this->page = $page;
			$this->data = $data;
			if (isset($this->data->excludes) && in_array($this->page, $this->data->excludes)) {
				$this->data->exclude = TRUE;
			}
			$this->init();
		}

		function construct()
		{
		}

		function init()
		{
		}

		function edit()
		{
		}

		function editWidget()
		{
			$bypass = $this->edit();
			if (empty($bypass)) {
				$fields = $this->editFields();
			} else {
				$fields = array();
			}
			$fields['type'] = 'widget';
			$form = new Form($fields, $this->data);
			if (!empty($bypass)) {
				$form->bypass = $bypass;
			}
			return $form;
		}

		function editFields()
		{
			return array();
		}

		function view($type = '') // $type == dock|embedded|template
		{
			return 'WIDGET_VIEW';
		}

		function save($data, $id = '')
		{
			if ($this->beforeSave((object) $data) === false) {
				return FALSE;
			}

			// -55 show widget on every page
			// -66 show widget only on current page
			// -77 show widget on current page and its subpages

			if (isset($data['status'])) {
				if ($data['status'] == -66 && isset($data['exclude'])) {
					$data['status'] = -55;
				}
				if (empty($this->page) && ($data['status'] == -66 || $data['status'] == -77)) {
					$data['status'] = -55;
				}
	
				if ($data['status'] == -66 || $data['status'] == -77) {
					if (!is_array(@$data['includes']) || !in_array($this->page, $data['includes'])) {
						$data['includes'][] = $this->page;
					}
				}

				if ($data['status'] == -55 && isset($this->data->includes)) {
					unset($this->data->includes);
				}
			}

			unset($this->data->exclude);
			if (isset($data['exclude'])) {
				if (empty($this->data->excludes)) {
					$data['excludes'] = array();
				} else {
					$data['excludes'] = $this->data->excludes;
				}
				if (!in_array($this->page, $data['excludes'])) {
					$data['excludes'][] = $this->page;
				}
				unset($data['exclude']);
			}

			if (empty($data['exclude'])) {
				if (isset($this->data->excludes)) {
					$key = array_search($this->page, $this->data->excludes);
					if ($key !== FALSE) {
						unset($this->data->excludes[$key]);
					}
					if (empty($this->data->excludes)) {
						unset($this->data->excludes);
					}
				}
			}

			$fields = $this->editWidget()->fields;
			$this->data->widget = get_class($this);
			foreach ($data as $name => $value) {
				$isFile = FALSE;
				foreach ($fields as $field) {
					if ($field['name'] == $name && $field['type'] == 'file') {
						$targetDir = $this->getStorage($field['options']['target']).'/';
						if (!file_exists($targetDir)) {
							if (@mkdir($targetDir, 0777)) {
								@chmod($targetDir, 0777);
							} else {
								exit('No permissions to create '.$targetDir);
							}
						}
						if (isset($_FILES[$field['name']])) {
							if (empty($_FILES[$field['name']]['error'])) {
								$filename = $id.'-'.Address::transform($_FILES[$field['name']]['name']);
								move_uploaded_file($_FILES[$field['name']]['tmp_name'], $targetDir.$filename);
								@chmod($targetDir.$filename, 0777);
							}
						}

						if (!empty($data[$name.'URL'])) {
							$filename = explode('/', $data[$name.'URL']);
							$filename = array_pop($filename);
							$filename = $id.'-'.Address::transform($filename);
							copy($data[$name.'URL'], $targetDir.$filename);
							@chmod($targetDir.$filename, 0777);
						}

						/*if (!empty($data[$name]) && $data[$name] != $filename) {
							@unlink($targetDir.$data[$name]);
						}*/
						if (!empty($filename)) {
							$this->data->$name = $filename;
							$isFile = TRUE;
						}
						break;
					}
				}
				if (!$isFile) {
					$this->data->$name = $value;
				}
			}

			// kick out empty checkboxes
			foreach ($fields as $field) {
				if ($field['type'] == 'checkbox' && empty($data[$field['name']])) {
					unset($this->data->{$field['name']});
				}
			}

			if (empty($id)) {
				$id = 'widget';
			}

			return $this->setOption($id, json_encode($this->data), $this->dock, TRUE);
		}

		function beforeSave($data)
		{
			return TRUE;
		}

		function remove($id)
		{
			return $this->delOption($id);
		}

		function getTitle()
		{
		}
	}
?>
