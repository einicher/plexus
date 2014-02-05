<?php
	/*
		Site Feed
		Tag Results
		Search Results
		User contributions
	*/

	class Feed extends Core
	{
		public $options;

		static $counter = 0;

		function &__construct(array $options = array())
		{
			$this->options = new stdClass;
			
			$this->set('excerptLength', 64);
			$this->set('titleLength', -1);
			$this->set('thumbWidth', $this->getOption('gallery.thumbSize'));
			$this->set('thumbHeight', $this->getOption('gallery.thumbSize'));
			$this->set('imageWidth', $this->getOption('content.width'));
			$this->set('headingBelowThumb', false);
			$this->set('limit', 10);
			$this->set('order', 'ORDER BY i.published DESC');
			
			//include POST,PAGE,IMAGE
			//exclude --""--
			//search
			//tag
			//author
			
			$this->set('noThumbs', 0);
			$this->set('paginationPages', 7);

			$this->set('showTypeSelector', false);
			$this->set('showPagination', true);

			$this->options = (object) array_merge((array) $this->options, $options);
			
			return $this;
		}

		function set($name, $value = '')
		{
			$this->options->$name = $value;
		}

		function option($name)
		{
			if (empty($this->options->$name)) {
				return FALSE;
			} else {
				return $this->options->$name;
			}
		}

		function getQuery()
		{
			$c = '';
			
			if (isset($this->options->sql)) {
				$query = $this->options->sql;
			} else {
				$properties = false;
				$select = '';
				
				if (isset($this->options->parent)) {
					$c .= ' && i.parent='.$this->options->parent;
				}
				
				if (isset($this->options->author)) {
					$c .= ' && i.author='.$this->options->author;
				}
				
				if (isset($this->options->include)) {
					if (is_string($this->options->include)) {
						$this->options->include = explode(',', $this->options->include);
					}
					$c .= ' && i.type IN ("'.implode('","', $this->options->include).'")';
				}
				
				if (isset($this->options->exclude)) {
					if (is_string($this->options->exclude)) {
						$this->options->exclude = explode(',', $this->options->exclude);
					}
					$c .= ' && i.type NOT IN ("'.implode('","', $this->options->exclude).'")';
				}
				
				if (isset($this->options->search)) {
					$properties = true;
					$p = '"+'.str_replace(' ', ' +', $this->d->escape($this->options->search)).'"';
					$c .= ' && MATCH(p.value) AGAINST('.$p.') && status!=2';
					$this->options->order = ' ORDER BY `score` ASC, i.published DESC';
					$select = ',MATCH(p.value) AGAINST('.$p.') `score`';
				}
				
				if (isset($this->options->tag)) {
					$properties = true;
					$c .= ' && p.name="tags" && (
						FIND_IN_SET("'.$this->options->tag.'", p.value)
						OR FIND_IN_SET(" '.$this->options->tag.'", p.value)
					)';
				}

				if (!empty($_GET['filter'])) {
					$c .= ' AND `type`="'.$this->d->escape($_GET['filter']).'"';
				}

				if ($properties) {
					$query = 'SELECT i.*'.$select.' FROM `#_index` i, `#_properties` p WHERE i.id=p.parent && status=1'.$c;
				} else {
					$query = 'SELECT i.*'.$select.' FROM `#_index` i WHERE status=1'.$c;
				}
			}

			// order
			if (!empty($this->options->order)) {
				$query .= ' '.$this->options->order;
			}

			return $query;
		}

		function getCount()
		{
			return Database::instance()->query($this->getQuery())->num_rows;
		}

		function getItems($page = 1)
		{
			$query = $this->getQuery();

			// limit
			$query .= $page > 1 ? ' LIMIT '.(($page*$this->option('limit'))-$this->option('limit')).','.$this->option('limit') : ' LIMIT '.$this->option('limit');

			$r = array();
			$rid = array();

			$results = Database::instance()->query($query);

			if ($results || $results->num_rows) {
				while ($f = $results->fetch_object()) {
					$r[$f->id] = $f;
					$rid[] = $f->id;
				}
			}

			if (empty($r)) {
				return array();
			}

			$results = Database::instance()->query('SELECT parent,name,value FROM `#_properties` WHERE parent IN('.implode(',', $rid).')');
			if ($results && $results->num_rows) {
				while ($f = $results->fetch_object()) {
					$r[$f->parent]->{$f->name} = $f->value;
				}
			}

			return $r;
		}

		function view()
		{

			$page = 1;
			if (Control::instance()->paginationActive) {
				$page = Control::instance()->paginationPage;
			}

			$r = $this->getItems($page);

			if ($page > 1 && count($r) > 0 && Control::instance()->paginationActive) {
				Control::instance()->paginationUsed = true;
			}

			$i = 0;
			$cache = array();
			$results = array();
			foreach ($r as $id => $result) {
				$i++;

				$type = $this->getDataType($result->type);
				require_once $type->file;
				$type = new $type->class($result, true);

				$type->count = $i;
				$type->titleLength = $this->option('titleLength');
				$type->excerptLength = $this->option('excerptLength');
				$type->noThumbs = $this->option('noThumbs');
				$type->thumbWidth = $this->option('thumbWidth');
				$type->thumbHeight = $this->option('thumbHeight');
				$type->headingBelowThumb = $this->option('headingBelowThumb');
				$type->imageWidth = $this->option('imageWidth');

				$results[] = $type->result($this->options, $cache, $i);
				if ($this->option('cluster') == 1) {
					$cache[] = $type;
				}
				if ($i == $this->options->limit) {
					break;
				}
			}

			$results = implode('', $results);

			// add pagination
			if ($this->options->showPagination) {
				$count = $this->getCount();
				if ($count > $this->option('limit')) {
					self::$counter++;
					$results .= Tools::instance()->pagination('feed-'.self::$counter, $count, $page, $this->option('limit'), $this->option('paginationPages')).'<div class="clear"></div>';
				}
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
			$current = §('-- Filter by Type --');
			foreach (Core::$types as $name => $type) {
				if (in_array($name, $this->options->include)) {
					$items[] = (object) array(
							'label' => $type['label'],
							'href' => $this->a->current('', FALSE, array('filter' => $name))
					);
				}
				if (isset($_GET['filter']) && $name == $_GET['filter']) {
					$current = §('Filtered by type {{<strong>'.$type['label'].'</strong>}}');
				}
			}
			return $this->t->get('feed-type-selector.php', array(
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
