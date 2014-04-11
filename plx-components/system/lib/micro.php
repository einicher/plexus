<?php
	class Micro extends PlexusCrud
	{
		public $type = 'MICRO';

		function construct()
		{
			$this->add('text', 'post', TRUE, array(
				'label' => §('Text'),
				'limit' => 140,
				'rows' => 3
			));
			$this->add('datetime', 'published', TRUE, array(
				'label' => §('Published'),
				'caption' => §('May be in the future.')
			));
			$this->add('status', 'status', TRUE, array(
				'label' => §('Status')
			));
			$this->status = 1;
		}

		function beforeSave($data)
		{
			preg_match_all('/#(\w+)/', $this->post, $results);
			$this->add('string', 'tags');
			$this->tags = implode(',', $results[1]);			
		}

        function onSaveReady($data)
        {
            $this->d->query('UPDATE `#_index` SET address="'.base_convert($this->id+3600, 10, 36).'" WHERE id='.$this->id);
        }

		function getTitle()
		{
			return §('Micropost on {{'.date('l', $this->published).'}}, {{'.date('Y-m-d', $this->published).'}} at {{'.date('H:i', $this->published).'}}');
		}

		function getDescription($words = 37)
		{
			return htmlspecialchars($this->post);
		}

        function getContent()
        {
            if (!ContentControls::$editMode) {
            	$this->post = preg_replace('/#(\w+)/', '<a href="'.$this->a->assigned('system.tags').'/\\1">#\\1</a>', $this->post);
            }
        	return $this->t->get('view-micro.php', array('micro' => $this));
        }

        function result()
        {
        	$this->excerpt = $this->post;
        	$this->title = $this->getTitle();
        	$this->footer = true;
			return $this->t->get('result-single.php', array('result' => $this));
        }
        
        function getPost()
        {
        	return $this->post;
        }
	}
?>
