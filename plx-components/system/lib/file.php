<?php
	class File extends PlexusCrud
	{
		public $type = 'FILE';
		public $target = 'files';

		function construct()
		{
			$this->add('string', 'title', FALSE, array(
				'label' => $this->lang->get('Title'),
				'transformToAddress' => 1
			));
			$this->add('text', 'description', FALSE, array(
				'label' => $this->lang->get('Description')
			));
			$this->add('file', 'file', FALSE, array(
				'label' => $this->lang->get('File'),
				'target' => $this->target
			));
			$this->add('string', 'tags', FALSE, array(
				'label' => $this->lang->get('Tags'),
				'caption' => $this->lang->get('Separate with commas')
			));
			$this->add('datetime', 'published', TRUE, array(
				'label' => $this->lang->get('Published'),
				'caption' => $this->lang->get('May be in the future.')
			));
		}

		function init()
		{
			if (!empty($this->file)) {
				$this->src = $this->getStorage($this->target.'/'.$this->file);
			}
			if (isset($_GET['lite2'])) {
				$this->status = 2;
			}
		}

		function getContent()
		{
			return Template::get2('file.php', array('file' => $this));
		}
		
		function result()
		{
			if (!empty($this->description)) {
				$this->excerpt = $this->description;
			}

			if (strtolower(substr($this->file, -4)) == '.pdf') {
				$this->hasThumb = 1;
				$this->thumbSrc = $this->src;
			}

			$this->footer = 1;
			return $this->t->get('result-single.php', array('result' => $this));
		}

		function save($data = '')
		{
			$id = parent::save($data);
			if (isset($_GET['lite2'])) {
				if (empty($this->title)) {
					$this->title = $this->file;
				}
				echo '<a class="download" href="plx-file://'.$this->target.'/'.$this->file.'">'.$this->title.'</a>';
				exit;
			}
		}
	}
?>
