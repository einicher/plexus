<?php
	class PlexusCache
	{
		public $path;
		public $src;
		public $width = 0;
		public $height = 0;

		protected $cache;

		static $storage;

		function __construct($args)
		{
			if (!empty($args['width'])) { $this->width = $args['width']; }
			if (!empty($args['height'])) { $this->height = $args['height']; }
			if (!empty($args['path'])) { $this->path = $args['path']; }
			if (substr($this->path, 0, 4) == 'http') {
				$this->src = $this->path;
			} else {
				$this->path = parse_url($this->path);
				$this->path = $this->path['path'];
				if (substr($this->path, 0, 14) == 'plx-components') {
					$this->src = $this->path;
				} else {
					$this->src = $this->getStorage($this->path);
				}
			}
			$this->cache = $this->getStorage('cache/');
			if (!file_exists($this->cache)) {
				mkdir($this->cache);
			}
			$ext = explode('.', $this->path);
			$ext = array_pop($ext);
			$this->cachename = md5($this->path.$this->width.$this->height).'.'.$ext;
		}

		function clearCache()
		{
			$dir = opendir($this->cache);
			while ($c = readdir($dir)) {
				if ($c != '.' && $c !== '..') {
					unlink($dir.$c);
				}
			}
		}

		function view()
		{
			$file = $this->cache.$this->cachename;
			if (strtolower(substr($this->src, -4)) == '.pdf') {
				$file = substr($file, 0, -4).'.jpg';
			}
			if (file_exists($file) && !isset($_GET['force'])) {
				if (isset ($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
					if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) < time()) {
						header ('HTTP/1.1 304 Not Modified');
						exit;
					}
				}
				header('content-type: image/jpeg');
				return file_get_contents($file);
			} else {
				if (strtolower(substr($this->src, -4)) == '.pdf') {
					$i = new imagick($this->src);
					$i->setImageFormat('jpg');
					$i->setResolution(72,72);
					$i->thumbnailImage($this->width, $this->height);
					$i->writeImage($file);
				} else {
					list($width, $height, $type) = getimagesize($this->src);
					switch ($type) {
						case 1: $img = imageCreateFromGIF($this->src); break;
						case 2: $img = imageCreateFromJPEG($this->src); break;
						case 3:
							$img = imageCreateFromPNG($this->src);
							imagealphablending($img, true);
						break;
						default: exit('Could not read image format.');
					}

					$src_x = 0;
					$src_y = 0;
					$src_w = $width;
					$src_h = $height;

					if ($width < $this->width) {
						$this->width = $width;
					}
					if ($height < $this->height) {
						$this->height = $height;
					}

					if (empty($this->width) && empty($this->height)) {
						$nw = $width;
						$nh = $height;
					} elseif (empty($this->height)) {
						$ratio = $width/$height;
						$nw = $this->width;
						$nh = $this->width/$ratio;
					} elseif (empty($this->width)) {
						$ratio = $height/$width;
						$nh = $this->height;
						$nw = $this->height/$ratio;
					} else {
						$nw = $this->width;
						$nh = $this->height;
						$cmp_x = $width/$nw;
						$cmp_y = $height/$nh;
						if ($cmp_x > $cmp_y) {
							$src_w = round(($width/$cmp_x*$cmp_y));
							$src_x = round(($width-($width/$cmp_x*$cmp_y))/2);
						} elseif ($cmp_y > $cmp_x) {
							$src_h = round(($height/$cmp_y*$cmp_x));
							$src_y = round(($height-($height/$cmp_y*$cmp_x))/2);
						}
					}
					$new = imagecreatetruecolor($nw, $nh);
					imagealphablending($new, false);
					imagesavealpha($new, true);
					$transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
					imagefilledrectangle($new, 0, 0, $nw, $nh, $transparent);
					imagecolortransparent($new, $transparent);
					imagecopyresampled($new, $img, 0, 0, $src_x, $src_y, $nw, $nh, $src_w, $src_h);
					switch ($type) {
						case 1: imageGIF($new, $file); break;
						case 2: imageJPEG($new, $file); break;
						case 3: imagePNG($new, $file); break;
					}
				}
				header('content-type: image/jpeg');
				return file_get_contents($file);
			}
		}

		function getStorage($append = '') // copy changes to the plexus Core ! its redundant for performance reasons
		{
			if (empty(self::$storage)) {
				self::$storage = PLX_STORAGE;
				if (file_exists(PLX_MULTI)) {
					self::$storage = PLX_MULTI.$_SERVER['SERVER_NAME'].'/';
				}
			}
			return self::$storage.$append;
		}
	}
?>
