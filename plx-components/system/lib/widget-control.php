<?php
	class WidgetControl extends Core
	{
		static $instance;
		
		static function instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function getWidget($widget, $name, $options = '')
		{
			if (!isset(self::$widgets[$widget])) {
				return 'WIDGET “'.$widget.'” NOT FOUND.';
			} else {
				$class = $widget;
				$page = @end(Control::$current)->id;
				$fetch = $this->getOption('widget', $name);
				if (empty($fetch)) {
					$data = '';
				} else {
					$data = json_decode($fetch->value);
					$data->id = $fetch->id;
				}
				require_once self::$widgets[$widget]['file'];
				$widget = new $widget($name, $page, $data);
				$widget->id = $name;
				if (is_array($options)) {
					$widget->dock = (object) $options;
				}

				$container = 'div';
				if (!empty($widget->dock->container)) {
					$container = $widget->dock->container;
				}

				$content = $widget->view('template');

				ob_start();
?>
<<?php echo $container; ?> id="<?php echo $name; ?>" class="widget standaloneWidget">
<?php if ($this->access->granted('system.editWidgets') && !empty($page)) : ?>
	<span id="<?php echo $name; ?>StandaloneEdit" class="plexusEdit plexusControls"><?php echo §('Edit'); ?></span>
	<script type="text/javascript">
		jQuery('#<?php echo $name; ?>StandaloneEdit').fancybox({
			href: plxRoot + 'PlexusStandaloneWidget/<?php echo $name; ?>/<?php echo $page; ?>/<?php echo $class; ?>?options=<?php echo urlencode(json_encode($options)); ?>',
			autoDimensions: false,
			centerOnScroll: true,
			overlayOpacity: 0.5,
			overlayColor: '#000',
			transitionIn: 'elastic',
			transitionOut: 'elastic',
			onComplete: function() {
				plxWidgetHtml2AjaxForm(plxRoot + 'PlexusStandaloneWidget/<?php echo $name; ?>/<?php echo $page; ?>/<?php echo $class; ?>?options=<?php echo urlencode(json_encode($options)); ?>');
				jQuery('form.plexusForm button.remove').click(function() {
					var action = jQuery('form.plexusForm').attr('action') + '&plexusRemove';
					jQuery('form.plexusForm').attr('action', action);
				});
			}
		});
	</script>
<?php endif; ?>
<?php if ($widget->getTitle()) : ?>
		<h1><?php echo $widget->getTitle(); ?></h1>
<?php endif; ?>
		<div class="wrap">
<?php echo $content; ?>
		</div>
</<?php echo $container; ?>>
<?php
				return ob_get_clean();
			}
		}

		function addWidget($level, $levels, $cache)
		{
			$this->a->root = '';
			if (!empty($_GET['ajax'])) {
				$this->a->root = $_GET['ajax'];
			}
			$name = $this->a->getLevel(2, $levels);
			$page = $this->a->getLevel(3, $levels);
			$widget = $this->a->getLevel(4, $levels);

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

		function editWidget($level, $levels, $cache)
		{
			ob_start();
			$this->a->root = '';
			$id = $this->a->getLevel(2, $levels);
			$fetch = Core::getOption($id);
			$widget = json_decode($fetch->value);
			$dock = new Dock($fetch->association);
			if (isset($_GET['options'])) {
				$dock->options = json_decode(stripslashes(urldecode($_GET['options'])));
			}
			$dock->page = $this->a->getLevel(3, $levels);
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

		function standaloneWidget($level, $levels, $cache)
		{
			ob_start();
			$this->a->root = '';
			$name = $this->a->getLevel(2, $levels);
			$page = $this->a->getLevel(3, $levels);
			$fake = new stdClass;
			$fake->id = $page;
			Control::$current[] = $fake;
			$widget = $this->a->getLevel(4, $levels);
			$fetch = $this->getOption('widget', $name);
			if (empty($fetch)) {
				@$data->widget = $widget;
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
