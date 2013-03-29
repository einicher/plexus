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
						'label' => $this->lang->get('Title')
					) 
				)
			);
		}

		function init()
		{
			if ($this->addr->getLevel(1) == $this->addr->assigned('system.search')) {
				$this->show = FALSE;
			}		
		}

		function view()
		{
			return $this->tpl->cut('result.php', 'search', array('search' => (object) array(
				'action' => $this->addr->getRoot($this->addr->assigned('system.search')),
				'inputWidth' => ceil(($this->dock->width-16)*0.7),
				'buttonWidth' => ceil(($this->dock->width-14)*0.3)
			)));
		}

		function getTitle()
		{
			return $this->data->title;
		}
	}
?>
