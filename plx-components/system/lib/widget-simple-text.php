<?php
	class SimpleTextWidget extends Widget
	{
		public $name = 'Simple Text';
		public $description = 'Add some text and an optional Title (with WYSIWYG editor).';
		public $data;
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
					'type' => 'wysiwyg',
					'name' => 'text',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Text'),
						'rows' => 12,
						'mode' => 'simple'
					) 
				)
			);
		}

		function getTitle()
		{
			return $this->data->title;
		}

		function view()
		{
			return $this->tools->detectSpecialSyntax($this->data->text);
		}
	}
?>