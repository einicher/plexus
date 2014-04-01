<?php
	class Template extends Core
	{
		static $instance;
		static $cache;

		public $themeRoot;

		static function &instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		static public function connect($name, $values = '')
		{
			self::$cache['connect'][$name] = $values;
		}

		static public function disconnect($name)
		{
			unset(self::$cache['connect'][$name]);
		}

		public function get($file, array $vars = array(), $component = '')
		{
			$__file = self::locateFile($file, $component);
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
			$m = self::getFromCache($__file);
			$m = str_replace('<?', '<?php', $m);
			$m = str_replace('<?phpphp', '<?php', $m);
			$m = str_replace('<?php=', '<?php echo ', $m);
			eval('?>'.$m);
			return ob_get_clean();
		}

		static public function getFromCache($file)
		{
			if (empty(self::$cache['files'][$file])) {
				self::$cache['files'][$file] = str_replace('<?=', '<?php echo ', file_get_contents($file));
			}
			return self::$cache['files'][$file];
		}

		static public function locateFile($file, $component = '')
		{
			if (empty($component) && $component !== false) {
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

		static public function includeFromThemeRoot($file)
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

		function getThemeRoot($component = '', $append = '')
		{
			if (empty($this->themeRoot)) {
				$theme = Core::getConf('system', 'theme');
				if (empty($theme)) {
					$this->themeRoot = PLX_COMPONENTS;
				} else {
					if (file_exists(Core::getStorage($theme))) {
						$this->themeRoot = Core::getStorage($theme);
					} elseif (file_exists(PLX_STORAGE.'themes/'.$theme)) {
						$this->themeRoot = PLX_STORAGE.'themes/'.$theme;
					} else {
						$this->themeRoot = 'THEME_NOT_FOUND';
					}
				}
			}

			if (!empty($component)) {
				$component = '/'.$component;
			}
			if (!empty($append)) {
				$append = '/'.$append;
			}

			if (strpos($this->themeRoot, 'plx-components') === false) {
				return $this->themeRoot.$component.$append;
			} else {
				return $this->themeRoot.$component.'/theme'.$append;
			}
		}
	}
?>
