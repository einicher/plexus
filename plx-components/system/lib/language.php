<?php
	class Language extends Core
	{
		static $instance;
		static $files = array();
		static $file;
		static $custom = array();
		static $lang;
		static $global = array();

		static function getInstance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function __construct()
		{
			$this->tpl->includeFromThemeRoot('lang.php');
		}

		function get($text)
		{
			if (empty(self::$lang)) {
				self::$lang = Core::getConf('system', 'lang');
			}

			if (!empty(self::$lang) && self::$lang != 'en') {
				$dbg = debug_backtrace();
				$file = $dbg[0]['file'];
				if ($dbg[1]['function'] == '§') {
					#$file = $dbg[3]['file'];
					foreach ($dbg as $key => $trace) {
						if (
							strpos($trace['file'], 'template.php') === FALSE
							&& strpos($trace['file'], 'template2.php') === FALSE
							&& strpos($trace['file'], 'functions.php') === FALSE
						) {
							$file = $trace['file'];
							break;
						}
					}
				}
				preg_match('='.PLX_COMPONENTS.'([^/]*)/=', $file, $results);

				if (!empty($results[1])) {
					$file = PLX_COMPONENTS.$results[1].'/lang/'.self::$lang.'.php';

					if (isset(self::$files[$file])) {
						//already set
					} elseif (file_exists($file)) {
						self::$file = $file;
						include $file;
					} else {
						unset($file);
					}
				}

				if (empty($file)) {
					$file = PLX_SYSTEM.'lang/'.self::$lang.'.php';
					if (isset(self::$files[$file])) {
						//already set
					} elseif (file_exists($file)) {
						self::$file = $file;
						require_once $file;
					} else {
						unset($file);
					}
				}

				$key = preg_replace('={{(.*)}}=isU', '{{}}', $text);

				if (isset($file)
				 && isset(self::$files[$file])
				 && isset(self::$files[$file][$key])) {
					$text = self::replaceVariables($text, self::$files[$file][$key]);
				} else {
					$file = PLX_SYSTEM.'lang/'.self::$lang.'.php';
					if (isset($file)
					 && isset(self::$files[$file])
					 && isset(self::$files[$file][$key])) {
						$text = self::replaceVariables($text, self::$files[$file][$key]);
					}
				}

				if (isset(self::$global[self::$lang][$key])) {
					$text = self::replaceVariables($text, self::$global[self::$lang][$key]);
				}

				if (isset(self::$custom[self::$lang][$key])) {
					$text = self::replaceVariables($text, self::$custom[self::$lang][$key]);
				}
			}

			$text = preg_replace('={{(.*)}}=isU', '\\1', $text);

			return $text;
		}

		function replaceVariables($text, $replace)
		{
			if (preg_match_all('={{(.*)}}=isU', $text, $results)) {
				$i=0;
				$text = preg_replace('={{}}=ieU', '\'{{\'.$i++.\'}}\'', $replace);
				foreach ($results[1] as $key => $value) {
					$text = str_replace('{{'.$key.'}}', $value, $text);
				}
			} else {
				$text = $replace;
			}
			return $text;
		}

		function set($name, $value, $global = false)
		{
			if ($global) {
				self::$global[self::$lang][$name] = $value;
			} else {
				self::$files[self::$file][$name] = $value;
			}
		}

		static function add($lang, $orig, $trans)
		{
			self::$custom[$lang][$orig] = $trans;
		}

		static function getTranslations($id, $translations = array(), $taken = array(0))
		{
			$r = Database2::instance()->query('SELECT * FROM `#_options` WHERE name="translation" && (association='.$id.' || value='.$id.') && id NOT IN('.implode(',', $taken).')', array('force_array' => true));
			if ($r && $r->num_rows) {
				while ($f = $r->fetch_object()) {
					$taken[] = $f->id;
					$translations[$f->value] = $f->value;
					$translations[$f->association] = $f->association;
					if ($id == $f->association) {
						$translations = self::getTranslations($f->value, $translations, $taken);
					} else {
						$translations = self::getTranslations($f->association, $translations, $taken);
					}
				}
			}
			return $translations;
		}
	}
?>
