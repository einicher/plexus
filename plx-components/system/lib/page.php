<?php
	class Page extends PlexusCrud
	{
		public $type = 'PAGE';
		public $status = 2;

		function __construct($mixed1 = '', $mixed2 = '')
		{
			if (!empty($mixed1) && is_string($mixed1) && !is_numeric($mixed1)) {
				$this->construct();
				$this->title = $mixed1;
				$this->content = $mixed2;
				$this->init();
				return $this;
			} else {
				$return = parent::__construct($mixed1, $mixed2);
			}
			return $return;
		}

		function construct()
		{
			$this->add('string', 'title', FALSE, array(
				'label' => §('Title'),
				'transformToAddress' => 1
			));
			$this->add('wysiwyg', 'content', FALSE, array(
				'label' => §('Content'),
				'multimedia' => TRUE
			));
			$this->add('string', 'tags', FALSE, array(
				'label' => §('Tags'),
				'caption' => §('Separate with commas'),
				'suggest' => 'Tools::suggestTags()'
			));
			$this->add('datetime', 'published', TRUE, array(
				'label' => §('Published'),
				'caption' => §('May be in the future.')
			));
			$this->add('status', 'status', TRUE, array(
				'label' => §('Status')
			));
		}

		function getTitle()
		{
			return $this->o->notify('page.getTitle', $this->title);
		}

		function getContent()
		{
			return $this->o->notify('page.getContent', $this->t->get('view-page.php', array('page' => $this)));
		}

		function getTags()
		{
			return $this->o->notify('page.getTags', $this->tags);
		}

		function result()
		{
			$c = $this->tools->stripSpecialSyntax($this->content); //caution, if special syntax is found here widgets find themselfs => loop
			$i = $this->tools->detectImage($c);
			if (!empty($i)) {
				$this->hasThumb = TRUE;
				$this->thumbSrc = str_replace('../', '', $i);
				$this->thumbWidth = 100;
			}
			$this->excerpt = $this->tools->cutByWords(strip_tags($c), $this->excerptLength);
			$this->footer = 1;
			return $this->t->get('result-single.php', array('result' => $this));
		}
	}
?>
