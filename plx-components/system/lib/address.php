<?php
	class Address extends Core
	{
		static $instance;
		static $reserved = array();
		static $occupied = array();
		static $dependencies = array();

		public $root;
		public $home;
		public $path;
		public $levels;
		public $constants;

		static public function &instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function __construct()
		{
			$root = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
			if ($root == '/') {
				$this->home = 'http://'.$_SERVER['SERVER_NAME'];
				$this->path = $_SERVER['REQUEST_URI'];
			} else {
				$this->home = substr('http://'.$_SERVER['SERVER_NAME'].$root, 0, -1);
				$this->path = substr($_SERVER['REQUEST_URI'], strlen($root));
			}
			$this->path = parse_url('http://'.str_replace('//', '/', 'www.example.com/'.$this->path), PHP_URL_PATH);
			if (substr($this->path, 0, 1) == '/') {
				$this->path = substr($this->path, 1);
			}
			$this->levels = explode('/', $this->path);
			foreach ($this->levels as $name) {
				$this->root .= '../';
			}
			if (!empty($this->root)) $this->root = substr($this->root, 3);
			if (!empty($_GET['ajax'])) {
				$this->root = $_GET['ajax'];
			}
			if (!empty($this->levels[0])) {
				array_unshift($this->levels, '');
			}
		}

		function getLevel($level, $levels = '')
		{
			if (empty($levels)) {
				$levels = $this->levels;
				if (isset($levels[1]) && $levels[1] == Control::$language) {
					unset($levels[1]);
					$levels = array_merge($levels);
				}
			}

			if (is_numeric($level)) {
				if ($level >= 0) {
					#$level--;
					if (isset($levels[$level])) {
						return @$levels[$level];
					} else {
						return FALSE;
					}
				} else {
					$level = count($levels)+$level;
					if ($level >= 0) {
						return @$levels[$level];
					}
				}
			}

			if ($level == 'first') {
				if (isset($levels[1])) {
					return @$levels[1];
				} else {
					return FALSE;
				}
			}

			if ($level == 'last') {
				$last = count($levels)-1;
				if (isset($levels[$last])) {
					return @$levels[$last];
				} else {
					return FALSE;
				}
			}
		}

		// deprecated
		function register()
		{
			trigger_error('Address::register is deprecated as of plexus version 0.6 '.print_r(func_get_args(), true), E_USER_ERROR);
		}

		function assign($name, $address, $call, $dependency = '', $exit = false, $takeOverMainLoop = false)
		{
			self::$reserved[$name] = array(
				'name' => $name,
				'address' => $address,
				'call' => $call,
				'dependency' => $dependency,
				'exit' => $exit,
				'takeOverMainLoop' => $takeOverMainLoop
			);
			if (is_array(self::$reserved[$name]['dependency'])) {
				$array = self::$reserved[$name]['dependency'];
				self::$reserved[$name]['dependency'] = $array[0];
				foreach ($array as $value) {
					self::$reserved[$name][$value] = true;
				}
			}
		}

		function assigned($name, $wildcard = array(), $prefix = 0)
		{
			if (!is_array($wildcard)) {
				$wildcard = array($wildcard);
			}
			if (!is_string($name)) {
				echo µ($name);
				$dbg = debug_backtrace();
				echo µ($dbg[0]);
			}
			if (isset(self::$reserved[$name])) {
				$address = self::$reserved[$name]['address'];
				if ($address == '*') {
					$address = str_replace(' ', '+', array_pop($wildcard));
				}
				if (empty(self::$reserved[$name]['dependency'])) {
					foreach (self::$occupied as $type => $assigned) {
						if ($assigned == $name && !empty($wildcard)) {
							$address .= '/'.array_pop($wildcard);
						}
					}
					if (count(Control::$languages) > 1 && !empty(Control::$language)) {
						$address = Control::$language.'/'.$address;
					}
					if ($prefix === 0) {
						return $this->getRoot().$address;
					} elseif ($prefix === 1) {
						return $this->getHome().$address;
					} elseif ($prefix === 2) {
						return $address;
					} else {
						return $prefix.$address;
					}
				} elseif (is_numeric(self::$reserved[$name]['dependency'])) {
					if (self::$reserved[$name]['dependency'] < 0) {
						$c = $this->current();
						return $c == './' ? $c.$address : $c.'/'.$address;
					} else {
						return 'NUMERIC_LEVELS_ARE_DEPRECATED';
					}
				} else {
					return $this->assigned(self::$reserved[$name]['dependency'], $wildcard, $prefix).'/'.$address;
				}
			}
			return 'ADDRESS_NOT_FOUND:'.$name;
		}

		function isAssigned($level, $levels, $cache)
		{
			foreach (self::$reserved as $name => $reserved) {
				$check = count($levels) == 1 ? 0 : 1;
				if (!isset($reserved['dependency'])) { //catch old reserved stuff and exit
					echo µ($reserved);
					exit;
				}
				if (is_numeric($reserved['dependency']) && $reserved['dependency'] < 0) {
					$check = count($levels)+$reserved['dependency'];
					if ($check < 0) {
						continue;
					}
//echo µ($name.'('.count($levels).'+'.$reserved['dependency'].')'.$check);
				}
				if (
					isset($levels[$level])
					&& $levels[$level] == urlencode($reserved['address'])
					&& $level == $check
				) {
					if ($reserved['exit']) {
						define('PLX_CONTROL_EXIT', true);
					}
					foreach (self::$occupied as $type => $registered) {
						if ($registered == $name && !empty($levels[$level+1])) {
							$fetch = $this->db->fetch('SELECT * FROM '.$this->db->table('index').' WHERE parent=0 AND type="'.$type.'" AND address="'.$levels[$level+1].'"');
							if (!empty($fetch)) {
								self::$dependencies[] = array(
									'name' => 'dataType.'.$type,
									'address' => $levels[$level+1]
								);
								return $this->getData($fetch);
							}
						}
					}
					self::$dependencies[] = $reserved;
					return $reserved;
				}
				if (!empty(self::$dependencies)) {
					$prev = end(self::$dependencies);
					if (isset($levels[$level]) && ($levels[$level] == urlencode($reserved['address']) || $reserved['address'] == '*') && $prev['name'] === $reserved['dependency']) {
						if (!empty($reserved['exit'])) {
							define('PLX_CONTROL_EXIT', true);
						}
						self::$dependencies[] = $reserved;
						return $reserved;
					}
				}
			}
			return false;
		}

		function rewrite($name, $newAddress)
		{
			self::$reserved[$name]['address'] = self::transform($newAddress);
		}

		function getRoot($append = '')
		{
			if (empty($this->root)) {
				return './'.$append;
			} else {
				return $this->root.$append;
			}
		}

		function getHome($append = '')
		{
			if (empty($append)) {
				$append = '/';
			} else {
				$append = '/'.$append;
			}
			return $this->home.$append;
		}

		function getPath($append = '')
		{
			return $this->path.'/'.$append;
		}

		function getLink($id, $address = '')
		{
			$fetch = $this->d->get('SELECT id,type,parent,address,language FROM `#_index` WHERE id='.$id);
			if (empty($fetch->address)) {
				if (!empty($address)) {
					return $address;
				} else {
					if (count(Control::$languages) > 1) {
						return $fetch->language;
					} else {
						return;
					}
				}
			} else {
				if (!empty($address)) {
					$address = '/'.$address;
				}
				if ($fetch->parent == 0 && !empty(self::$occupied[$fetch->type])) {
					$fetch->address = $this->assigned(self::$occupied[$fetch->type]).'/'.$fetch->address;
				}
				$address = $fetch->address.$address;
				if ($fetch->parent > 0) {
					return $this->getLink($fetch->parent, $address);
				} else {
					if (count(Control::$languages) > 1) {
						return $fetch->language.'/'.$address;
					} else {
						return $address;
					}
				}
			}
		}

		function getRootLink($id)
		{
			$link = $this->getRoot($this->getLink($id));
			if (empty($link)) {
				$link = $this->getHomeLink($id);
			}
			return $link;
		}

		function getHomeLink($id)
		{
			return $this->getHome($this->getLink($id));
		}

		function current($link = '', $force = FALSE, $getParams = '', $home = 0, $excludeParams = '')
		{
			if (is_array($link)) {
				$append = $link[1];
				$link = $link[0];
			}
			if (empty($link)) {
				if ($home) {
					$return = $this->getHome($this->path);
				} else {
					$return = $this->getRoot($this->path);
				}
			} elseif (is_numeric($link) && !$force) {
				if ($link < 0) {
					$link = substr($link, 1);
					if ($link < 0) {
						$link = 0;
					}
				}

				$new = $this->levels;
				unset($new[0]);
				for ($i=0; $i<$link; $i++) {
					array_pop($new);
				}
				$return = $this->getRoot(implode('/', $new));
			} else {
				if (!empty($this->path)) {
					$link = '/'.$link;
				}
				if ($home) {
					$return = $this->getHome(str_replace('//', '/', $this->path.$link));
				} else {
					$return = $this->getRoot(str_replace('//', '/', $this->path.$link));
				}
			}

			if (empty($return)) {
				$return =  $this->getHome();
			}

			if (isset($append)) {
				$return .= '/'.$append;
			}

			return $this->httpGetVars($return, $getParams, $excludeParams);
		}

		function getAddress($name)
		{
			if (isset(self::$reserved[$name]['address'])) {
				return self::$reserved[$name]['address'];
			}
		}

		function occupy($type, $reserved)
		{
			self::$occupied[$type] = $reserved;
			self::$reserved['dataType.'.$type] = array(
				'name' => 'dataType.'.$type,
				'dependency' => $reserved,
				'address' => '*'
			);
		}

		function isSubPageOf($ancestor, $descendend)
		{
			if (is_array($ancestor)) {
				$cond = FALSE;
				foreach ($ancestor as $id) {
					if ($this->isSubPageOf($id, $descendend)) {
						$cond = TRUE;
					}
				}
				return $cond;
			}

			$id = $descendend;
			$descendends = array($id);
			while (1) {
				$fetch = $this->d->get('SELECT parent FROM `#_index` WHERE id='.$id);
				if ($fetch->parent == 0) {
					break;
				}
				$id = $fetch->parent;
				$descendends[] = $id;
			}
			if (in_array($ancestor, $descendends)) {
				return TRUE;
			}
			return FALSE;
		}

        function isInCurrentAddress($address)
        {
            return in_array($address, $this->levels);
        }

		function assignedIsActive($name)
		{
			$reserved = self::$reserved[$name];
			if (empty($reserved['dependency'])) {
				return $this->getLevel(1) == $reserved['address'];
			} elseif (is_numeric($reserved['dependency']) && $reserved['dependency'] < 0) {
				$key = count($this->levels)+$reserved['dependency'];
				return $this->getLevel($key) == $reserved['address'];
			}
		}

        function isActive($path, $exact = true)
        {
		    $path = str_replace('../', '', $path);
		    $path = str_replace('./', '', $path);
		    if ($exact) {
		    	if ($this->path == $path) {
		    		return true;
		    	}
        	} else {
		    	if (substr($this->path, 0, strlen($path)) == $path) {
		    		return true;
		    	}
        	}
        	return false;
        }

		function httpGetVars($url, $getParams = '', $excludeParams = '')
		{
			if (!empty($_GET) || !empty($getParams)) {
				$get = $_GET;
				if (is_array($getParams)) {
					$get = array_merge($get, $getParams);
				}
				if (is_array($excludeParams)) {
					foreach ($excludeParams as $key) {
						unset($get[$key]);
					}
				}
				if (empty($get)) {
					
				} else {
					if (get_magic_quotes_gpc()) {
						$url .= '?'.http_build_query(array_map('stripslashes', $get));
					} else {
						$url .= '?'.http_build_query($get);
					}
				}
			}
			return $url;
		}

		static public function transform($string, $strict = FALSE)
		{
			$string = trim($string);

			$url = preg_replace('=á|à|ã|â|ă=i', 'a', $string);
			$url = preg_replace('=Á|À|Ã|Â|Ă=i', 'A', $url);
			$url = preg_replace('=é|è|ê|ě=i', 'e', $url);
			$url = preg_replace('=É|È|Ê|Ě=i', 'E', $url);
			$url = preg_replace('=í|ì|ĩ|î=i', 'i', $url);
			$url = preg_replace('=Í|Ì|Ĩ|Î=i', 'I', $url);
			$url = preg_replace('=ó|ò|õ|ô|ø=i', 'o', $url);
			$url = preg_replace('=Ó|Ò|Õ|Ô|Ø=i', 'O', $url);
			$url = preg_replace('=ú|ù|ũ|û=i', 'u', $url);
			$url = preg_replace('=Ú|Ù|Ũ|Û=i', 'U', $url);
			$url = preg_replace('=ś|ŝ|š=i', 's', $url);
			$url = preg_replace('=Ś|Ŝ|Š=i', 'S', $url);
			$url = preg_replace('=ć|ç|č=i', 'c', $url);
			$url = preg_replace('=Ć|Ç|Č=i', 'C', $url);
			$url = preg_replace('=ł|ĺ|ľ=i', 'l', $url);
			$url = preg_replace('=Ł|Ĺ|Ľ=i', 'L', $url);
			$url = preg_replace('=ä|æ=i', 'ae', $url);
			$url = preg_replace('=Ä|Æ=i', 'Ae', $url);
			$url = preg_replace('=ü=i', 'ue', $url);
			$url = preg_replace('=Ü=i', 'Ue', $url);
			$url = preg_replace('=ö=i', 'oe', $url);
			$url = preg_replace('=Ö=i', 'Oe', $url);
			$url = preg_replace('=ß=i', 'sz', $url);

			$url = preg_replace('=[^a-zA-Z0-9\.\-\ ]=', '', $url);
			$url = trim($url);
			$url = preg_replace('=[\ ]+=', '-', $url);
			$url = preg_replace('=[\-]+=', '-', $url);
			if (substr($url, -1) == '-') {
				$url = substr($url, 0, -1);
			}
			if (substr($url, 0, 1) == '-') {
				$url = substr($url, 1);
			}
			if ($strict) {
				$url = strtolower($url);
			}

			if (substr($string, 0, 1) == '/') {
				$url = $this->root($url);
			}
			return $url;
		}
	}
?>
