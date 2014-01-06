<?php
	class GalleryWidget extends Widget
	{
		public $name = 'Display Gallery';
		public $description = 'Shows thumbnails of one of your image galleries.';
		public $version = 1;
		public $link = 'http://plexus.setanodus.net/Components/System';
		public $author = 'Markus René Einicher';
		public $authorMail = 'markus.einicher@gmail.com';
		public $authorLink = 'http://einicher.plexus.at';

		function init()
		{
			if (empty($this->data->thumbs)) {
				$this->data->thumbs = 5;
			}
		}

		function editWidget()
		{
			$options = array();
			$galleries = $this->pdb->get('GALLERY', NULL, TRUE);
			foreach ($galleries as $gallery) {
				$options[$gallery->id] = $gallery->title;
			}

			return new Form(array('type' => 'widget',
				array('type' => 'string', 'name' => 'title', 'required' => FALSE, 'options' => array(
					'label' => $this->lang->get('Title')
				)),
				array('type' => 'select', 'name' => 'gallery', 'required' => TRUE, 'options' => array(
					'label' => $this->lang->get('Gallery'),
					'caption' => $this->lang->get('Select the gallery you want to show.'),
					'values' => $options
				)),
				array('type' => 'number', 'name' => 'thumbs', 'required' => FALSE, 'options' => array(
					'label' => $this->lang->get('Number of Thumbs to show')
				))
				
			), $this->data);
		}

		function view($type = '')
		{
			$gallery = $this->getData($this->data->gallery);
			if ($gallery) {
				return $this->t->get('widget-gallery.php', array(
					'thumbs' => $gallery->listThumbs($this->data->thumbs),
					'title' => $this->data->title,
					'gallery' => $gallery,
					'widget' => $widget
				));
			} else {
				return 'GALLERY_'.$this->data->gallery.'_NOT_FOUND';
			}
		}
	}
?>
