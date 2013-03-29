<?php
	class ListView extends Core
	{
		private $items = array();
		private $cols = array();
		private $sql;
		private $pagination = '';
		public $limit = 30;

		function __construct(array $cols)
		{
			$this->cols = $cols;
		}

		function __toString()
		{
			return $this->view();
		}

		function browseDatabase($sql)
		{
			$start = 0;
			$current = 1;
			$limit = $this->limit;
			if (is_numeric($this->addr->level(-1))) {
				$current = $this->addr->level(-1);
				$start = ($current*$limit)-$limit;
			}

			$qry = mysql_query($sql);
			$count = mysql_num_rows($qry);
			$sql .=  'LIMIT '.$start.','.$limit;
			$qry = mysql_query($sql);

			if ($count > $limit) {
				$this->pagination = $this->tools->pagination('listViewPagination', $count, $current, $limit);
			}

			while ($fetch = mysql_fetch_object($qry)) {
				$this->add($this->type($fetch));
			}
		}

		function add($item)
		{
			$this->items[] = $item;
		}

		function view()
		{
			foreach ($this->items as $item) {
				foreach ($this->cols as $col => $label) {
					if (is_array($label) && isset($label['callback']) && isset($label['actor'])) {
						$value = call_user_func(array($label['actor'], $label['callback']), $item->$col, $item);
					} else {
						$value = strip_tags($item->$col);			
					}
					$this->tpl->repeat('list-view.php', 'icol', array('value' => $value));
				}
				$this->tpl->repeat('list-view.php', 'item', array('item' => $item));
				$this->tpl->set('list-view.php', 'icol');
			}

			foreach ($this->cols as $col => $label) {
				if (is_array($label)) {
					if (isset($label['label'])) {
						$label = $label['label'];
					} else {
						$label = $col;
					}
				}
				if (empty($_GET['asc'])) {
					$label = '<a class="asc" href="'.$this->addr->current().'?asc='.$col.'">'.$label.'</a>';
				} else {
					$label = '<a class="desc" href="'.$this->addr->current().'?desc='.$col.'">'.$label.'</a>';
				}
				$this->tpl->repeat('list-view.php', 'hcol', array('value' => $label));
			}
			$this->tpl->cut('list-view.php', 'header');
			$this->tpl->set('list-view.php', 'hcol');

			if (!empty($this->pagination)) {
				$this->tpl->cut('list-view.php', 'pagination', array(
					'colspan' => count($this->cols),
					'pagination' => $this->pagination
				));
			}

			return $this->tpl->get('list-view.php');
		}
	}
?>