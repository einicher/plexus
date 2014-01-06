<?php
	class Video extends PlexusCrud
	{
		public $type = 'VIDEO';

		function construct()
		{
			$this->add('string', 'title', FALSE, array(
				'label' => §('Title'),
				'transformToAddress' => 1
			));
			$this->add('text', 'code', TRUE, array(
				'label' => §('Code'),
				'caption' => §('Add a code snippet you can copy from the various video platforms (You Tube for example).')
			));
			$this->add('wysiwyg', 'description', FALSE, array(
				'label' => §('Description')
			));
			$this->add('string', 'tags', FALSE, array(
				'label' => §('Tags'),
				'caption' => §('Separate with commas')
			));
			$this->add('datetime', 'published', TRUE, array(
				'label' => §('Published'),
				'caption' => §('May be in the future.')
			));
			$this->add('status', 'status', TRUE, array(
				'label' => §('Status')
			));
		}

		function init()
		{
			if (isset($_GET['lite2'])) {
				$this->status = 2;
			}
		}
		
		function getContent()
		{
			$this->code = self::detectAndFit($this->code);
			return $this->t->get('view-video.php', array('video' => $this));
		}

		function result($data = '')
		{
			$this->detect();
			if (!empty($this->src)) {
				if (!empty($this->description)) {
					$this->hasThumb = 1;
					$this->thumbSrc = $this->src;
				} else {
					if (isset($data->width)) {
						$width = $data->width;
					} else {
						$width = $this->getOption('content.width');
					}
					$image = (object) array(
						'width' => $width,
						'src' => $this->imageScaleLink($this->src, $width)
					);
				}
			}

			if (!empty($this->description)) {
				$this->excerpt = $this->tools->cutByWords(strip_tags($this->tools->detectSpecialSyntax($this->description)), $this->excerptLength);
			}

			$this->footer = 1;
			$result = array('result' => $this);
			if (isset($image)) {
				$result['image'] = $image;
			}
			return $this->t->get('result-single.php', $result);
		}

		static function detectAndFit($code)
		{
			if (stripos($code, '<iframe') !== FALSE) {
				$code = preg_replace('/<iframe([^>]*)>/ie', '\'<iframe\'.self::detectAndResizeObject(\'\\1\').\'>\'', preg_replace('/<iframe([^>]*)>/ie', '\'<iframe\'.self::detectAndResizeObject(\'\\1\').\'>\'', $code));
			} elseif (stripos($code, '<embed') !== FALSE) {
				$code = preg_replace('/<embed([^>]*)>/ie', '\'<embed\'.self::detectAndResizeObject(\'\\1\').\'>\'', preg_replace('/<object([^>]*)>/ie', '\'<object\'.self::detectAndResizeObject(\'\\1\').\'>\'', $code));
			} elseif (stripos($code, 'youtube.com/watch?v=') !== FALSE) {
				$path = parse_url($code);
				if (!empty($path['query'])) {
					parse_str($path['query'], $query);
					if (!empty($query['v'])) {
						$width = Core::getOption('content.width');
						$code = '<iframe width="'.$width.'" height="'.round($width/(16/9)).'" src="http://www.youtube.com/embed/'.$query['v'].'" frameborder="0" allowfullscreen="true"></iframe>';
					}
				}
			}
			return $code;
		}

		static function detectAndResizeObject($atts)
		{
			$atts = stripslashes($atts);
			if (stripos($atts, 'style="') !== FALSE) {
				preg_match('=width: (.*)px=i', $atts, $width);
				$width = $width[1];
				preg_match('=height: (.*)px=i', $atts, $height);
				$height = $height[1];
			} else {
				preg_match('/width\=\"([^\"]*)\"/i', $atts, $width);
				$width = $width[1];
				preg_match('/height\=\"([^\"]*)\"/i', $atts, $height);
				$height = $height[1];
			}
			$ratio = $width/$height;
			$newWidth = Core::getOption('content.width');
			$newHeight = round($newWidth/$ratio);
			$atts = preg_replace('/width\=\"([^\"]*)\"/i', 'width="'.$newWidth.'"', $atts);
			$atts = preg_replace('/height\=\"([^\"]*)\"/i', 'height="'.$newHeight.'"', $atts);
			return $atts;
		}

		function extractYouTubeHashFromUrl($url)
		{
			$hash = str_replace('http://www.youtube.com/v/', '', $url);
			$hash = explode('&', $hash);
			$hash = $hash[0];
			$hash = explode('?', $hash);
			$hash = $hash[0];
			return $hash;
		}

		function detectYouTube()
		{
			preg_match_all('/value\=\"([^\"]*)\"/i', $this->code, $results);
			foreach ($results[1] as $result) {
				if (stripos($result, 'youtube.com') !== FALSE) {
					$code = $this->extractYouTubeHashFromUrl($result);
					if (empty($this->description)) {
						$this->src = 'http://img.youtube.com/vi/'.$code.'/0.jpg';
					} else {
						$this->src = 'http://img.youtube.com/vi/'.$code.'/1.jpg';
					}
				}
			}
			if (empty($this->src)) {
				preg_match_all('/src\=\"([^\"]*)\"/i', $this->code, $results);
				foreach ($results[1] as $result) {
					if (stripos($result, 'youtube.com') !== FALSE) {
						$code = $this->extractYouTubeHashFromUrl($result);
						$code = parse_url($result, PHP_URL_PATH);
						$code = str_replace('/embed/', '', $code);
						if (empty($this->description)) {
							$this->src = 'http://img.youtube.com/vi/'.$code.'/0.jpg';
						} else {
							$this->src = 'http://img.youtube.com/vi/'.$code.'/1.jpg';
						}
					}
				}
			}
		}

		function detect()
		{
			$this->detectYouTube();
		}

		function getMeta()
		{
			if (stripos($this->code, '<iframe') !== FALSE) {
				preg_match('/src="([^\"]*)"/i', $this->code, $r);
				$code = str_replace('//www.youtube.com/embed/', '', $r[1]);
				$video = 'http://www.youtube.com/v/'.$code;
				$image = 'http://img.youtube.com/vi/'.$code.'/0.jpg';
			}
			if (!empty($video)) {
?>
		<meta property="og:video" content="<?=$video?>" />
		<meta property="og:video:type" content="application/x-shockwave-flash" />
<?php
			}
			if (!empty($image)) {
?>
		<meta property="og:image" content="<?=$image?>" />
<?php
			}
		}

		function save($data = '')
		{
			$id = parent::save($data);
			if (isset($_GET['lite2'])) {
				echo '<div class="video">'.$id.'</div>';
				exit;
			}
		}
	}
?>
