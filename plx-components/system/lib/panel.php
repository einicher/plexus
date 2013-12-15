<?php
	class Panel extends Core
	{
		protected $items = array();

		function addItem($menu, $name, $label, $args = array())
		{
			$this->items[$name] = array_merge($args, array(
				'menu' => $menu,
				'name' => $name,
				'label' => $label
			));
		}

		function view()
		{
			$m = array();
			$m['left'] = array();
			$m['right'] = array();

			foreach ($this->items as $name => $item) {
				if (empty($item['link'])) {
					$item['link'] = 'javascript:void(0);';
				} elseif (!empty($item['ajax']))  {
					$item['link'] .= '?ajax='.$this->addr->root;
				}
				$m[$item['menu']][] = (object) $item;
			}

			krsort($m['right']);

			return $this->t->get('panel.php', array(
				'menu' => $m
			));
		}
	}
?>
