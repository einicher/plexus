<?php
	class Post extends Page
	{
		public $type = 'POST';
		public $status = 1;

		function getContent()
		{
			$c = $this->observer->notify('post.getContent', $this->tpl->get('view-post.php', array('post' => $this)));
			$this->tpl->set('view-post.php');
			return $c;
		}
	}
?>