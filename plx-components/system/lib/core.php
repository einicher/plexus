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
		static $options; // cache
		static $debug = array();
		static $api;
		static $extendedApis = array();

		static $d; // Database2
		static $t; // Template2

		function &__get($property)
		{
			switch ($property) {
				case 'conf':
					$this->conf =& self::getConf();
					return $this->conf;
				break;

				case 'addr':
					$this->addr =& Address::getInstance();
					return $this->addr;
				break;

				case 'db':
					$this->db =& Database::getInstance($this->conf->database);
					return $this->db;
				break;

				case 'pdb':
					$this->pdb =& PlexusDataControl::instance();
					return $this->pdb;
				break;

				case 'pdc':
					$this->pdb =& PlexusDataControl::getInstance();
					return $this->pdb;
				break;

				case 'tpl':
					$this->tpl =& Template::getInstance();
					return $this->tpl;
				break;

				case 'observer':
					$this->observer =& Observer::getInstance();
					return $this->observer;
				break;

				case 'lang':
					$this->lang =& Language::getInstance();
					return $this->lang;
				break;

				case 'tools':
					$this->tools =& Tools::getInstance();
					return $this->tools;
				break;

				case 'access':
					$this->access =& Access::getInstance();
					return $this->access;
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

				case 'control':
					$this->control =& Control::getInstance();
					return $this->control;
				break;
				
				case 'components':
					$this->components = self::$components;
					return $this->components;
				break;

				case 'd':
					$this->d = Database2::instance();
					return $this->d;
				break;

				case 't':
					$this->t = Template2::instance();
					return $this->t;
				break;

				case 'api':
					$this->api = Api::instance();
					return $this->api;
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

		function getWidget($widget, $name, $options = '')
		{
			if (!isset(self::$widgets[$widget])) {
				return 'WIDGET “'.$widget.'” NOT FOUND.';
			} else {
				$class = $widget;
				$page = @end(Control::$current)->id;
				$fetch = $this->getOption('widget', $name);
				if (empty($fetch)) {
					$data = '';
				} else {
					$data = json_decode($fetch->value);
					$data->id = $fetch->id;
				}
				require_once self::$widgets[$widget]['file'];
				$widget = new $widget($name, $page, $data);
				$widget->id = $name;
				if (is_array($options)) {
					$widget->dock = (object) $options;
				}

				$container = 'div';
				if (!empty($widget->dock->container)) {
					$container = $widget->dock->container;
				}

				$content = $widget->view('template');
				/*if (empty($content)) {
					return;
				}*/

				ob_start();
?>
<<?=$container?> id="<?=$name?>" class="widget standaloneWidget">
<? if ($this->access->granted('system.editWidgets') && !empty($page)) : ?>
	<span id="<?=$name?>StandaloneEdit" class="plexusEdit plexusControls"><?=$this->lang->get('Edit')?></span>
	<script type="text/javascript">
		jQuery('#<?=$name?>StandaloneEdit').fancybox({
			href: root + 'PlexusStandaloneWidget/<?=$name?>/<?=$page?>/<?=$class?>?options=<?=urlencode(json_encode($options))?>',
			autoDimensions: false,
			centerOnScroll: true,
			overlayOpacity: 0.5,
			overlayColor: '#000',
			transitionIn: 'elastic',
			transitionOut: 'elastic',
			onComplete: function() {
				plxWidgetHtml2AjaxForm(root + 'PlexusStandaloneWidget/<?=$name?>/<?=$page?>/<?=$class?>?options=<?=urlencode(json_encode($options))?>');
				jQuery('form.plexusForm button.remove').click(function() {
					var action = jQuery('form.plexusForm').attr('action') + '&plexusRemove';
					jQuery('form.plexusForm').attr('action', action);
				});
			}
		});
	</script>
<? endif; ?>
<? if ($widget->getTitle()) : ?>
		<h1><?=$widget->getTitle()?></h1>
<? endif; ?>
		<div class="wrap">
<?=$content?>
		</div>
</<?=$container?>>
<?php
				return ob_get_clean();
			}
		}

		function getRoot($append = '')
		{
			return PLX_ROOT.$append;
		}

		function getConf()
		{
			if (empty(self::$conf)) {
				$storage = self::getStorage();
				@include_once $storage.'config.php';
				if (empty($conf)) {
					$conf->system->lang = 'en';
					$conf->system->theme = 'default';
					self::$conf = $conf;
					echo Control::setup($storage);
					exit;
				} else {
					Database::getInstance($conf->database);
					$conf->system->theme = self::getOption('site.theme');
					$conf->system->lang = self::getOption('site.language');
					self::$conf = $conf;
				}
			}

			$args = func_get_args();

			if (empty($args)) {
				return self::$conf;
			} else {
				return eval('return @self::$conf->'.implode('->', $args).';');
			}
		}

		function getStorage($append = '') // copy changes to plx-resources/plx-cache ! its redundant for performance reasons
		{
			if (empty(self::$storage)) {
				self::$storage = PLX_STORAGE;
				if (file_exists(PLX_MULTI)) {
					self::$storage = PLX_MULTI.$_SERVER['SERVER_NAME'].'/';
				}
			}
			return self::$storage.$append;
		}

		function error($message, $cache = FALSE)
		{
			self::$errors .= $message.'<br />';
			if ($cache) {
				@$_SESSION['errors'] .= $message.'<br />';
			}
			return TRUE;
		}

		function info($message, $cache = FALSE)
		{
			self::$infos .= $message.'<br />';
			if ($cache) {
				@$_SESSION['infos'] .= $message.'<br />';
			}
			return TRUE;
		}
		
		function registerType($type, $class, $file, $label, $options = array())
		{
			self::$types[$type] = array(
				'class' => $class,
				'file' => $file,
				'label' => $label,
				'options' => $options
			);
		}

		function getType($type)
		{
			if (isset(self::$types[strtoupper($type)])) {
				return (object) self::$types[strtoupper($type)];
			}
		}

		/** @deprecated use getData instead
		 *
		 */
		function type($type, $mixed = null)
		{
			return $this->getData($type, $mixed);
		}

		function getData($type, $mixed = null)
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
			$type = self::getType($type);
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

		function resource($name)
		{
			$loadFile = PLX_RESOURCES.$name.'/load.php';
			if (empty(self::$resources[$loadFile])) {
				if (file_exists($loadFile)) {
					require_once $loadFile;
				}
				self::$resources[$loadFile] = TRUE;
			}
		}

		function overwriteOption($name, $value = '')
		{
			if (empty($value)) {
				unset(self::$options[$name]);
			} else {
				self::$options[$name] = $value;
			}
		}

		function getOptionExact($name, $value, $association)
		{
			return Database::fetch('SELECT * FROM '.Database::table('options').' WHERE name="'.Database::escape($name).'" && association="'.Database::escape($association).'" && value="'.Database::escape($value).'"');
		}

		function getOption($name, $association = '', $object = false)
		{
			if (empty($association) && isset(self::$options[$name])) {
				return self::$options[$name];
			}
			
			if (is_numeric($name)) {
				return Database::fetch('SELECT * FROM '.Database::table('options').' WHERE id="'.Database::escape($name).'"');
			}

			$sql = 'SELECT * FROM '.Database::table('options').' WHERE name="'.Database::escape($name).'"';
			if (!empty($association)) {
				$sql .= ' && association="'.Database::escape($association).'"';
			}
			Database::clear($sql);
			$query = Database::query($sql);
			$count = Database::count();
			if ($count > 1) {
				$options = array();
				while ($fetch = Database::fetch('', 1)) {
					$options[] = $fetch;
				}
				return self::$options[$name] = $options;
			} else {
				$fetch = Database::fetch();
				if (empty($fetch)) {
					if ($object === 2) {
						return array();
					} else {
						return false;
					}
				} elseif ($object === 2) {
					return array($fetch);
				} elseif (!$object && empty($fetch->association)) {
					return $fetch->value;
				} else {
					return $fetch;
				}
			}
		}

		function setOption($name, $value, $association = '', $multi = FALSE)
		{
			if (is_numeric($name)) {
				Database::query('UPDATE '.Database::table('options').' SET value="'.Database::escape($value).'" WHERE id='.$name);
				return $name;
			}
			if ($multi) {
				$check = FALSE;
			} else {
				$check = Core::getOption($name, $association, true);
			}
			if (empty($check)) {
				Database::query('INSERT INTO '.Database::table('options').' SET name="'.Database::escape($name).'", association="'.Database::escape($association).'", value="'.Database::escape($value).'"');
				$id = Database::lastId();
			} else {
				Database::query('UPDATE '.Database::table('options').' SET value="'.Database::escape($value).'" WHERE id='.$check->id);
				$id = $check->id;
			}
			return $id;
		}

		function delOption($mixed, $value = '', $association = '')
		{
			if (is_numeric($mixed)) {
				return Database::query('DELETE FROM '.Database::table('options').' WHERE id='.$mixed);
			} else {
				if (!empty($association) && !empty($value)) {
					return Database::query('DELETE FROM '.Database::table('options').' WHERE name="'.Database::escape($mixed).'" && value="'.Database::escape($value).'" && association="'.Database::escape($association).'"');
				} elseif (!empty($association)) {
					return Database::query('DELETE FROM '.Database::table('options').' WHERE name="'.Database::escape($mixed).'" && association="'.Database::escape($association).'"');
				} else {
					return Database::query('DELETE FROM '.Database::table('options').' WHERE name="'.Database::escape($mixed).'"');
				}
			}
		}

		function imageScaleLink($src, $width = 468, $height = '', $root = '')
		{
			if (empty($root)) {
				$root = Address::getInstance()->root;
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
