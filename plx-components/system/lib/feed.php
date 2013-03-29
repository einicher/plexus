<?php
	/*
		Site Feed
		Tag Results
		Search Results
		User contributions
	*/

	class Feed
	{
		public $options;

		static $counter = 0;

		function __construct(array $options = array())
		{
			$this->options = (object) $options;
			$this->set('excerptLength', 64);
			$this->set('thumbWidth', 100);
			$this->set('thumbHeight', 100);
			$this->set('limit', 10);
			$this->set('order', 'ORDER BY published DESC');
			$this->set('cluster', 1);
			$this->set('noThumbs', 0);
			$this->set('paginationPages', 7);

			$this->set('showTypeSelector', false);
			$this->set('showPagination', true);
		}

		function set($name, $value = '')
		{
			if (!isset($this->options->$name)) {
				$this->options->$name = $value;
			}
		}

		function option($name)
		{
			if (empty($this->options->$name)) {
				return FALSE;
			} else {
				return $this->options->$name;
			}
		}

		function view()
		{
			$c = '';
			
			if (isset($this->options->sql)) {
				$query = $this->options->sql;
			} else {
				$query = 'SELECT * FROM `#_index`';

				if (isset($this->options->parent)) {
					$c .= ' parent='.$this->options->parent;
				}

				if (!empty($c)) {
					$query .= ' WHERE'.$c;
				}
			}

			// order
			if (!empty($this->options->order)) {
				$query .= ' '.$this->options->order;
			}

			$r = array();
			$rid = array();

			$results = Database2::instance()->query($query);
			if ($results || $results->num_rows) {
				$count = $results->num_rows;
				while ($f = $results->fetch_object()) {
					$r[$f->id] = $f;
					$rid[] = $f->id;
				}
			}

			if (empty($r)) {
				return;
			}

			// limit
			$page = 1;
			if (Control::getInstance()->paginationActive) {
				$page = Control::getInstance()->paginationPage;
			}
			$limit = $this->option('limit');
			$query .= $page > 1 ? ' LIMIT '.(($page*$limit)-$limit).','.$limit : ' LIMIT '.$limit;

			$results = Database2::instance()->query('SELECT parent,name,value FROM `#_properties` WHERE parent IN('.implode(',', $rid).')');
			if ($results && $results->num_rows) {
				while ($f = $results->fetch_object()) {
					$r[$f->parent]->{$f->name} = $f->value;
				}
			}

			$i = 0;
			$cache = array();
			$results = array();
			Template::connect('siteFeedThumb', 100);
			foreach ($r as $id => $result) {
				$i++;
				$type = Core::getType($result->type);
				require_once $type->file;
				$type = new $type->class($result, true);

				$type->excerptLength = $this->option('excerptLength');
				$type->count = $i;
				$type->noThumbs = $this->option('noThumbs');

				$results[] = $type->result($this->options, $cache, $i);
				if ($this->option('cluster') == 1) {
					$cache[] = $type;
				}
				if ($i == $this->options->limit) {
					break;
				}
			}

			$results = implode('', $results).'<div class="clear"></div>';

			// add pagination
			if ($this->options->showPagination && $count > $limit) {
				self::$counter++;
				$results .= Tools::getInstance()->pagination('feed-'.self::$counter, $count, $page, $limit, $this->option('paginationPages')).'<div class="clear"></div>';
			}

			// add type select
			if ($this->options->showTypeSelector) {
				$results = $this->getTypeSelector().$results;
			}			

			return $results;
		}

		function getTypeSelector()
		{
			$items = array();
			$current = $this->lang->get('-- Filter by Type --');
			foreach (Core::$types as $name => $type) {
				if (in_array($name, $this->data->except)) {
					$items[] = array(
						'item' => (object) array(
							'label' => $type['label'],
							'href' => $this->addr->current('', FALSE, array('filter' => $name))
						)
					);
				}
				if (isset($_GET['filter']) && $name == $_GET['filter']) {
					$current = $this->lang->get('Filtered by type {{<strong>'.$type['label'].'</strong>}}');
				}
			}
			return Template::get2('feed-type-selector.php', array(
				'current' => $current,
				'items' => $items
			));
		}

		function __toString()
		{
			return $this->get();
		}
	}
?>
