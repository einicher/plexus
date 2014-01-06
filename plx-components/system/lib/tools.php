<?php
	class Tools extends Core
	{
		static $instance;

		static public function &instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function cutByWords($text, $count = 28)
		{
			$text = strip_tags($text);
			$text = str_replace("\t", ' ', $text);
			$text = preg_replace('=[\ ]+=', ' ', $text);

			$words = explode(' ', $text);
			if (count($words) > $count) {
				$text = '';
				for ($z=0; $z<=$count; $z++) {
					$text .= $words[$z].' ';
				}
				$text .= '..';
			}
			return $text;
		}

		function cutByChars($text, $chars = 72, $openEnd = TRUE)
		{
			$text = str_replace("\t", ' ', $text);
			$text = preg_replace('=[\ ]+=', ' ', $text);

			if (strlen($text) < $chars) {
				return $text;
			} else {
				if ($openEnd) {
					return mb_substr($text, 0, $chars-2, 'UTF-8').'..';
				} else {
					return mb_substr($text, 0, $chars-5, 'UTF-8').'..'.mb_substr($text, strlen($text)-5, strlen($text), 'UTF-8');
				}
			}
		}

		/** Detect human readable dates like yesterday, tomorrow, toda from an unix timestamp
		 *
		 * @param int $stamp Unix Timestamp that should be converted
		 */
		function detectTime($stamp, $dateonly = 0)
		{
			$vorGestern = strtotime('yesterday -1 days');
			$yesterday = strtotime('yesterday');
			$today = strtotime('today');
			$tomorrow = strtotime('tomorrow');
			$ueberMorgen = strtotime('tomorrow +1 day');
			$ueberUeberMorgen = strtotime('tomorrow +2 days');

			if ($dateonly == 1) {
				if ($stamp >= $ueberMorgen && $stamp < $ueberUeberMorgen) {
					return §('the day after tomorrow');
				} elseif ($stamp >= $tomorrow && $stamp < $ueberMorgen) {
					return §('tomorrow');
				} elseif ($stamp >= $today && $stamp < $tomorrow) {
					return §('today');
				} elseif ($stamp >= $yesterday && $stamp < $today) {
					return §('yesterday');
				} elseif ($stamp >= $vorGestern && $stamp < $yesterday) {
					return §('two days ago');
				} else { §('two days ago at').' '.date('H:i', $stamp);
					$date = date(§('l, F j, Y'), $stamp);
					if (function_exists('localeDate')) {
						$date = localeDate($date);
					}
					return §('on {{'.$date.'}}');
				}
			} else {
				if ($stamp >= $ueberMorgen && $stamp < $ueberUeberMorgen) {
					return §('the day after tomorrow at').' '.date('H:i', $stamp);
				} elseif ($stamp >= $tomorrow && $stamp < $ueberMorgen) {
					return §('tomorrow at').' '.date('H:i', $stamp);
				} elseif ($stamp >= $today && $stamp < $tomorrow) {
					return §('today at').' '.date('H:i', $stamp);
				} elseif ($stamp >= $yesterday && $stamp < $today) {
					return §('yesterday at').' '.date('H:i', $stamp);
				} elseif ($stamp >= $vorGestern && $stamp < $yesterday) {
					return §('two days ago at').' '.date('H:i', $stamp);
				} else {
					$date = date(§('l, F j, Y'), $stamp);
					if (function_exists('localeDate')) {
						$date = localeDate($date);
					}
					if ($dateonly == 2) {
						return §('{{'.date(§('m/d/Y'), $stamp).'}}, {{'.date('H:i', $stamp).'}}');
					} elseif ($dateonly == 3) {
						return §('{{'.$date.'}}, {{'.date('H:i', $stamp).'}}');
					} else {
						return §('on {{'.$date.'}} at {{'.date('H:i', $stamp).'}}');
					}
				}
			}
		}

		/* Get the age of timestamp or date
		 * $birthdate is date like 19851225, if $timestamp is set to TRUE $birthdate is considered a unix timestamp
		 */
		function age($birthdate, $timestamp = FALSE)
		{
			if ($timestamp) {
				$day = date('d', $birthdate);
				$month = date('m', $birthdate);
				$year = date('Y', $birthdate);
			} else {
				$day = substr($birthdate, 6, 2);
				$month = substr($birthdate, 4, 2);
				$year = substr($birthdate, 0, 4);
			}

			$age1 = date("Y",time())-$year;
			$age2 = date("m",time())-$month;
			$age3 = date("d",time())-$day;

			if ($age2 < 0 AND $age3 < 0) { 
				$age1--;
			}
			return $age1;
		}

		function correctRootPaths($path)
		{
			if (substr($path, 0, 7) == 'mailto:' || substr($path, 0, 1) == '#' || strpos($path, '://') !== FALSE) {
				return $path;
			} else {
				$chunks = str_split($path, 3);
				foreach ($chunks as $key => $chunk) {
					if ($chunk == '../') {
						unset($chunks[$key]);
					} else {
						break;
					}
				}
				return $this->a->root.implode('', $chunks);
			}
		}

		function detectLinkInText($text)
		{
			$text = preg_replace('=http://(\S*)=i', '<a href="http://\\1" target="blank">\\1</a>', $text);
			return $text;
		}

		function detectProblems($content)
		{
			$content = preg_replace_callback('=href\="([^\"]*)"=U', create_function('$m', 'return \'href="\'.Tools::instance()->correctRootPaths($m[1]).\'"\';'), $content);
			$content = preg_replace_callback('=src\="([^\"]*)"=U', create_function('$m', 'return \'src="\'.Tools::instance()->correctRootPaths($m[1]).\'"\';'), $content);
			return $content;
		}

		function detectSpecialSyntax($content)
		{
			$content = preg_replace_callback('=<div class\="widget\">(.*)</div>=iU', create_function('$m', 'return Tools::instance()->replaceWidget($m[1]);'), $content);
			$content = preg_replace_callback('=<div class\="video\">(.*)</div>=iU', create_function('$m', 'return Tools::instance()->replaceVideo($m[1]);'), $content);
			$content = preg_replace_callback('=<div class\="gallery\">(.*)</div>=iU', create_function('$m', 'return Tools::instance()->replaceGallery($m[1]);'), $content);
			$content = str_replace('rel="lightbox[pageContent]"', 'rel="lightboxPageContent"', $content);
			
			$content = $this->detectProblems($content);
			
			$content = preg_replace_callback('=\[\[([^|[]*)\|([^]]*)\]\]=U', create_function('$m', 'return \'<a href="\'.Tools::instance()->detectLink($m[1]).\'">\'.$m[2].\'</a>\';'), $content);
			$content = preg_replace_callback('=\[\[(.*)\]\]=U', create_function('$m', 'return \'<a href="\'.Tools::instance()->detectLink($m[1]).\'">\'.$m[1].\'</a>\';'), $content);

			$content = preg_replace_callback('=<a([^\>]*)>=iU', create_function('$m', 'return \'<a\'.Tools::instance()->detectLinkTarget($m[1]).\'>\';'), $content);
			
			$content = $this->detectStoragePaths($content);
			$content = preg_replace_callback('=href\="plx-file://(.*)"=iU', create_function('$m', 'return \'href="\'.Tools::instance()->plexusFile($m[1]).\'"\';'), $content);

			return $content;
		}

		function stripSpecialSyntax($content)
		{
			$content = preg_replace('=<div class\="widget\">(.*)</div>=iU', '', $content);
			$content = preg_replace('=<div class\="video\">(.*)</div>=iU', '', $content);
			$content = preg_replace('=<div class\="gallery\">(.*)</div>=iU', '', $content);
			
			$content = $this->detectProblems($content);
			
			$content = preg_replace('=\[\[([^|[]*)\|([^]]*)\]\]=U', '\\2', $content);
			$content = preg_replace('=\[\[(.*)\]\]=U', '\\1', $content);

			$content = $this->detectStoragePaths($content);
			$content = preg_replace_callback('=href\="plx-file://(.*)"=iU', create_function('$m', ' return \'href="\'.Tools::instance()->plexusFile($m[1]).\'"\';'), $content);

			return $content;
		}

		function detectStoragePaths($text)
		{
			$text = preg_replace_callback('=src\="plx-storage://(.*)"=iU', create_function('$m', 'return \'src="\'.$this->overwriteCacheParams($m[1]).\'"\';'), $text);
			$text = preg_replace_callback('=href\="plx-storage://(.*)"=iU', create_function('$m', 'return \'href="\'.$this->overwriteCacheParams($m[1], 1).\'"\';'), $text);
			return $text;
		}

		function plexusFile($file)
		{
			return $this->a->getRoot('plx-file/'.$file);
		}

		function overwriteCacheParams($path, $mode = 0)
		{
			if ($mode) {
				$query = '?w='.$this->getOption('content.fullsize');
			} else {
				$query = '?w='.$this->getOption('content.width');
			}
			return $this->a->getRoot('plx-cache/').$path.$query;
		}

		function detectLink($link)
		{
			if (substr($link, 0, 1) == '/') {
				return $this->a->getRoot(substr($link, 1));
			} elseif (strpos($link, '://') !== FALSE) {
				return $link;
			} else {
				return $this->a->current($this->a->transform($link));
			}
		}

		function detectLinkTarget($a)
		{
			$a = stripcslashes($a);
			$home = substr($this->a->getHome(), 0, -1);
			$count = strlen($home);

			if (preg_match('=href\="([^\"]*)"=iU', $a, $r)) {
				if ((substr($r[1], 0, 7) == 'http://' || substr($r[1], 0, 8) == 'https://') && substr($r[1], 0, $count) != $home) {
					if (preg_match('=class\="([^\"]*)"=iU', $a, $r2)) {
						$a = preg_replace('=class\="([^\"]*)"=iU', 'class="'.$r2[1].' external"', $a);
					} else {
						$a .= ' class="external"';
					}
					if (preg_match('=target\="([^\"]*)"=iU', $a, $r2)) {
						$a = preg_replace('=target\="([^\"]*)"=iU', 'target="_blank"', $a);
					} else {
						$a .= ' target="_blank"';
					}
				}
			}

			return $a;
		}

		function replaceWidget($id)
		{
			if (is_numeric($id)) {
				$widget = $this->getOption($id);
				if (!empty($widget)) {
					$widget = json_decode($widget->value);
					if (!isset(self::$widgets[$widget->widget])) {
						return 'WIDGET_'.$widget->widget.'_NOT_FOUND';
					}
					$widget->id = $id;
					require_once self::$widgets[$widget->widget]['file'];
					$widget = new $widget->widget('plx.embedded', -1, $widget);
					$title = $widget->getTitle();
					$view = $widget->view('embedded');
					if ($title) {
						$view = '<h1>'.$title."</h1>".$view;
					}
					return '<div id="widgetNo'.$id.'" class="widget '.get_class($widget).'">'.$view.'</div>';
				} else {
					return 'WIDGET_NOT_FOUND';
				}
			} else {
				return $id;
			}
		}

		function replaceVideo($id)
		{
			if (is_numeric($id)) {
				$video = $this->getData($id);
				if ($video->type == 'VIDEO') {
					return '<div class="video">'.Video::detectAndFit($video->code).'</div>';
				} else {
					return §('Data #{{'.$id.'}} is not a video.');
				}
			}
			return §('Video content needs to be the id of a video data type.');
		}

		function replaceGallery($id)
		{
			if (is_numeric($id)) {
				$gallery = $this->getData($id);
				if ($gallery->type == 'GALLERY') {
					return '<div class="gallery thumbs clearfix" style="width: '.($this->getOption('content.width')+5).'px; overflow: hidden;">'.$gallery->listResultThumbs().'</div>';
				} else {
					return §('Data #{{'.$id.'}} is not a gallery.');
				}
			}
			return §('Video content needs to be the id of a video data type.');
		}

		function detectTags($tags, $raw = FALSE)
		{
			if (is_string($raw)) {
				$path = $raw;
				$raw = FALSE;
			} else {
				$path = $this->a->assigned('system.tags', '', 1);
			}
			$tags = preg_split('=,=', $tags, -1, PREG_SPLIT_NO_EMPTY);
			$tagCount = count($tags);
			$collect = array();
			$i = 0;
			foreach ($tags as $tag) {
				$i++;
				$separator = false;
				$tag = trim($tag);
				if ($i < $tagCount) {
					$separator = true;
				}
				$collect[] = (object) array(
					'name' => $tag,
					'link' => $path.'/'.str_replace(' ', '+', $tag),
					'separator' => $separator
				);
			}
			if ($raw) {
				$tags = $collect;
			}
			if (!empty($collect)) {
				$tags = $this->t->get('tags.php', array(
					'tags' => $collect
				));
			}
			if (!empty($tags)) {
				return $tags;
			}
		}

		static public function suggestTags($count = 20)
		{
			$tags = array();
			$results = Database::instance()->get('SELECT p.value FROM #_index i, #_properties p WHERE p.name="tags" && p.parent=i.id && i.status>0 && i.published<'.time(), array(
				'force_array' => true
			));
			if ($results) {
				foreach ($results as $r) {
					$t = explode(',', $r->value);
					foreach ($t as $tag) {
						@$tags[trim($tag)]++;
					}
				}
				arsort($tags);
			}
			return array_slice($tags, 0, $count);
		}
		
		function detectImage($content)
		{
			if (preg_match('=<img.*src\="(.*)".*>=iU', stripslashes($content), $result)) {
				$img = $result[1];
				if (strpos($img, 'timthumb.php') !== FALSE) {
					preg_match('=src\=([^\&]*)=', $img, $result);
					return $result[1];
				}
				return $img;
			}
			return;
		}

		function pagination($id, $count, $current = 1, $limit = 10, $digits = 11)
		{
			$pages = ceil($count/$limit);

			if ($pages>1) {
				$start = 2;
				$stop = $pages-1;
				$postBreak = FALSE;
				$preBreak = FALSE;

				if ($pages > $digits && $current != $pages) {
					if ($current < $digits-2) {
						$stop = $digits-2;
						$postBreak = TRUE;
					}
					if ($current > $digits-3) {
						$stop = $current+round(($digits-5)/2);
						$start = $current-round(($digits-5)/2);
						$preBreak = TRUE;
						$postBreak = TRUE;
					}
					if ($current > $pages-($digits-3)) {
						$start = $pages-($digits-3);
						$stop = $pages-1;
						$postBreak = FALSE;
						$preBreak = TRUE;
					}
				}

				$collect = '';
				if ($current > 1) {
					$link = str_replace('//', '/', $this->a->current(array(-1, $current-1)));
					if ($current-1 == 1) {
						$link = $this->a->current(-1);
					}
					$collect .= '<a href="'.$link.'">'.§('« Newer').'</a> ';
				}

				for ($i=1; $i<=$pages; $i++) {
					if ($i == 1 || ($i >= $start && $i <= $stop) || $i == $pages) {
						if ($i == $start && $preBreak == TRUE) {
							$collect .= ' .. ';
						}
						$link = str_replace('//', '/', $this->a->current(array(-1, $i)));
						if ($i == 1) {
							$link = str_replace('//', '/', $this->a->current(-1));
						}
						if ($current == 1) {
							$link = str_replace('//', '/', $this->a->current($i, 1));
						}
						if ($i == 1 && $current == 1) {
							$link = str_replace('//', '/', $this->a->current());
						}

						if ($i == $current) {
							$collect .= ' <a href="'.$link.'" class="active">'.$i.'</a> ';
						} else {
							$collect .= ' <a href="'.$link.'">'.$i.'</a> ';
						}

						if ($i == $stop && $postBreak == TRUE) {
							$collect .= ' .. ';
						}
					}
				}

				if ($current < $pages) {
					$link = str_replace('//', '/', $this->a->current(array(-1, $current+1)));
					if ($current == 1) {
						$link = str_replace('//', '/', $this->a->current($current+1, 1));
					}
					$collect .= '<a href="'.$link.'">'.§('Older »').'</a> ';
				}

				return '<div class="plxPagination '.$id.'">'.$collect.'</div>';
			}
		}

		function httpPostRequest($host, $path, $params, $https = FALSE)
		{
			if (empty($path)) {
				$path = '/';
			}
			$data = '';
	        foreach ($params as $key => $value) {
	            $data .= '&'.$key.'='.urlencode($value);
	        }
	        $data = substr($data, 1);
	        $buffer = '';
	        if ($https) {
	        	$fp = fsockopen('ssl://'.$host, 443, $errno, $errstr, 30);
	        } else {
	        	$fp = fsockopen($host, 80, $errno, $errstr, 30);
	        }
	        if (!$fp) {
	            $buffer .= "$errstr ($errno)<br />\n";
	        } else {
	            $out  = "POST ".$path." HTTP/1.1\r\n";
	            $out .= "Host: ".$host."\r\n";
	            $out .= "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n";
	            $out .= "Content-Length: ".strlen($data)."\r\n";
	            $out .= "Connection: Close\r\n\r\n";
	            $out .= $data;
#echo µ($out);
	            fwrite($fp, $out);
	            while (!feof($fp)) {
	                $buffer .= fgets($fp, 128);
	            }
	            fclose($fp);
	        }
	        $buffer = explode("\r\n\r\n", $buffer);
	        array_shift($buffer);
	        $buffer = implode("\r\n\r\n", $buffer);
	        return $buffer;
		}

		function httpGetRequest($host, $path, $https = FALSE)
		{
			if (empty($path)) {
				$path = '/';
			}
	        $buffer = '';
	        if ($https) {
	        	$fp = fsockopen('ssl://'.$host, 443, $errno, $errstr, 30);
	        } else {
	        	$fp = fsockopen($host, 80, $errno, $errstr, 30);
	        }
	        if (!$fp) {
	            $buffer .= "$errstr ($errno)<br />\n";
	        } else {
	            $out  = "GET ".$path." HTTP/1.1\r\n";
	            $out .= "Host: ".$host."\r\n";
	            $out .= "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n";
	            $out .= "Connection: Close\r\n\r\n";

	            fwrite($fp, $out);
	            while (!feof($fp)) {
	                $buffer .= fgets($fp, 128);
	            }
	            fclose($fp);
	        }
	        $buffer = explode("\r\n\r\n", $buffer);
	        array_shift($buffer);
	        $buffer = implode("\r\n\r\n", $buffer);
	        return $buffer;
		}

		function sendMail($mail, $subject, $message, $from = '')
		{
			if (empty($from)) {
				$from = $this->getOption('site.name').' <'.$this->getOption('site.mail').'>';
			}
			$headers  = 'MIME-Version: 1.0'."\r\n";
			$headers .= 'Content-type: text/plain; charset=utf-8'."\r\n";
			$headers .= 'From: '.$from."\r\n";
			return mail($mail, $subject, $message, $headers);
		}
	}
?>
