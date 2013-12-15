<?php
	class Post extends Page
	{
		public $type = 'POST';
		public $status = 1;

		function getContent()
		{
			return $this->o->notify('post.getContent', $this->t->get('view-post.php', array(
				'post' => &$this
			)));
		}
	}
?>
