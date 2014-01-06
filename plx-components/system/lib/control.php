<?php
	class Control extends Core
	{
		static $instance;
		static $current = array();
		static $activeComponents = array();
		static $componentsCallback = array();
		static $activeComponentsDirs = array();
		static $language = '';
		static $languages = array();
		static $standalone = false;
		static $overwrite;
		static $content;
		static $cache = true;

		public $paginationActive = false;
		public $paginationUsed = false;
		public $paginationPage = 0;

		static public function &instance()
		{
			if (empty(self::$instance)) {
				return new self;
			}
			return self::$instance;
		}

		final public function __construct()
		{
			$this->debug('Control::construct START');

			if ($this->a->path == 'plxCheckForRewrittenUrls') {
				exit('TRUE');
			}

			if (empty(self::$instance)) {
				date_default_timezone_set($this->getConf('system', 'timezone'));
				
				if (!empty($_SESSION['infos'])) {
					Core::$infos = $_SESSION['infos'];
					unset($_SESSION['infos']);
				}
				if (!empty($_SESSION['errors'])) {
					Core::$errors = $_SESSION['errors'];
					unset($_SESSION['errors']);
				}

				// REGISTER SYSTEM DATA TYPES
				$this->registerType('USER', 'User', PLX_SYSTEM.'lib/user.php', §('User'));
				$this->registerType('GROUP', 'Group', PLX_SYSTEM.'lib/group.php', §('Group'));
				$this->registerType('PAGE', 'Page', PLX_SYSTEM.'lib/page.php', §('Page'));
				$this->registerType('POST', 'Post', PLX_SYSTEM.'lib/post.php', §('Post'));
				$this->registerType('MICRO', 'Micro', PLX_SYSTEM.'lib/micro.php', §('Micropost'));
				$this->registerType('LINK', 'Link', PLX_SYSTEM.'lib/link.php', §('Link'));
				$this->registerType('IMAGE', 'Image', PLX_SYSTEM.'lib/image.php', §('Image'));
				$this->registerType('GALLERY', 'Gallery', PLX_SYSTEM.'lib/gallery.php', §('Gallery'));
				$this->registerType('VIDEO', 'Video', PLX_SYSTEM.'lib/video.php', §('Video'));
				$this->registerType('FILE', 'File', PLX_SYSTEM.'lib/file.php', §('File'));

				// CONTENT CONTROLS
				$this->a->assign('system.new', §('new'), array('ContentControls::instance()', 'plxNew'));
				$this->a->assign('system.new.type', '*', array('ContentControls::instance()', 'plxNew'), 'system.new');
				$this->a->assign('system.create', §('create'), array('ContentControls::instance()', 'plxCreate'), array(-1, 'preceded_empty'));
				$this->a->assign('system.create.type', §('create'), array('ContentControls::instance()', 'plxCreate'), array(-2, 'preceded_empty'));
				$this->a->assign('system.edit', §('edit'), array('ContentControls::instance()', 'plxEdit'), -1);
				$this->a->assign('system.translate', §('translate'), array('ContentControls::instance()', 'plxTranslate'), -1);
				$this->a->assign('system.translate.language', '*', array('ContentControls::instance()', 'plxTranslate'), -2);
				$this->a->assign('system.copy', §('copy'), array('ContentControls::instance()', 'plxCopy'), -1);

				// SYSTEM CONTROL
				$this->a->assign('system.login', §('login'), array('System::instance()', 'login'), -1);
				$this->a->assign('system.logout', §('logout'), array('System::instance()', 'logout'), -1);
				$this->a->assign('system.users', §('users'), array('System::instance()', 'plxUsers'));
				$this->a->assign('system.users.password', §('lost-password'), array('System::instance()', 'plxUsersPassword'));
				$this->a->assign('system.groups', §('groups'), array('System::instance()', 'plxGroups'));

				$this->a->assign('system.tags', §('tags'), array('System::instance()', 'tags'));
				$this->a->assign('system.tags.detail', '*', array('System::instance()', 'tags'), 'system.tags');
				$this->a->assign('system.search', §('search'), array('System::instance()', 'search'));
				$this->a->assign('system.search.pattern', '*', array('System::instance()', 'search'), 'system.search');
				$this->a->assign('system.trackback', 'trackback', array('Trackback::instance()', 'control'), -1);

				$this->a->assign('system.permalink', 'permalink', array('System::instance()', 'permalink'), '', true);
				$this->a->assign('system.cache', 'plx-cache', array('System::instance()', 'plxCache'), '', true);
				$this->a->assign('system.style', 'style.css', array('System::instance()', 'getCss'), array('', 'preceded_empty'), true);
				$this->a->assign('system.feed', 'atom.xml', array('System::instance()', 'getAtom'), '', true);
				$this->a->assign('system.sitemap', 'sitemap.xml', array('System::instance()', 'getSitemap'), '', true);
				$this->a->assign('system.favicon', 'favicon.ico', array('System::instance()', 'getFavicon'), '', true);

				$this->a->assign('system.ajax', 'plxAjax', array(&$this, 'getAjax'), '', true);
				$this->a->assign('system.export', 'plx-export', array(&$this, 'plxExport'), '', true);
				$this->a->assign('system.api', 'plx-api', array('Api::instance()', 'control'), '', false, true);

				$this->a->assign('system.addWidget', 'PlexusAddWidget', array('WidgetControl::instance()', 'addWidget'), '', true);
				$this->a->assign('system.editWidget', 'PlexusEditWidget', array('WidgetControl::instance()', 'editWidget'), '', true);
				$this->a->assign('system.standaloneWidget', 'PlexusStandaloneWidget', array('WidgetControl::instance()', 'standaloneWidget'), '', true);

				$this->a->assign('plexus.pack', 'plx-pack', array('Components::instance()', 'plxPack'));

				$this->a->assign('system.database', 'plx-database', array('PlexusDatabase::instance()', 'control'));
				$this->a->assign('system.database.edit', 'edit', array('PlexusDatabase::instance()', 'control'), 'system.database');
				$this->a->assign('system.database.edit.type', '*', array('PlexusDatabase::instance()', 'control'), 'system.database.edit');
				$this->a->assign('system.database.delete', 'delete', array('PlexusDatabase::instance()', 'control'), 'system.database');
				$this->a->assign('system.database.delete.ids', '*', array('PlexusDatabase::instance()', 'control'), 'system.database.delete');
				$this->a->assign('system.database.type', '*', array('PlexusDatabase::instance()', 'control'), 'system.database');

				$this->a->assign('system.preferences', 'plx-preferences', array('Preferences::instance()', 'control'));
				$this->a->assign('system.preferences.languages', 'languages', array('Preferences::instance()', 'control'), 'system.preferences');

				$this->a->assign('system.preferences.components', 'components', array('Preferences::instance()', 'control'), 'system.preferences');
				$this->a->assign('system.preferences.components.install', 'install', array('Preferences::instance()', 'control'), 'system.preferences.components');
				$this->a->assign('system.preferences.components.activate', 'activate', array('Components::instance()', 'index'), 'system.preferences.components', true, true);
				$this->a->assign('system.preferences.components.deactivate', 'deactivate', array('Components::instance()', 'index'), 'system.preferences.components', true, true);
				$this->a->assign('system.preferences.components.remove', 'remove', array('Components::instance()', 'index'), 'system.preferences.components');

				$this->a->assign('system.preferences.cache', 'cache', array('Preferences::instance()', 'control'), 'system.preferences');
				$this->a->assign('system.preferences.cache.clear', 'clear', array('Preferences::instance()', 'control'), 'system.preferences.cache');
				$this->a->assign('system.preferences.trackbacks', 'trackbacks', array('Preferences::instance()', 'control'), 'system.preferences');
				$this->a->assign('system.preferences.blockedIps', 'blocked-ips', array('Preferences::instance()', 'control'), 'system.preferences');

				// MAKE SOME DATA TYPES OCCUPY ADDRESSES
				$this->a->occupy('USER', 'system.users');
				$this->a->occupy('GROUP', 'system.groups');

				// REDIRECT GET SEARCH PATTERN TO REWRITTEN SEARCH URL
				if (isset($_GET['pattern']) && $this->a->assignedIsActive('system.search')) {
					header('HTTP/1.1 301 Moved Permanently');
					header('Location:'.$this->a->assigned('system.search.pattern', $_GET['pattern']));
					exit;
				}

				// REGISTER SOME METHODS TO GET USED WITH AJAX
				$this->registerAjaxCall('getDock', $this);
				$this->registerAjaxCall('analyseLink', $this);
				$this->registerAjaxCall('multiUpload', $this);
				$this->registerAjaxCall('load', $this);
				$this->registerAjaxCall('reloadPanel', $this);
				$this->registerAjaxCall('plxFormDeleteFile', $this);

				// REGISTER WIDGETS
				$this->registerWidget(§('Simple Text'), 'SimpleTextWidget', 'lib/widget-simple-text.php');
				$this->registerWidget(§('XHTML Markup'), 'XHTMLMarkupWidget', 'lib/widget-xhtml-markup.php');
				$this->registerWidget(§('Simple Banner'), 'SimpleBannerWidget', 'lib/widget-simple-banner.php');
				$this->registerWidget(§('Display Gallery'), 'GalleryWidget', 'lib/widget-gallery.php');
				$this->registerWidget(§('Menu'), 'MenuWidget', 'lib/widget-menu.php');
				$this->registerWidget(§('Tag Cloud/Tag List'), 'TagCloudWidget', 'lib/widget-tag-cloud.php');
				$this->registerWidget(§('Site Feed'), 'SiteFeedWidget', 'lib/widget-site-feed.php');
				$this->registerWidget(§('Search'), 'SearchWidget', 'lib/widget-search.php');

				// REGISTER RIGHTS FOR ACCESS CONTROL
				$this->access->registerRight('system.showPanel', 'Get system panel displayed');
				$this->access->registerRight('system.manageConnect', 'Manage Plexus Connect');
				$this->access->registerRight('system.create', 'May create new data objects on given addresses');
				$this->access->registerRight('system.new', 'May create new data objects with automated addresses');
				$this->access->registerRight('system.edit', 'May edit data objects');
				$this->access->registerRight('system.copy', 'May copy data objects');
				$this->access->registerRight('system.delete', 'May delete data objects');
				$this->access->registerRight('system.editOwnData', 'May edit/delete own data objects');
				$this->access->registerRight('system.edit.advanced', 'See the advanced section in edit forms');
				$this->access->registerRight('system.edit.docks', 'create/edit/delete widgets in docks');

				// CONNECT OBSERVERS
				$this->o->connect('site.getHeader', 'loadJavascriptDefaults', $this); // setzt var root
				$this->o->connect('data.onSaveReady', 'publishToTheWorld', $this);
				$this->o->connect('data.onSaveReady', 'Trackback::instance()->manageTrackbacks');

				// TRIGGER SETUP IF NECESSAIRY
				$setup = new Setup;
				if (!$setup->checkAdmin()) {
					echo $setup->get();
					exit;
				}

				// GET AVAILABLE LANGUAGES
				self::$languages = $this->getLanguages();

				// CHECK FOR LOGIN
				if (!empty($_POST['plexusLogin'])) {
					if ($this->a->getLevel(-1) == $this->a->getAddress('system.login')) {
						$this->access->login($_POST['login'], $_POST['password'], @$_POST['remember']);
					} else {
						if ($this->access->login($_POST['login'], $_POST['password'], @$_POST['remember'], FALSE)) {
							header('Location:'.$this->a->current());
							exit;
						}
					}
				}

				// CHECK FOR LOGGED IN USER
				$this->access->check();

				// LOAD RESOURCES
				$this->resource('jqueryui');
				$this->resource('fancybox');
				if ($this->access->granted()) {
					$this->resource('tinymce');
				}

				// INIT ACITVATED COMPONENTS
				$this->initComponents();

				// LET THE DESIGN THEME OVERWRITE OUR SETTINGS NOW
				//$this->t->includeFromThemeRoot('control.php');
				$hostControlFile = $this->getStorage('control.php');
				if (file_exists($hostControlFile)) {
					include_once $hostControlFile;
				}

				self::$instance =& $this;
			}

			if (!empty(self::$instance) && empty($_SERVER['REMOTE_ADDR']) || Preferences::instance()->isBlockedIP($_SERVER['REMOTE_ADDR'])) {
				exit(§('Sorry sweety, but your ip address “{{'.$_SERVER['REMOTE_ADDR'].'}}” is blocked on this website.'));
			}

			$this->debug('Control::construct READY');
			return self::$instance;
		}

		public function run($mixed, $parent = 0)
		{
			$this->debug('Control::run START');
			if (!empty(self::$overwrite)) {
				return self::$overwrite;
			}

			if (is_string($mixed)) {
				if (substr($mixed, 0, 1) !== '/') {
					$mixed = '/'.$mixed;
				}
				$levels = explode('/', $mixed);
			} elseif (is_object($mixed)) {
				$levels = (array) $mixed;
			} elseif (is_array($mixed)) {
				$levels = $mixed;
			} else {
				trigger_error('Given argument could not be processed in Control::run()', E_USER_WARNING);
			}

			$draft = '';
			if ($this->access->granted()) {
				$draft = ' || (status=0 && author='.$this->access->getUser('id').')';
			}

			$publish = ' && published <= '.time();
			if ($this->access->granted()) {
				$publish = ' && (published <= '.time().' || (published > '.time().' && author='.$this->access->getUser('id').'))';
			}

			$cache = array();
			$additional = '';

			$notReal = array();
			foreach (Core::$types as $type => $properties) {
				if (!empty($properties['options']['noRealAddress'])) {
					$notReal[] = $type;
				}
			}

			// check for languages
			$language = '';
			foreach (self::$languages as $prefix => $lang) {
				if (isset($levels[1]) && $levels[1] == $prefix) {
					self::$language = $prefix;
					unset($levels[1]);
					$levels = array_values($levels);
					break;
				}
			}
			if (empty($levels[1]) && count(self::$languages) > 1 && empty(self::$language)) {
				if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
					echo $this->t->get('system', 'home-language.php', array(
						'languages' => self::$languages
					));
					exit;
				} else {
					$browserLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
					foreach ($browserLanguages as $l) {
						$l = explode(';', $l);
						$l = explode('-', $l[0]);
						$l = $l[0];
						if (isset(self::$languages[$l])) {
							header('Location:'.$this->a->getHome($l));
							exit;
						}
					}
				}
			}
			$language = ' && language="'.Database::escape(self::$language).'"';

			$this->debug('Control::run loop START');

			// SYSTEM MAIN LOOP
			$lvs = $levels;
			if (isset($levels[2]) && empty($levels[2])) {
				$current = '';
			} else {
				foreach ($levels as $level => $address) {
					if (!empty($next)) {
						$current = $next;
						unset($next);
					} else {
						$current = $this->a->isAssigned($level, $lvs, $cache);
if (isset($_GET['debug'])) {
	echo µ($current);
}
					}

					if (defined('PLX_CONTROL_EXIT')) {
						if (is_array($current)) {
							$current = $this->processCallback($current['call'], $level, $levels, $cache);
						}
						return $current;
					}

					if (is_array($current) && !empty($current['takeOverMainLoop'])) {
						break;
					}

					if (is_object($current) && !empty($current->id)) {
						self::$current[] = $cache[] = $current;
						$parent = $current->id;
						continue;
					}

					if (is_array($current)) {
						$cache[] = $current;
					}

					if (empty($current)) {
						$sql = 'SELECT * FROM `#_index` WHERE (parent=?'.$additional.') && address=? && (status>0'.$draft.') '.$publish.' '.$language;
						$current = $this->d->getPrepared($sql, 'is', $parent, urldecode($address));
						$additional = '';
						if (empty($current)) {
							$next = $this->a->isAssigned($level+1, $lvs, $cache);
							if ($next && isset($next['preceded_empty'])) {
								$nexted = true;
								continue;
							} else {
								if (isset($nexted)) {
									$current = end($cache);
									unset($nexted);
								}
								#break;
							}
						} else {
							if (isset(Address::$occupied[$current->type]) && (
								!isset($levels[$level-1]) || $this->a->getAddress(Address::$occupied[$current->type]) != $levels[$level-1]
							)) {
								unset($current);
								break;
							}
							if (in_array($current->type, $notReal)) {
								unset($current);
								break;
							}
							self::$current[] = $cache[] = $current;
							$this->o->notify('system.loop', $current);
							$parent = $current->id;
						}
					}
					if (empty($current) && is_numeric($address) && $level == (count($levels)-1)) {
						$current = end($cache);
						$this->paginationActive = true;
						$this->paginationPage = $address;
					}
					if (empty($current)) {
						break;
					}
					if (empty($address) && empty($this->a->multihost)) {
						$additional = ' OR parent=0';
					}
				}

				if (empty($address) && empty($this->a->multihost)) {
					$additional = ' OR parent=0';
				}
			}

			$this->debug('Control::run loop READY');

			if (is_array($current)) {
				if (empty($current['call'])) {
					$current = '';
				} else {
					$current = $this->processCallback($current['call'], $level, $levels, $cache);
					if ($this->paginationActive && !$this->paginationUsed) {
						$current = null;
					}
				}
			}

			$this->debug('Control::run processCallback READY');

			if (is_object($current) && empty($current->id)) {
				$this->debug('Control::run READY pre1');
				return $current;
			} elseif (!empty($current)) {
				$current = $this->getData($current);
				$current->view(); // pagination in widgets
				if ($this->paginationActive && !$this->paginationUsed) {
					$current = null;
				}
			}

			if (empty($current) || $current->noRealAddress) {
				$current = new Error404;
			}

			if (empty($current->language)) {
				self::$language = Core::getOption('site.language');
			} else {
				self::$language = $current->language;
			}

			$this->debug('Control::run READY');

			return $current;
		}

		public function view()
		{
			self::$content = $this->run($this->a->levels);
			if (defined('PLX_CONTROL_EXIT')) {
				return self::$content;
			}
			$this->t->connect('content', self::$content);
			$site = new Site(self::$content);
			$this->t->connect('site', $site);
			if (isset($_GET['ajax'])) {
				$return = $site->getContent();
			} elseif (isset($_GET['popup'])) {
				$return = $this->t->get('popup.php');
			} elseif (self::$standalone) {
				$return = self::$content->view();
			} else {
				$customView = $this->t->getThemeRoot('system', 'view-'.self::$content->id.'.php');
				if (file_exists($customView)) {
					$return  = $this->t->get('system', 'view-'.self::$content->id.'.php');
				} else {
					$return  = $this->t->get('index.php');
				}
			}

			return $this->o->notify('system.final.output', $return);
		}

		function initComponents()
		{
			$active = array();
			$components = $this->getOption('system.activeComponents');
			if (!empty($components)) {
				$components = json_decode($components);
				if (!is_array($components)) {
					return;
				}
				foreach ($components as $c) {
					if (on_exist_require(PLX_COMPONENTS.$c->file.'/lib/'.$c->file.'.php')) {
						self::$componentsCallback[$c->class] = new $c->class;
						$active[] = $c->class;
						self::$activeComponentsDirs[] = $c->file;
					}
				}
			}
			self::$activeComponents = $active;
		}

		function processCallback($current, $level, $levels, $cache)
		{
			if (is_object($current[0])) {
				$current = call_user_func_array(array($current[0], $current[1]), array($level, $levels, $cache));
			} elseif (is_string($current[0])) {
				if (strpos($current[0], '::') === FALSE) {
					if (isset($current[2])) {
						$object = new $current[0]($current[2]);
					} else {
						$object = new $current[0];
					}
					$current = call_user_func_array(array($object, $current[1]), array($level, $levels, $cache));
				} else {
					$current[0] = eval('return '.$current[0].';');
					$current = call_user_func_array(array($current[0], $current[1]), array($level, $levels, $cache));
				}
			}
			return $current;
		}

		public function get($id)
		{
			return $this->pdb->getDataById($id);
		}

		function loadJavascriptDefaults($siteHeader, &$content)
		{
			ob_start();
			echo $siteHeader;
?>
		<script type="text/javascript">
			plxRoot = '<?=$this->a->getRoot()?>';
			plxHome = '<?=$this->a->getHome()?>';
			plxId = <?= empty($content->id) ? 0 : $content->id ?>;
			root = plxRoot; //deprecated as of 0.4.1, use plxRoot instead!
		</script>
		<script type="text/javascript" src="<?=$this->a->getRoot(PLX_SYSTEM.'theme/plexus.js')?>"></script>
<?php
			return ob_get_clean();
		}

		static public function setup($host)
		{
			return new Setup($host);
		}

		function getAjax($level, $levels, $cache)
		{
			$call = $this->a->getLevel(2, $levels);
			$addr =& Address::instance();
			if (empty($_GET['ajax'])) {
				$addr->root = '';
			} else {
				$addr->root = $_GET['ajax'];
			}
			if (isset(Core::$ajaxCalls[$call])) {
				return Core::$ajaxCalls[$call]->$call($levels);
			}
		}

		function getDock($levels) // Ajax
		{
			$dock = new Dock(@$levels[3], @$levels[4]);
			array_pop($this->a->levels); // sonst wird zahl als pagination gewertet
			if (isset($_GET['options'])) {
				$dock->options = json_decode(stripslashes(urldecode($_GET['options'])));
				if (!empty($dock->options)) {
					foreach ($dock->options as $n => $value) {
						$dock->$n = '';
						$dock->$n =& $dock->options->$n;
					}
				}
			}
			return $dock->view(1);
		}

		function reloadPanel($levels)
		{
			$c = $this->type($levels[3]);
			$s = new Site($c);
			$this->t->connect('content', $c);
			$this->t->connect('site', $s);
			return $s->panel();
		}

		function analyseLink($levels)
		{
			header('content-type: text/plain; charset=utf-8');
			$json = Link::analyse($_GET['url']);
			$json = json_encode($json);
			return $json;
		}

		function multiUpload($levels)
		{
			$_FILES['file'] = $_FILES['Filedata'];
			$image = new Image;
			$image->status = 2;
			$image->doRedirect = false;
			$image->autoFormatAddress = true;
			$id = $image->save();

			if (!empty($levels[3])) {
				$gallery = new Gallery($levels[3]);
				$gallery->images[] = $id;
				$gallery->doRedirect = false;
				$gallery->save();
			}

			$image = Gallery::multiUploadThumb($id, $image, $_POST['prefix']);
			return json_encode(array(
				'id' => $id,
				'image' => $image
			));
		}

		function publishToTheWorld($data)
		{
			if (isset($data->ajaxCreate) || $data->justCreated) {

				//Ping Google
				$google = $this->getOption('site.pingGoogle');
				if (!empty($google)) {
					file_get_contents('http://blogsearch.google.com/ping?name='.urlencode($this->getOption('site.name')).'&url='.urlencode($this->a->getHome()).'&changesURL='.urlencode($this->a->getHome('atom.xml')));
				}
			}
		}

		function export($mixed)
		{
			if (is_numeric($mixed)) {
				$data = $this->getData($mixed);
			} else {
				$data = $mixed;
			}

			$current = (object) array(
				'parent' => $data->parent,
				'address' => $data->address,
				'type' => $data->type,
				'status' => $data->status,
				'author' => $data->author,
				'published' => $data->published
			);

			$fetch = PlexusDataControl::fetchDataSet($data);
			foreach (PlexusDataModel::$bluePrints[$fetch->type] as $field) {
				$current->$field['name'] = @$fetch->$field['name'];
				if ($field['type'] == 'file') {
					if (strpos($fetch->$field['name'], '://') === FALSE) {
						$src = $this->getStorage($field['options']['target'].'/');
						$src .= $fetch->$field['name'];
					} else {
						$src = $fetch->$field['name'];
					}
					$current->{$field['name'].'-data'} = base64_encode(file_get_contents($src));
				}
			}
			$current->oldID = $fetch->id;

			return json_encode($current);
		}

		function importExport($levels)
		{
			if (!empty($_POST['export'])) {
				$where = '';
				if (is_numeric($_POST['export'])) {
					$where = ' WHERE author='.$_POST['export'];
				}

				header('content-type: application/octet-stream');
				header('content-disposition: attachment; filename="plexus-export-'.date('YmdHis').'.json"');
				ob_implicit_flush(TRUE);
				while ($fetch = $this->db->fetch('SELECT * FROM '.$this->db->table('index').$where.' ORDER BY published ASC', 1)) {
					echo $this->export($fetch)."\n";
				}
				exit;
			}

			if (!empty($_FILES) || !empty($_GET['file'])) {
				ob_implicit_flush();
				echo '<div id="plxAdminContainer">Start importing.<br />';
				if (empty($_FILES['import']['error']) || !empty($_GET['file'])) {
					if (empty($_GET['file'])) {
						$file = $_FILES['import']['tmp_name'];
					} else {
						$file = $_GET['file'];
					}
					$fs = fopen($file, 'r');

					$key = 0;
					$users = array();
					echo 'Start inserting data.';
					while (($buffer = fgets($fs)) !== FALSE) {
						$object = json_decode($buffer);
						$object->plexusImport = TRUE;

						$data = $this->type($object, TRUE);
if (empty($data)) {
echo 'FAILED TO CREATE: '.µ($object->type.' with oldID '.$object->oldID);
	continue;
}

						$data->doRedirect = FALSE;
						$newID = $data->save();
if (is_array($newID)) {
echo 'FAILED TO CREATE: '.µ($newID);
	continue;
}
						$oldIDs[$object->oldID] = $newID;
						#echo §('CREATED '.strtolower($object->type).' (#'.$newID.') author: '.$object->author.'->'.$data->author).'<br />'."\n";
						$key++;
					}
					fclose($fs);

					#$fs = fopen('id-references.json', 'w');
					#fwrite($fs, json_encode($oldIDs));
					#fclose($fs);
					#chmod('id-references.json', 0777);
					echo 'Start updating references.';

					$lostParents = array();
					foreach ($oldIDs as $oldID => $newID) {
						$new = mysql_fetch_object(mysql_query('SELECT * FROM '.Database::table('index').' WHERE id='.$newID));
						if (empty($new)) {
echo 'newID not found: '.$newID.'<br />';
continue;
						} else {
							$author = empty($oldIDs[$new->author]) ? 0 : $oldIDs[$new->author];
							$parent = '';
							if ($new->parent > 0) {
								if (isset($oldIDs[$new->parent])) {
									$parent = ', parent='.$oldIDs[$new->parent];
									#echo §('UPDATED parent of '.$newID.': '.$new->parent.' -> '.$oldIDs[$new->parent].'<br />')."\n";
								} else {
									@$lostParents[$newID] = $object->parent;
								}
							}
							mysql_query('UPDATE '.Database::table('index').' SET author='.$author.$parent.' WHERE id='.$newID);
						}

						if ($new->type == 'ARTICLE') {
							$ea = mysql_fetch_object(mysql_query('SELECT parent,value FROM '.Database::table('numeric').' WHERE parent='.$new->id.' AND name="editorialAuthor"'));
							if (empty($oldIDs[$ea->value])) {
							
							} else {
								mysql_query('UPDATE '.Database::table('numeric').' SET value='.$oldIDs[$ea->value].' WHERE parent='.$ea->parent);
							}
							#echo §('UPDATED editorialAuthor of '.$newID.': '.$object->editorialAuthor.' -> '.$oldIDs[$object->editorialAuthor].'<br />')."\n";
						}
					}
echo 'lostParents:'.µ($lostParents);
				} else {
					echo §('Something went wrong during upload. In most cases the file\'s size ({{'.round($_FILES['import']['size']/1024/1024).'M}}) you tried to upload exceeded the allowed file size ({{'.ini_get('post_max_size').'}}) for file uploads of your webspace.');
				}
				echo '</div>';
exit;
			}

			ob_start();
?>
<div id="plxAdminContainer">
	<h1><?=§('Import')?></h1>
	<p><form class="plexusPreferencesForm" method="post" enctype="multipart/form-data" action="<?=$this->a->current()?>">
		<input type="file" name="import" id="import" />
		<button type="submit"><?=§('Upload Plexus JSON File')?></button>
	</form></p>
	<h1><?=§('Export')?></h1>
	<p><form class="plexusPreferencesForm" method="post" enctype="multipart/form-data" action="<?=$this->a->current()?>">
	<?=§('Get complete Plexus JSON data export')?>
	<input type="hidden" name="export" value="all" />
	<button type="submit"><?=§('Export')?></button>
	</form></p>
	<p><form class="plexusPreferencesForm" method="post" enctype="multipart/form-data" action="<?=$this->a->current()?>">
		Get Plexus JSON export of user #<input size="2" type="text" id="export" name="export" />
		<button type="submit"><?=§('Export')?></button>
	</form></p>
</div>
<?php
			return ob_get_clean();
		}

		function load($level, $levels, $cache)
		{
			$content = $this->run($_GET['path']);
			$this->t->connect('content', $content);
			$site = new Site($content);
			return $site->getContent();
		}

		static function componentIsActive($component)
		{
			if (in_array($component, self::$activeComponents)) {
				return TRUE;
			} else {
				return FALSE;
			}
		}

		function getActiveComponent($name)
		{
			if (isset(Control::$componentsCallback[$name])) {
				return Control::$componentsCallback[$name];
			}
			return FALSE;
		}

		function plxExport($level, $levels, $cache)
		{
			if ($this->access->granted('system.export') && !empty($levels[2])) {
				$r = $this->d->query('SELECT * FROM `'.$this->d->table('index').'` WHERE id IN ('.$this->d->escape($levels[2]).')');
				if ($r && $r->num_rows) {
					#header('Content-type: text/plain; charset=utf-8');
					header('content-type: application/octet-stream');
					header('content-disposition: attachment; filename="plexus-export-'.date('YmdHis').'.json"');
					ob_implicit_flush(TRUE);
					while ($fetch = $r->fetch_object()) {
						echo $this->export($fetch)."\n";
					}
					exit;
				}
			} else {
				return §('You do not have access to perform this action (are you logged in?) or did not deliver correct ids.');
			}
		}

		function getLanguage()
		{
			return self::$language;
		}

		function getLanguages()
		{
			$l = array();
			$languages = $this->getOption('system.languages');
			if (!empty($languages)) {
				$languages = json_decode($languages);
				foreach ($languages->prefix as $key => $prefix) {
					$l[$prefix] = $languages->lang[$key];
				}
			}
			return $l;
		}

		function cache($content, $file)
		{
			$fs = fopen($file, 'w');
			fwrite($fs, $content);
			fclose($fs);
			chmod($file, 0777);
		}

		function crawl($content)
		{
			header('content-type: text/html; charset=utf-8');
			ob_implicit_flush();
			$start = microtime(1);
			$urls = array();
			echo "<pre>Start crawling at $start\n";
			$this->crawlLinks($content, $urls, $start, $this->a->getHome());
			echo "Ready crawling after ".(microtime(1)-$start)."</pre>";
		}

		function crawlLinks($content, &$urls, &$start, $parent = '')
		{
			if (preg_match_all('/href="([^"]*)"/', $content, $results)) {
				foreach ($results[1] as $result) {
					if (strpos($result, '://') === false) {
						$url = parse_url($this->a->getHome(str_replace('./', '', str_replace('../', '', $result))));
						if ($url['path'] != '/'
							&& substr($url['path'], -3) != '.js'
							&& substr($url['path'], -4) != '.css'
							&& substr($url['path'], -4) != '.xml'
							&& substr($url['path'], -4) != '.pdf'
							&& substr($url['path'], -10) != '/trackback'
						) {
							$url = $url['scheme'].'://'.$url['host'].''.$url['path'];
							if (isset($urls[$url])) {
								$urls[$url] += 1;
							} else {
								$urls[$url] = 1;
								$c = @file_get_contents($url);
								if ($c === false) {
									echo '<strong style="color: #D00;">Error crawling <a href="'.$url.'" target="_blank">'.$url.'</a></strong> found in <a href="'.$parent.'">'.$parent.'</a>'."\n";
								} else {
									echo (microtime(1)-$start).' <a href="'.$url.'" target="_blank">'.$url.'</a> found in <a href="'.$parent.'">'.$parent.'</a>'."\n";
									if ($_GET['crawl'] == 'recursive') {
										$this->crawlLinks($c, $urls, $start, $url);
									}
								}
							}
						}
					}
				}
			}
		}

		function plxFormDeleteFile($levels)
		{
			$file = PlexusDataControl::getProperty($_POST['id'], $_POST['property']);
			PlexusDataControl::deleteProperty($_POST['id'], $_POST['property']);
			unlink($this->getStorage($_POST['target'].'/'.$file));
			return 'OK';
		}

		function clearCache()
		{
			self::$cache = false;
			Cache::clearPageCache();
		}
	}
?>
