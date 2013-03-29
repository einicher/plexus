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
				'label' => $this->lang->get('Title'),
				'transformToAddress' => 1
			));
			$this->add('wysiwyg', 'content', FALSE, array(
				'label' => $this->lang->get('Content'),
				'multimedia' => TRUE
			));
			$this->add('string', 'tags', FALSE, array(
				'label' => $this->lang->get('Tags'),
				'caption' => $this->lang->get('Separate with commas'),
				'suggest' => 'Tools::suggestTags()'
			));
			$this->add('datetime', 'published', TRUE, array(
				'label' => $this->lang->get('Published'),
				'caption' => $this->lang->get('May be in the future.')
			));
			$this->add('status', 'status', TRUE, array(
				'label' => $this->lang->get('Status')
			));
		}

		function getTitle()
		{
			return $this->observer->notify('page.getTitle', $this->title);
		}

		function getContent()
		{
			return $this->observer->notify('page.getContent', Template::get2('view-page.php', array('page' => $this)));
		}

		function getTags()
		{
			return $this->observer->notify('page.getTags', $this->tags);
		}

		function result()
		{
			$c = $this->tools->detectSpecialSyntax($this->content);
			$i = $this->tools->detectImage($c);
			if (!empty($i)) {
				$this->hasThumb = TRUE;
				$this->thumbSrc = str_replace('../', '', $i);
			}
			$this->excerpt = $this->tools->cutByWords(strip_tags($c), $this->excerptLength);
			$this->footer = 1;
			return Template::get2('result-single.php', array('result' => $this));
		}
	}
?>