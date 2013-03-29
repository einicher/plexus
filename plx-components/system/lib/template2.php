<?php
	class Template2 extends Core
	{
		static $instance;
		public $themeRoot;

		static function instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function get($comp, $file, $vars = array())
		{
			return Template::get2(Template::locateFile($file, $comp), $vars);
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
