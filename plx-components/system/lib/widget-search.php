<?php
	class SearchWidget extends Widget
	{
		public $name = 'Search';
		public $description = 'Display a basic search field.';

		function editFields()
		{
			return array(
				array(
					'type' => 'string',
					'name' => 'title',
					'required' => FALSE,
					'options' => array(
						'label' => §('Title')
					) 
				)
			);
		}

		function init()
		{
			if ($this->a->getLevel(1) == $this->a->assigned('system.search')) {
				$this->show = FALSE;
			}		
		}

		function view($type = '')
		{
			return $this->t->get('search.php', array('search' => (object) array(
				'action' => $this->a->getRoot($this->a->assigned('system.search')),
				'inputWidth' => ceil(($this->dock->width-16)*0.7),
				'buttonWidth' => ceil(($this->dock->width-14)*0.3)
			)));
		}

		function getTitle()
		{
			return @$this->data->title;
		}
	}
?>
