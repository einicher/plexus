<?php
	class XHTMLMarkupWidget extends Widget
	{
		public $name = 'XHTML Widget';
		public $description = 'Add pure XHTML to your docks.';
		public $embedable = FALSE;

		function editFields()
		{
			return array('type' => 'widget',
				array(
					'type' => 'string',
					'name' => 'title',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Title')
					) 
				),
				array(
					'type' => 'text',
					'name' => 'markup',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Text'),
						'rows' => 12
					) 
				)
			);
		}
		
		function getTitle()
		{
			return $this->data->title;
		}

		function view($type = '')
		{
			return $this->data->markup;
		}
	}
?>
