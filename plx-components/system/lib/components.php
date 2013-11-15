<?php
	class Components extends Core
	{
		static $instance;

		static function instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function index($level, $levels, $cache)
		{
			$message = '';

			switch (@$levels[3]) {
				case 'install':
					if (empty($levels[4]) || empty($levels[5])) {
						$this->install();
					} else {
						$m = $this->processInstall('install', $levels[4], $levels[5], $this->install());
						if ($m['status'] == 1) {
							header('Location: '.$this->addr->assigned('system.preferences.components'));
							exit;
						} else {
							$this->install($m['message']);
						}
					}
				break;

				case 'remove':
					$message = $this->remove($levels[4]);
					$this->overview($message);
					echo $this->tpl->get('admin-components.php', array(
						'message' => $message
					));
					exit;
				break;

				case 'upgrade':
					$m = $this->upgrade($levels[4]);
					if ($m['status'] == 1) {
						$this->overview($m['message']);
						echo $this->tpl->get('admin-components.php', array(
							'message' => $m['message']
						));
						exit;
					}
					if ($m['status'] == 0) {
						$this->overview($m['message'], true);
						echo $this->tpl->get('admin-components.php', array('message' => 1));
						exit;
					}
				break;

				case 'activate':
					$message = $this->activate($levels[4], $levels[5]);
					$this->overview($message);
					echo $this->tpl->get('admin-components.php', array(
						'message' => $message
					));
					exit;
				break;

				case 'deactivate':
					$message = $this->deactivate($levels[4]);
					$this->overview($message);
					echo $this->tpl->get('admin-components.php', array(
						'message' => $message
					));
					exit;
				break;

				default: $this->overview();
			}

			return $this->tpl->get('admin-components.php', array(
				'message' => $message
			));
		}

		function overview($message = '', $error = false)
		{
			if (!empty($message) && !$error) {
				$this->tpl->cut('admin-components.php', 'message', array(
					'message' => $message
				));
			}

			$this->checkForUpdates();

			$versions = array();
			$upgrades = array();
			$available = json_decode($this->getOption('plexus.updates.available'));
			if (!empty($available)) {
				foreach ($available as $upgrade) {
					$upgrades[] = $upgrade->name;
					$versions[] = $upgrade->version;
				}
			}
			$components = $this->detectComponents();
			asort($components);
			foreach ($components as $c => $class) {
				$e = new $class(TRUE);
				$e->class = $class;
				$e->file = $c;
				if (in_array($class, Control::$activeComponents)) {
					$this->tpl->cut('admin-components.php', 'deactivate', array('component' => $e));
				} else {
					$this->tpl->cut('admin-components.php', 'activate', array('component' => $e));
				}
				$upgrade = array_search($c, $upgrades);
				if (is_array($upgrades) && $upgrade !== FALSE) {
					$this->tpl->repeat('admin-components.php', 'upgrade', array('component' => $c));
					$this->tpl->repeat('admin-components.php', 'upgradeAvailable', array('component' => $e, 'version' => $versions[$upgrade]));
				}
				$this->tpl->repeat('admin-components.php', 'component', array('component' => $e));
				$this->tpl->set('admin-components.php', 'activate');
				$this->tpl->set('admin-components.php', 'deactivate');
				$this->tpl->set('admin-components.php', 'upgrade');
				$this->tpl->set('admin-components.php', 'upgradeAvailable');
			}
			$system = array_search('plexus', $upgrades);
			if (is_array($upgrades) && in_array('plexus', $upgrades)) {
				$this->tpl->cut('admin-components.php', 'plexusUpgrade', array('newVersion' => $versions[$system]));
			}
			unset($upgrades[$system]);
			if (is_array($upgrades) && !empty($upgrades)) {
				if (count($upgrades) == 1) {
					$this->tpl->cut('admin-components.php', 'overviewUpgradesSingular', array('upgrades' => count($upgrades)));
				} else {
					$this->tpl->cut('admin-components.php', 'overviewUpgradesPlural', array('upgrades' => count($upgrades)));
				}
				$this->tpl->cut('admin-components.php', 'overviewUpgrades', array('upgrades' => count($upgrades)));
			}
			$this->tpl->cut('admin-components.php', 'overview', array(
				'error' => $error,
				'message' => $message
			));
		}

		function install($message = '')
		{
			$results = array();

			if (!empty($_POST)) {
				$results = json_decode(file_get_contents($this->system->home.'components/JSON/'.urldecode($_POST['searchComponent'])));
				if (empty($results->matches)) {
					$this->tpl->cut('admin-components.php', 'noResults');
				} else {
					foreach ($results->results as $key => $result) {
						$this->tpl->repeat('admin-components.php', 'result', array(
							'key' => $key,
							'result' => $result
						));
					}
					$this->tpl->cut('admin-components.php', 'results', array(
						'results' => $results
					));
				}
			}

			$level4 = $this->addr->getLevel(4);
			if (empty($results) && !empty($level4)) {
				$results = json_decode(file_get_contents($this->system->home.'components/JSON/'.urldecode($level4).'?exactMatch'));
			}

			$this->tpl->cut('admin-components.php', 'install', array(
				'searchComponent' => @$_POST['searchComponent'],
				'message' => $message
			));

			return $results;
		}

		function activate($component, $file)
		{
			return $this->activation($component, true, $file);
		}

		function deactivate($component)
		{
			return $this->activation($component, false);
		}

		function activation($class, $mode, $file = '')
		{
			$components = $this->getOption('system.activeComponents');
			if (empty($components)) {
				$components = array();
			} else {
				$components = json_decode($components);
			}

			if ($mode) {
				$components[] = (object) array(
					'class' => $class,
					'file' => $file
				);
				Control::$activeComponents[] = $class;
				$message = $this->lang->get('The component has been successfully activated.');
			} else {
				foreach ($components as $key => $c) {
					if ($c->class == $class) {
						unset($components[$key]);
						unset(Control::$activeComponents[array_search($class, Control::$activeComponents)]);
					}
				}
				$components = array_merge($components);
				$message = $this->lang->get('The component has been successfully deactivated.');
			}

			$this->setOption('system.activeComponents', json_encode($components));

			return $message;
		}

		function upgrade($component)
		{
			$results = json_decode(file_get_contents($this->system->home.'components/JSON/'.urldecode($component).'?exactMatch'));
			if (!empty($results)) {
				$source = base64_encode($results->results[0]->source);
				return $this->processInstall('upgrade', $component, $source, $results);
			}
		}

		function remove($homedir)
		{
			$target = PLX_COMPONENTS.$homedir;
			$this->deleteRecursive($target);
			return $this->lang->get('The component has been successfully removed.');
		}

		function processInstall($type, $component, $source, $results)
		{
			$status = 1;
			if (is_writable(PLX_COMPONENTS)) {
				if ($component == 'plexus') {
					$source = json_decode(file_get_contents($this->system->home.'Components/JSON/plexus?exactMatch'));
					if (empty($source->results[0]->source)) {
						return array(
							'message' => $this->lang->get('Failed to get the link to the plexus core source file.'),
							'status' => 0
						);
					} else {
						$source = $results->results[0]->source;
						$script = @file_get_contents($source);
						if (empty($script)) {
							return array(
								'message' => $this->lang->get('Failed to fetch the plexus core source from „{{'.$source.'}}“.'),
								'status' => 0
							);
						} else {
							$path = opendir('.');
							$excludes = array('.', '..', 'plx-storage', 'plx-components');
							while ($c = readdir($path)) {
								if (!in_array($c, $excludes)) {
									if (is_dir($c)) {
										$this->deleteRecursive($c);
									} else {
										unlink($c);
									}
								}
							}
							$this->deleteRecursive('./plx-components/system');
							$target = '';
							$process = eval($script);
							$this->system->version = $results->results[0]->version;
							return $this->plxComponents(1, array('', 'PlexusComponents'), array(), TRUE);
						}
					}
				} else {
					$target = PLX_COMPONENTS.$component.'/';
					if (file_exists($target)) {
						@chmod($target, 0777);
						$this->deleteRecursive($target);
					}
					if (!@mkdir($target, 0777)) {
						return array(
							'message' => $this->lang->get('Target directory {{'.$target.'}} could not be created. Maybe you need to set write permissions to the plx-components directory?'),
							'status' => 0
						);
					}
					@chmod($target, 0777);

					$script = @file_get_contents(@base64_decode($source));
					if (empty($script)) {
						return array(
							'message' => $this->lang->get('Download of install script failed.'),
							'status' => 0
						);
					} else {
						$process = eval($script);
						if ($process === FALSE) {
							return array(
								'message' => $this->lang->get('Install failed.').(empty($message) ? '' : '<p>'.$message.'</p>'),
								'status' => 0
							);
						} else {
							if ($type == 'upgrade') {
								return array(
									'message' => $this->lang->get('Upgrade of component {{<strong>'.$results->results[0]->name.'</strong>}} was successfull.'),
									'status' => 1
								);
							} else {
								return array(
									'message' => $this->lang->get('Component successfully installed. You need to enable it in the overview if you want to use it.'),
									'status' => 1
								);
							}
						}
					}
				}
			} else {
				if ($type == 'upgrade') {
					return array(
						'message' => $this->lang->get('Your {{<strong>plx-components</strong>}} directory is not writable. Automatic install will not work. Fix this problem or go to {{<a href="'.$results->results[0]->href.'" class="external" target="_blank">'.$this->lang->get('the components page').'</a>}} to download the new version on your own and install it by hand.'),
						'status' => 0
					);
				} else {
					return array(
						'message' => $this->lang->get('Your {{<strong>plx-components</strong>}} directory is not writable. Automatic install will not work. Fix this problem or go to {{<a href="'.$results->results[0]->href.'" class="external" target="_blank">'.$this->lang->get('the components page').'</a>}} to download the component and install it by hand by extracting amd movin it to the plx-components folder.'),
						'status' => 0
					);
				}
			}
		}

		function checkForUpdates()
		{
			$this->setOption('plexus.components.last.check', time());
			$components = self::detectComponents();
			$request = '?components=plexus:'.$this->system->version.';';
			foreach ($components as $c => $class) {
				$e = new $class(TRUE);
				if (!empty($e->version)) {
					$request .= $c.':'.$e->version.';';
				}
			}
			if (!empty($this->conf->preReleases)) {
				$request .= '&dev=1';
			}
			$check = file_get_contents($this->system->home.'components/CheckForUpdates'.$request);
			if ($check) {
				$check = json_decode($check);
				if ($check->status == 1) {
					$this->setOption('plexus.updates.available', json_encode($check->comps));
				} else {
					$this->delOption('plexus.updates.available');
				}
			}
		}

		function detectComponents()
		{
			$components = array();
			$p = PLX_COMPONENTS;
			$d = opendir($p);
			while ($c = readdir($d)) {
				if ($c != '.' && $c != '..' && is_dir($p.$c)) {
					$componentMainClassFile = $p.$c.'/lib/'.$c.'.php';
					if (file_exists($componentMainClassFile)) {
						require_once $componentMainClassFile;
						$class = str_replace('_', '-', $c);
						$class = str_replace(' ', '-', $class);
						$class = explode('-', $class);
						$class = array_map('ucfirst', $class);
						$class = implode('', $class);
						if (class_exists($class, FALSE)) {
							$components[$c] = $class;
						}
					}
				}
			}
			return $components;
		}

		function plxPack($level, $levels, $cache)
		{
			if (empty($levels[2])) {
				$collect = '<p>Which of the installed components do you want to pack?</p><ul>';
				$p = PLX_COMPONENTS;
				$dir = opendir($p);
				while ($c = readdir($dir)) {
					if ($c != '.' && $c != '..') {
						$collect .= '<li><a href="'.$this->addr->current($c).'">'.$c.'</a></li>';
					}
				}
				$collect .= '</ul>';
				return new Page('Plexus Packaging', $collect);
			} elseif ($levels[2] == 'plexus') {
				header('content-type: text/plain; charset=utf-8');
				echo $this->packPlexus();
				exit;
			} else {
				header('content-type: text/plain; charset=utf-8');
				echo $this->pack(PLX_COMPONENTS.$levels[2], PLX_COMPONENTS.$levels[2].'/', $levels[2]);
				exit;
			}
		}

		function packPlexus()
		{
	        $p = opendir('./');
	        $excluded = array('.', '..', '.bzr', '.bzignore', 'plx-storage', 'plx-components', '.settings', '.buildpath', '.project', '.bzrignore');
	        while($c = readdir($p)) {
				if (!in_array($c, $excluded)) {
				        $this->packSwitch($c, '');
				}
	        }
	        $this->packSwitch('plx-components', '', TRUE);
	        $this->packSwitch('plx-components/system', '');
		}

		function pack($p, $r, $c = '')
		{
			$dir = opendir($p);
			while ($c = readdir($dir)) {
				if ($c != '.' && $c != '..' && $c != '.bzr') {
					$current = $p.'/'.$c;
					$this->packSwitch($current, $r);
				}
			}
		}

		function packSwitch($current, $r, $oneLevelOnly = FALSE)
		{
			$path = str_replace($r, '', $current);
			if (is_dir($current)) {
				echo 'if (!file_exists($target.\''.$path.'\')) {
	mkdir($target.\''.$path.'\');
	chmod($target.\''.str_replace($r, '', $current).'\', 0777);
}

';
				if (!$oneLevelOnly) {
					echo $this->pack($current, $r);
				}
			} else {
				$extensions = array('php', 'txt', 'htm', 'html', 'css', 'xml', 'xsl', 'js', 'htaccess');
				$ext = strtolower(end(explode('.', $current)));
				if (in_array($ext, $extensions)) {
					echo '$f = fopen($target.\''.$path.'\', \'w\');
fwrite($f, \''.str_replace("'", "\'", str_replace('\\', '\\\\', file_get_contents($current))).'\');
fclose($f);
chmod($target.\''.$path.'\', 0777);

';
				} else {
					echo '$f = fopen($target.\''.$path.'\', \'w\');
fwrite($f, base64_decode(\''.base64_encode(file_get_contents($current)).'\'));
fclose($f);
chmod($target.\''.$path.'\', 0777);

';
				}
			}
			return $path;
		}

		function deleteRecursive($p)
		{
			if (!file_exists($p)) {
				return;
			}
			$dir = opendir($p);
			while ($c = readdir($dir)) {
				if ($c != '.' && $c != '..') {
					$path = $p.'/'.$c;
					if (is_dir($path)) {
						$this->deleteRecursive($path);
					} else {
						unlink($path);
					}
				}
			}
			rmdir($p);
		}
	}
?>
