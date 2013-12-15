<?php
	class SimpleBannerWidget extends Widget
	{
		public $name = 'Simple Banner';
		public $description = 'Have just an image and link it somewhere.';
		public $version = 1;
		public $link = 'http://plexus.setanodus.net/Components/System';
		public $author = 'Markus René Einicher';
		public $authorMail = 'markus.einicher@gmail.com';
		public $authorLink = 'http://einicher.plexus.at';
		public $target = 'simple-banner';

		function editFields()
		{
			return array('type' => 'widget',
				array('type' => 'file', 'name' => 'image', 'required' => TRUE, 'options' => array(
					'label' => §('Image'),
					'target' => $this->target
				)),
				array('type' => 'string', 'name' => 'link', 'required' => FALSE, 'options' => array(
					'label' => §('Link'),
					'caption' => §('To link to homepage use “/”, page links sould start with “/” too. For example: /Develop/Database.')
				))
			);
		}
		
		function view($type = '')
		{
			if (empty($this->data->image)) {
				return;
			}

			$src = $this->a->getRoot($this->getStorage($this->target.'/'.$this->data->image));

			if (!empty($this->dock->width) && !empty($this->dock->height)) {
				$src = $this->imageScaleLink($this->getStorage($this->target.'/'.$this->data->image), $this->dock->width, $this->dock->height);
			} elseif (!empty($this->dock->width)) {
				$src = $this->imageScaleLink($this->getStorage($this->target.'/'.$this->data->image), $this->dock->width);
			} elseif (!empty($this->dock->height)) {
				$src = $this->imageScaleLink($this->getStorage($this->target.'/'.$this->data->image), '', $this->dock->height);
			}

			if (!empty($this->data->link)) {
				if ($this->data->link == '/') {
					$this->data->link = $this->a->getRoot();
				}
				return $this->t->get('widget-simple-banner.php', array(
					'href' =>  $this->data->link,
					'src' => $src,
					'linked' => true
				));
			} else {
				return $this->t->get('widget-simple-banner.php', array(
					'src' => $src,
					'linked' => false
				));
			}
		}
	}
?>
