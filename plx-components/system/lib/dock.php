<?php
	class Dock extends Core
	{
		public $name;
		public $page; //current object id
		public $addNew = TRUE;
		public $addPositionFirst = TRUE; // on true create new widget is the first, on false the last widget

		function __construct($name = 'default', $page = -1)
		{
			$this->name = $name;
			$this->page = $page;
		}

		function addWidget($widget = '')
		{
			if ($this->access->granted('system.edit.docks')) {
				if (empty($widget)) {
					return $this->chooseWidget();
				} else {
					require_once self::$widgets[$widget]['file'];
					$widget = new $widget($this->name, $this->page);
	
					if (!empty($_POST['plexusForm'])) {
						unset($_POST['plexusForm']);
						$id = $widget->save($_POST);
						if ($id) {
							return array(
								'dock' => $this->name, 
								'widget' => $id
							);
						} else {
							return array(
								'content' => (string) $widget->editWidget()
							);
						}
					}
	
					return $widget->editWidget();
				}
			} else {
				return $this->lang->get('You do not have the necessary rights to add a new widget.');
			}
		}

		function editWidget($data, $id)
		{
			if ($this->access->granted('system.edit.docks')) {
				require_once self::$widgets[$data->widget]['file'];
				$this->widget = $widget = new $data->widget($this->name, $this->page, $data);

				if (!empty($_POST['plexusForm'])) {
					if (isset($_GET['plexusRemove'])) {
						$widget->remove($id);
						return array(
							'status' => 'OK',
							'dock' => $this->name,
							'widget' => $id
						);
					}
					unset($_POST['plexusForm']);
					if ($widget->save($_POST, $id)) {
						return array(
							'status' => 'OK',
							'dock' => $this->name,
							'widget' => $id
						);
					} else {
						return array(
							'content' => (string) $widget->editWidget()
						);
					}
				}

				if (!empty($id)) {
					$this->tpl->cut('form.php', 'remove');
				}

				return $widget->editWidget();
			} else {
				return $this->lang->get('You do not have the necessary rights to edit widgets.');
			}
		}

		function chooseWidget($embedded = FALSE, $editor = '')
		{
			if ($this->access->granted('system.edit.docks')) {
				$widgets = array();
				foreach (Core::$widgets as $widget) {
					$widget = (object) $widget;
					require_once $widget->file;
					$class = $widget->class;
					$widget = new $class;
					if ($embedded && !$widget->embedable) {
						continue;
					}
					$widget->editor = '';
					$widget->class = $class;
					$widget->href = $this->addr->getRoot('PlexusAddWidget/'.$this->name.'/'.$this->page.'/'.$class);
					if (!empty($this->options)) {
						$widget->href .= '?options='.urlencode(json_encode($this->options)).'&ajax='.urlencode($this->addr->getRoot());
					}
					if ($embedded) {
						$widget->href .= '?embed='.$editor;
						$widget->editor = $editor;
					}
					$widgets[] = $widget;
				}
				return Template::get2('dock-widget-chooser.php', array('widgets' => $widgets));
			} else {
				return $this->lang->get('You do not have the necessary rights to add or edit widgets.');
			}
		}

		function view($forceEnabledEdit = false)
		{
			$this->debug('Dock::view '.$this->name.' START');

			if (!empty($this->exclude)) {
				$e = explode(',', $this->exclude);
				if (in_array($this->page, $e)) {
					return;
				}
			}

			$addWidget = '';
			if ($this->addNew) {
				$this->addWidget = $this->addr->getRoot('PlexusAddWidget/'.$this->name.'/'.$this->page);
				if (!empty($this->options)) {
					$this->addWidget .= '?options='.urlencode(json_encode($this->options)).'&ajax='.urlencode($this->addr->getRoot());
				}
				if ($this->access->granted('system.edit.docks')) {
					$addWidget = Template::get2('dock-add-widget.php', array(
						'dock' => $this,
						'forceEnabledEdit' => $forceEnabledEdit
					));
				}
			}
			$widgets = array();
			$orders = array();
			$sql = 'SELECT * FROM '.$this->db->table('options').' WHERE name="widget" AND association="'.$this->name.'"';
			while ($fetch = $this->db->fetch($sql, 1)) {
				//dirty workaround for damaged widget db records
				if (strpos($fetch->value, '\\\\"') !== FALSE) {
					$fetch->value = str_replace('\\\\"', '\\"', $fetch->value);
				}
				$widget = json_decode($fetch->value);
				$widget->id = $fetch->id;
				if (!is_object($widget)) {
					continue;
				}
				if (
					($widget->status == -66 && !empty($this->page) && !in_array($this->page, $widget->includes))
				 || ($widget->status == -77 && !empty($this->page) && !$this->addr->isSubPageOf($widget->includes, $this->page))
				 || (($widget->status == -77 || $widget->status == -66) && empty($this->page))
				 || (/*!$this->access->granted() && */isset($widget->excludes) && in_array($this->page, $widget->excludes))
				) {
					continue;
				}
				if (empty(self::$widgets[$widget->widget])) {
					continue;
				}
				require_once self::$widgets[$widget->widget]['file'];
				$widget = new $widget->widget($this->name, $this->page, $widget);
				$widget->dock =& $this;
				$widget->id = $fetch->id;
				if (!$widget->show) {
					continue;
				}
				$widget->id = $fetch->id;
				$widget->href = $this->addr->getRoot('PlexusEditWidget/'.$widget->id.'/'.$this->page);
				if (!empty($this->options)) {
					$widget->href .= '?options='.urlencode(json_encode($this->options)).'&ajax='.urlencode($this->addr->getRoot());
				} else {
					$widget->href .= '?ajax='.urlencode($this->addr->getRoot());
				}
				if ($this->access->granted('system.edit')) {
					$widget->editWidget = 1;
				}
				$widget->view = $widget->view('dock');
				if (empty($widget->view)) {
					continue;
				}
				$orders[$fetch->id] = $widget->order;
				$widgets[$fetch->id] = $widget;
			}

			asort($orders, SORT_NUMERIC);

			$collect = array();
			foreach ($orders as $id => $order) {
				$collect[] = $widgets[$id];
			}

			$dock = Template::get2('dock.php', array(
				'dock' => $this,
				'widgets' => $collect,
				'addWidget' => $addWidget,
				'addWidgetPosition' => empty($this->addPositionFirst) ? 0 : 1,
				'forceEnabledEdit' => $forceEnabledEdit
			));
			$this->debug('Dock::view '.$this->name.' READY');
			return $dock;	
		}
	}
?>
