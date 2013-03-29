<?php
	class Template extends Core // Hybrid !!!
	{
		static $instance;
		static $cache;
		static $cache2;

		function getInstance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function includeFromThemeRoot($file)
		{
			$custom1 = Core::getStorage(Core::getConf('system', 'theme').'/'.$file);
			if (file_exists($custom1)) {
				include_once $custom1;
			} else {
				$custom2 = PLX_STORAGE.'themes/'.Core::getConf('system', 'theme').'/'.$file;
				if (file_exists($custom2)) {
					include_once $custom2;
				}
			}
		}

		function locateFile($file, $component = '')
		{
			if (empty($component)) {
				$dbg = debug_backtrace();
				if (isset($dbg[1]['file'])) {
					preg_match('='.PLX_COMPONENTS.'([^/]*)/=', $dbg[1]['file'], $results);
					if (!empty($results[1])) {
						$component = $results[1];
					}
				}
			}

			if (Core::getConf('system', 'theme') != '') {
	            if (empty($component)) {
	                $path = Core::getStorage(Core::getConf('system', 'theme').'/system/'.$file);
	                if (!file_exists($path)) {
	                	$path = PLX_STORAGE.'themes/'.Core::getConf('system', 'theme').'/system/'.$file;
	                }
	            } else {
	                $path = Core::getStorage(Core::getConf('system', 'theme').'/'.$component.'/'.$file);
	                if (!file_exists($path)) {
	                	$path = PLX_STORAGE.'themes/'.Core::getConf('system', 'theme').'/'.$component.'/'.$file;
	                }
	            }
			}

            if (empty($path) || !file_exists($path)) {
                if (empty($component)) {
                    $path = PLX_SYSTEM.'theme/'.$file;
                } else {
                    $path = PLX_COMPONENTS.$component.'/theme/'.$file;
                }
            }
            return $path;
		}

		function connect($name, $values = '')
		{
			self::$cache['connect'][$name] = $values;
		}

		function disconnect($name)
		{
			unset(self::$cache['connect'][$name]);
		}

		function getCached($file, $sub = '')
		{
			if (strpos($file, '/') === FALSE) {
				$file = self::locateFile($file);
			}
			if (empty($sub)) {
				self::$cache['cut'][$file];
			}
			return self::$cache['cut'][$file][$sub];
		}

		function cut($file, $sub, $array = '', $arrayOnly = FALSE, $repeat = FALSE)
		{
			if (strpos($file, '/') === FALSE) {
				$file = self::locateFile($file);
			}
			self::fetch($file);
			preg_match_all('=<tpl:'.$sub.'>(.*)<'.$sub.':tpl>=isU', self::$cache['file'][$file]['markup'], $results);
			if ($repeat) {
				@self::$cache['cut'][$file][$sub] .= self::render(@$results[1][0], $file, $array, $arrayOnly);
			} else {
				self::$cache['cut'][$file][$sub] = self::render(@$results[1][0], $file, $array, $arrayOnly);
			}
			return self::$cache['cut'][$file][$sub];
		}

		function repeat($file, $sub, array $array = array(), bool $arrayOnly = NULL)
		{
			return self::cut(self::locateFile($file), $sub, $array, $arrayOnly, TRUE);
		}

		function get($file, $mixed = '', bool $arrayOnly = NULL)
		{
			$array = array();
			$component = '';
			if (is_string($mixed)) {
				$component = $mixed;
			}
			if (is_array($mixed)) {
				$array = $mixed;
			}

			if (is_array($file)) {
				$file = self::locateFile($file[0], $file[1]);
			}
			if (strpos($file, '/') === FALSE) {
				$file = self::locateFile($file);
			}

			self::fetch($file);
			self::$cache['file'][$file]['markup'] = self::render(self::$cache['file'][$file]['markup'], $file, $array, $arrayOnly);
			$tpl = self::evaluate(self::$cache['file'][$file]['markup']);
			return $tpl;
		}

		function get2($__file, array $vars = array())
		{
			if (isset(self::$cache['connect']) && is_array(self::$cache['connect'])) {
				extract(self::$cache['connect']);
			}
			if (is_array($__file)) {
				$__file = self::locateFile($__file[0], $__file[1]);
			}
			if (strpos($__file, '/') === FALSE) {
				$__file = self::locateFile($__file);
			}
			extract($vars);
			ob_start();
			$m = self::getFromCache2($__file);
			eval('?>'.$m);
			return ob_get_clean();
		}

		function getFromCache2($file)
		{
			if (empty(self::$cache2[$file])) {
				self::$cache2[$file] = str_replace('<?=', '<?php echo ', file_get_contents($file));
			}
			return self::$cache2[$file];
		}

		function set($file, $name = '', $value = '')
		{
			$file = self::locateFile($file);

			if (empty($value)) {
				if (empty($name)) {
					unset(self::$cache['cut'][$file]);
					unset(self::$cache['file'][$file]);
				} else {
					unset(self::$cache['cut'][$file][$name]);
				}
			} else {
				self::$cache['cut'][$file][$name] = $value;
			}
		}

		function fetch($file)
		{
			if (!isset(self::$cache['file'][$file])) {
				self::$cache['file'][$file]['markup'] = self::detect($file);
			}
			return self::$cache['file'][$file]['markup'];
		}

		function render($content, $file, $array, $arrayOnly)
		{
			foreach (self::$cache['file'][$file]['surrounded'] as $name) {
				if (empty(self::$cache['cut'][$file][$name])) {
					$content = preg_replace('=<tpl\:'.$name.'>(.*)<'.$name.'\:tpl>=isU', '', $content);
				} else {
					$content = preg_replace('=<tpl\:'.$name.'>(.*)<'.$name.'\:tpl>=isU', str_replace('\\', '\\\\', self::$cache['cut'][$file][$name]), $content);
				}
			}	
			$content = self::evaluate($content, $array, $arrayOnly);
			return $content;
		}

		function evaluate($m, $array = '', $arrayOnly = FALSE)
		{
			if ($arrayOnly == FALSE && isset(self::$cache['connect'])) {
				extract(self::$cache['connect']);
			}

			if (!empty($array)) {
				extract($array);
			}

			ob_start();
			eval('?>'.$m);
			$m = ob_get_clean();

			return $m;
		}

		/** Detects the Subtemplates within the Template Files
		 * @return object $tpl Contains detected Stuff
		 */
		function detect($file)
		{
			$markup = str_replace('<?=', '<?php echo ', file_get_contents($file));
			$parsed = self::parse($markup);
			$surrounded = array();
			$rebuild = array();

			foreach ($parsed as $current) {
				$startJumper = preg_match_all("=<tpl([^>]*)>=isU", $current, $starts);
				$stopJumper = preg_match_all("=<\/tpl>=iU", $current, $stops);

				if ($startJumper) {
					preg_match_all('=([^\=]*)\=\"([^\"]*)\"=iU', trim($starts[1][0]), $aTagAttribute);

					foreach ($aTagAttribute[1] as $value => $name) {
						$attributes[trim($name)] = $aTagAttribute[2][$value];
					}

					$current = str_replace($starts[0][0], '<tpl:'.$attributes['name'].'>', $current);
					$stopTag = '<'.$attributes['name'].':tpl>';

					$collection[] = $stopTag;
					$surrounded[] = $attributes['name'];
				}

				if ($stopJumper) {
					$current = str_replace('</tpl>', array_pop($collection), $current);
				}

				$rebuild[] = $current;
			}

			#array_shift($surrounded);
			self::$cache['file'][$file]['surrounded'] = $surrounded;
			return implode('', $rebuild);
		}

		function parse($text, $tag1auf = '', $tag1zu = '')
		{
			if(empty($tag1auf)) $tag1auf = '<';
			if(empty($tag1zu)) $tag1zu = '>';
			$text = str_replace("\r",'',$text);
			$länge = strlen($text);
			$astg = array();
			$mz = 0;
			$tag_offen = 0;

			for ($z = 0; $z < $länge; $z++) {
				$zeichen = substr($text, $z, 1);
				if (empty($astg[$mz])) $astg[$mz] = '';
				if ($zeichen == $tag1auf AND $tag_offen == 0 AND strlen($astg[$mz]) > 0) { $mz++; }
	
				if (empty($astg[$mz])) $astg[$mz] = '';
				$astg[$mz] .= $zeichen;
	
				if ($zeichen == $tag1auf) $tag_offen = 1;
				if ($zeichen == $tag1zu) $tag_offen = 0;
				if ($tag_offen == 0 AND $zeichen == $tag1zu) $mz++;
			}

			return $astg;
		}
	}
?>