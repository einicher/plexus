<?php
	class Error404 extends PlexusCrud
	{
		public $type = 'ERROR404';
		public $showEditPanel = FALSE;
		
		function construct()
		{
			header('HTTP/1.0 404 Not Found');
			$this->title = $this->lang->get('Error 404');
		}
		
		function getTitle()
		{
			return $this->title;
		}
		
		function getContent()
		{
			return $this->tpl->get('error404.php');
		}
	}
?>
