<?php
	class Core
	{
		static $conf;
		static $system;
		static $errors;
		static $infos;
		static $types = array();
		static $resources;
		static $widgets = array();
		static $control;
		static $storage;
		static $components;
		static $preferences = array();
		static $ajaxCalls;
		static $cacheOptions;
		static $debug = array();
		static $api;
		static $extendedApis = array();

		function &__get($property)
		{
			switch ($property) {
				case 'access':
					$this->access =& Access::instance();
					return $this->access;
				break;

				case 'api':
					$this->api = Api::instance();
					return $this->api;
				break;

				case 'conf':
					$this->conf =& self::getConf();
					return $this->conf;
				break;

				case 'control':
					$this->control =& Control::instance();
					return $this->control;
				break;
				
				case 'components':
					$this->components = self::$components;
					return $this->components;
				break;

				case 'tools':
					$this->tools =& Tools::instance();
					return $this->tools;
				break;

				case 'user':
					$this->user =& Access::$user;
					return $this->user;
				break;

				case 'system':
					if (empty(self::$system)) {
						$system = new stdClass;
						$system->name = 'Plexus';
						$system->version = '0.6';
						$system->home = 'http://plexus-cms.org/';
						self::$system = $system;
					}
					return $this->system =& self::$system;
				break;

				case 'a':
					$this->a =& Address::instance();
					return $this->a;
				break;

				case 'd':
					$this->d = Database::instance($this->conf->database);
					return $this->d;
				break;

				case 'l':
					$this->l =& Language::instance();
					return $this->l;
				break;

				case 'o':
					$this->o =& Observer::instance();
					return $this->o;
				break;

				case 't':
					$this->t = Template::instance();
					return $this->t;
				break;

				default:
					$dbg = debug_backtrace();
					trigger_error('[Core] You need to declare <b>'.$property.'</b> in <b>'.get_called_class().'</b> in file <b>'.$dbg[0]['file'].'</b> on line <b>'.$dbg[0]['line'].'</b><br /><br />');
			}
		}

		function extend($class, $new, $file)
		{
			foreach (self::$types as $type => $data) {
				if ($data['class'] == $class) {
					self::$types[$type]['class'] = $new;
					self::$types[$type]['file'] = $file;
				}
			}
		}

		function registerWidget($name, $class, $file)
		{
			$dbg = debug_backtrace();
			if (isset($dbg[0]['file'])) {
				preg_match('='.PLX_COMPONENTS.'([^/]*)/=', $dbg[0]['file'], $results);
				if (!empty($results[1])) {
					$component = $results[1];
				}
			}

			Core::$widgets[$class] = array(
				#'name' => $name,
				'class' => $class,
				'file' => PLX_COMPONENTS.$component.'/'.$file
			); 
		}

		static public function &getConf()
		{
			if (empty(self::$conf)) {
				$storage = self::getStorage();
				@include_once $storage.'config.php';
				if (empty($conf)) {
					@$conf->system->lang = 'en';
					@$conf->system->theme = 'default';
					@$conf->system->timezone = 'GMT';
					self::$conf = $conf;
					echo Control::setup($storage);
					exit;
				} else {
					Database::instance($conf->database);
					@$conf->system->lang = self::getOption('site.language');
					if (empty($conf->system->lang)) {
						@$conf->system->lang = 'en';
					}
					@$conf->system->theme = self::getOption('site.theme');
					if (empty($conf->system->theme)) {
						@$conf->system->theme = 'default';
					}
					@$conf->system->timezone = self::getOption('site.timezone');
					if (empty($conf->system->timezone)) {
						@$conf->system->timezone = 'GMT';
					}
					self::$conf = $conf;
				}
			}

			$args = func_get_args();

			if (empty($args)) {
				return self::$conf;
			} else {
				$conf = eval('return @self::$conf->'.implode('->', $args).';');
				return $conf;
			}
		}

		static public function getStorage($append = '') // copy changes to plx-resources/plx-cache ! its redundant for performance reasons
		{
			if (empty(self::$storage)) {
				self::$storage = PLX_STORAGE;
				if (file_exists(PLX_MULTI)) {
					self::$storage = PLX_MULTI.$_SERVER['SERVER_NAME'].'/';
				}
			}
			return self::$storage.$append;
		}

		public static function error($message, $cache = FALSE)
		{
			self::$errors .= $message.'<br />';
			if ($cache) {
				@$_SESSION['errors'] .= $message.'<br />';
			}
			return TRUE;
		}

		public static function info($message, $cache = FALSE)
		{
			self::$infos .= $message.'<br />';
			if ($cache) {
				@$_SESSION['infos'] .= $message.'<br />';
			}
			return TRUE;
		}
		
		public static function registerType($type, $class, $file, $label, $options = array())
		{
			self::$types[$type] = array(
				'class' => $class,
				'file' => $file,
				'label' => $label,
				'options' => $options
			);
		}

		public static function getDataType($type)
		{
			if (isset(self::$types[strtoupper($type)])) {
				return (object) self::$types[strtoupper($type)];
			}
		}

		public static function getData($type, $mixed = null)
		{
			if (is_numeric($type)) {
				return PlexusDataControl::getDataById($type);
			}
			$assign = false;
			if (is_object($type)) {
				if ($mixed) {
					$assign = true;
				}
				$mixed = $type;
				$type = $type->type;
			}
			$type = self::getDataType($type);
			if (!empty($type)) {
				require_once $type->file;
				if ($assign) {
					$type = new $type->class($mixed, true);
				} else {
					$type = new $type->class($mixed);
				}
				return $type;
			}
		}

		public static function resource($name)
		{
			$loadFile = PLX_RESOURCES.$name.'/load.php';
			if (empty(self::$resources[$loadFile])) {
				if (file_exists($loadFile)) {
					require_once $loadFile;
				}
				self::$resources[$loadFile] = TRUE;
			}
		}

		public static function overwriteOption($name, $value = '')
		{
			if (empty($value)) {
				unset(self::$cacheOptions[$name]);
			} else {
				self::$cacheOptions[$name] = $value;
			}
		}

		public static function getOptionExact($name, $value, $association)
		{
			return Database::fetch('SELECT * FROM '.Database::table('options').' WHERE name="'.Database::escape($name).'" && association="'.Database::escape($association).'" && value="'.Database::escape($value).'"');
		}

		static public function getOption($name, $association = '', $object = false)
		{
			if (empty($association) && isset(self::$cacheOptions[$name])) {
				return self::$cacheOptions[$name];
			}
			
			if (is_numeric($name)) {
				return Database::instance()->get('SELECT * FROM `#_options` WHERE id="'.Database::instance()->escape($name).'"');
			}

			$sql = 'SELECT * FROM '.Database::table('options').' WHERE name="'.Database::escape($name).'"';
			if (!empty($association)) {
				$sql .= ' && association="'.Database::escape($association).'"';
			}

			$get = Database::instance()->get($sql);

			if (count($get) > 1) {
				return self::$cacheOptions[$name] = $get;
			} else {
				if (empty($get)) {
					if ($object === 2) {
						return array();
					} else {
						return false;
					}
				} elseif ($object === 2) {
					return array($fetch);
				} elseif (!$object && empty($get->association)) {
					return $get->value;
				} else {
					return $get;
				}
			}
		}

		public static function setOption($name, $value, $association = '', $multi = FALSE)
		{
			if (is_numeric($name)) {
				Database::instance()->query('UPDATE `#_options` SET value="'.Database::instance()->escape($value).'" WHERE id='.$name);
				return $name;
			}
			if ($multi) {
				$check = FALSE;
			} else {
				$check = Core::getOption($name, $association, true);
			}
			if (empty($check)) {
				$q = Database::instance()->query('INSERT INTO '.Database::table('options').' SET name="'.Database::instance()->escape($name).'", association="'.Database::instance()->escape($association).'", value="'.Database::instance()->escape($value).'"');
				$id = Database::instance()->insert_id;
			} else {
				Database::instance()->query('UPDATE '.Database::table('options').' SET value="'.Database::instance()->escape($value).'" WHERE id='.$check->id);
				$id = $check->id;
			}
			return $id;
		}

		public static function delOption($mixed, $value = '', $association = '')
		{
			if (is_numeric($mixed)) {
				return Database::instance()->query('DELETE FROM '.Database::table('options').' WHERE id='.$mixed);
			} else {
				if (!empty($association) && !empty($value)) {
					return Database::instance()->query('DELETE FROM '.Database::table('options').' WHERE name="'.Database::instance()->escape($mixed).'" && value="'.Database::instance()->escape($value).'" && association="'.Database::instance()->escape($association).'"');
				} elseif (!empty($association)) {
					return Database::instance()->query('DELETE FROM '.Database::table('options').' WHERE name="'.Database::instance()->escape($mixed).'" && association="'.Database::instance()->escape($association).'"');
				} else {
					return Database::instance()->query('DELETE FROM '.Database::table('options').' WHERE name="'.Database::instance()->escape($mixed).'"');
				}
			}
		}

		function imageScaleLink($src, $width = 468, $height = '', $root = '')
		{
			if (empty($root)) {
				$root = Address::instance()->root;
			}
			$s = Core::getStorage();
			$src = str_replace($s, '', $src);
			if (strpos($src, 'plx-cache/') !== FALSE) {
				$src = parse_url($src);
				$src = str_replace('plx-cache/', '', $src['path']);
			}
			$path = $root.'plx-cache/'.$src;
			$args = array();
			if (!empty($width)) $args[] = 'w='.$width; 
			if (!empty($height)) $args[] = 'h='.$height;
			if (!empty($args)) {
				$path .= '?'.implode('&', $args);
			}
			return $path;
		}

		function addPreference($name, $call, &$actor = '')
		{
			$n = $this->addr->transform(strtolower($name));
			self::$preferences[$n] = (object) array(
				'name' => $name,
				'address' => $n,
				'call' => $call,
				'actor' => &$actor
			);
			$this->addr->assign('system.preferences.'.$n, $n, array(&$actor, $call), 'system.preferences');
		}

		function registerAjaxCall($call, &$actor = '')
		{
			self::$ajaxCalls[$call] = &$actor;
		}

		function debug($name)
		{
			Core::$debug[] = '['.$name.']	'.(microtime(1)-PLX_START);
		}

		function component($component)
		{
			if (isset(Control::$componentsCallback[$component])) {
				return Control::$componentsCallback[$component];
			} else {
				return;
			}
		}

		function getComponentClass($class, $instanciate = true)
		{
			$backtrace = debug_backtrace();
			foreach ($backtrace as $key => $trace) {
				if (isset($trace['file']) && stripos($trace['file'], 'plx-components/') !== false && stripos($trace['file'], 'system/') === false) {
					$l = explode('/', $trace['file']);
					foreach ($l as $k => $v) {
						if ($v == 'plx-components') {
							$component = $l[$k+1];
							require_once PLX_COMPONENTS.$component.'/lib/'.classNameToFileName($class).'.php';
							if ($instanciate) {
								return new $class;
							}
						}
					}
				}
			}
		}

		function extendPlexusAPI($section, $file, $callback)
		{
			self::$extendedApis[$section] = array(
				'section' => $section,
				'file' => $file,
				'callback' => $callback
			);
		}
	}
?>
