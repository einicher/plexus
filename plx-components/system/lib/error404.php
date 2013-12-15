<?php
	class Error404 extends PlexusCrud
	{
		public $type = 'ERROR404';
		public $showEditPanel = FALSE;
		
		function construct()
		{
			header('HTTP/1.0 404 Not Found');
			$this->title = §('Error 404');
		}
		
		function getTitle()
		{
			return $this->title;
		}
		
		function getContent()
		{
			return $this->t->get('error404.php', array(
				'search' => $this->t->get('search.php', array(
					'search' => (object) array(
						'pattern' => '',
						'action' => $this->a->getRoot($this->a->assigned('system.search'))
					)
				))
			));
		}
	}
?>
