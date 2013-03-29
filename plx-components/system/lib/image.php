<?php
	class Image extends PlexusDataModel
	{
		public $type = 'IMAGE';
		public $target = 'images';
		
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
			$this->add('status', 'status', TRUE, array(
				'label' => $this->lang->get('Status')
			));

			$this->observer->connect('plexusDatabase.cols.image', 'plexusDatabaseCols', $this);
		}

		function init()
		{
			$this->src = $this->getOriginalLink();
			$this->fullsize = $this->getFullsizeLink();
			$this->resized = $this->getResizedLink();
			$this->thumb = $this->imageScaleLink($this->src, '150', '113');
			if (isset($_GET['lite']) || isset($_GET['lite2'])) {
				$this->status = 2;
			}
			if ($this->status == 2) {
				$this->noRealAddress = true;
			}
		}

		function getOriginalLink()
		{
			return $this->getStorage($this->target).'/'.$this->file;
		}

		function getFullsizeLink()
		{
			return $this->imageScaleLink($this->getOriginalLink(), $this->getOption('content.fullsize'));
		}

		function getResizedLink()
		{
			return $this->imageScaleLink($this->getOriginalLink(), $this->getOption('content.width'));
		}

		function getTitle()
		{
			return $this->observer->notify('image.getTitle', $this->title);
		}
		
		function getDescription()
		{
			return $this->description;
		}
		
		function save($data = '')
		{
			$id = parent::save($data);
			if (isset($_GET['lite'])) {
				$image = $this->type('IMAGE', $id);
				$image->fullsize = $this->imageScaleLink($image->src, $this->getOption('content.fullsize'));
				$image->resized = $this->imageScaleLink($image->src, $this->getOption('content.width'));
				$image->thumb = $this->imageScaleLink($image->src, '150', '113');
				echo $this->tpl->cut('form.php', 'wysiwygMultimediaImage', array('image' => $image));
				exit;
			}
			if (isset($_GET['lite2'])) { // introduced in 0.5
				$image = $this->type('IMAGE', $id);
				$image->fullsize = $this->imageScaleLink($image->src, $this->getOption('content.fullsize'));
				$image->resized = $this->imageScaleLink($image->src, $this->getOption('content.width'));
?>
				<a class="lightThumb" href="<?=$image->fullsize?>" rel="lightboxPageContent"><img class="lightThumb" src="<?=$image->resized?>" alt="" /></a>
<?php
				exit;
			}
			return $id;
		}

		function getContent()
		{
			$this->enlargedSrc = $this->imageScaleLink($this->getOriginalLink(), $this->getOption('content.fullsize'));
			$this->src = $this->imageScaleLink($this->getOriginalLink(), $this->getOption('content.width'));
			$c = $this->tpl->get('view-image.php', array(
				'image' => $this
			));
        	$this->tpl->set('view-image.php');
			return $c;
		}

		function result($data = '')
		{
			if (empty($this->description)) {
				if (isset($data->width)) {
					$width = $data->width;
				} else {
					$width = $this->getOption('content.width');
				}
				$image = (object) array(
					'width' => $width,
					'src' => $this->imageScaleLink($this->src, $width)
				);
			} else {
				$this->thumbSrc = $this->src;
				$this->hasThumb = TRUE;
				$this->excerpt = $this->tools->cutByWords(strip_tags($this->tools->detectSpecialSyntax($this->description)), $this->excerptLength);
			}
			$this->footer = 1;
			$result = array('result' => $this);
			if (isset($image)) {
				$result['image'] = $image;
			}
			return Template::get2('result-single.php', $result);
		}

		function plexusDatabaseCols($cols)
		{
			exit;
			return array('id' => 'ID');
		}
	}
?>