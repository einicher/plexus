<?php
	class WidgetControl extends Core
	{

		function plxAddWidget($level, $levels, $cache)
		{
			$this->addr->root = '';
			if (!empty($_GET['ajax'])) {
				$this->addr->root = $_GET['ajax'];
			}
			$name = $this->addr->level(2, $levels);
			$page = $this->addr->level(3, $levels);
			$widget = $this->addr->level(4, $levels);

			$dock = new Dock($name);
			if (isset($_GET['options'])) {
				$dock->options = json_decode(stripslashes(urldecode($_GET['options'])));
			}
			$dock->page = $page;
			ob_start();
			$add = $dock->addWidget($widget);

			if (is_array($add)) {
				if (isset($add['content'])) {
					header('content-type: text/plain; charset=utf-8');
					return json_encode((object) array(
						'content' => ob_get_clean().$add['content']
					));
				} else {
					$class = new stdClass;
					$class->status = 'OK';
					$class->dock = $add['dock'];
					$class->widget = $add['widget'];
					$class->page = $dock->page;
					if (isset($_GET['options'])) {
						$class->options = stripslashes($_GET['options']);
					}
					header('content-type: text/plain; charset=utf-8');
					return json_encode($class);
				}
			} else {
				return ob_get_clean().$add;
			}
		}

		function plxEditWidget($level, $levels, $cache)
		{
			ob_start();
			$this->addr->root = '';
			$id = $this->addr->level(2, $levels);
			$fetch = Core::getOption($id);
			$widget = json_decode($fetch->value);
			$dock = new Dock($fetch->association);
			if (isset($_GET['options'])) {
				$dock->options = json_decode(stripslashes(urldecode($_GET['options'])));
			}
			$dock->page = $this->addr->level(3, $levels);
			$edit = $dock->editWidget($widget, $id);
			if (is_array($edit)) {
				$output = ob_get_clean();
				header('content-type: text/plain; charset=utf-8');
				if (isset($edit['content'])) {
					return json_encode((object) array(
						'content' => $output.$edit['content']
					));
				} else {
					$class = new stdClass;
					$class->status = 'OK';
					$class->dock = $edit['dock'];
					$class->page = $dock->page;
					$class->widget = $edit['widget'];
					if (isset($_GET['options'])) {
						$class->options = stripslashes($_GET['options']);
					}
					return json_encode($class);
				}
			} else {
				return $edit;
			}
		}

		function plxStandaloneWidget($level, $levels, $cache)
		{
			ob_start();
			$this->addr->root = '';
			$name = $this->addr->level(2, $levels);
			$page = $this->addr->level(3, $levels);
			$fake = new stdClass;
			$fake->id = $page;
			Control::$current[] = $fake;
			$widget = $this->addr->level(4, $levels);
			$fetch = $this->getOption('widget', $name);
			if (empty($fetch)) {
				$data->widget = $widget;
			} else {
				$data = json_decode($fetch->value);
			}
			$dock = new Dock($name, $page);
			$options = '';
			if (isset($_GET['options'])) {
				$options = (array) json_decode(stripslashes(urldecode($_GET['options'])));
			}
			$edit = $dock->editWidget($data, @$fetch->id);
			if (is_array($edit)) {
				$output = ob_get_clean();
				header('content-type: text/plain; charset=utf-8');
				if (isset($edit['content'])) {
					return json_encode((object) array(
						'content' => $output.$edit['content']
					));
				} else {
					$class = new stdClass;
					$class->status = 'OKS';
					$class->dock = $name;
					$class->content = $output.$this->getWidget(get_class($dock->widget), $name, $options);
					$class->options = stripslashes($_GET['options']);
					header('content-type: text/plain; charset=utf-8');
					return json_encode($class);
				}
			} else {
				return $edit;
			}
		}
	}
?>