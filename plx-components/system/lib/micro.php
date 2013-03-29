<?php
	class Micro extends PlexusCrud
	{
		public $type = 'MICRO';

		function construct()
		{
			$this->add('text', 'post', TRUE, array(
				'label' => $this->lang->get('Text'),
				'limit' => 140,
				'rows' => 3
			));
			$this->add('datetime', 'published', TRUE, array(
				'label' => $this->lang->get('Published'),
				'caption' => $this->lang->get('May be in the future.')
			));
			$this->add('status', 'status', TRUE, array(
				'label' => $this->lang->get('Status')
			));
			$this->status = 1;
		}

        function onSaveReady()
        {
            $this->db->query('UPDATE '.$this->db->table('index').' SET address="'.base_convert($this->id+3600, 10, 36).'" WHERE id='.$this->id);
        }

		function getTitle()
		{
			return $this->lang->get('Micropost on {{'.date('l', $this->published).'}}, {{'.date('Y-m-d', $this->published).'}} at {{'.date('H:i', $this->published).'}}');
		}

		function getDescription()
		{
			return htmlspecialchars($this->post);
		}

        function getContent()
        {
        	$c = $this->tpl->get('view-micro.php', array('micro' => $this));
        	$this->tpl->set('view-micro.php');
			return $c;
        }

        function result()
        {
        	$this->excerpt = $this->post;
        	$this->title = $this->getTitle();
        	$this->footer = TRUE;
			return $this->tpl->get2('result-single.php', array('result' => $this));
        }
        
        function getPost()
        {
        	return $this->post;
        }
	}
?>
