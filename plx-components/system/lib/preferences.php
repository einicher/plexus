<?php
	class Preferences extends Page
	{
		static $instance;
		public $section;

		static function instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function control($level, $levels, $cache)
		{
			if ($this->access->granted('preferences')) {
				Control::$standalone = true;
				if (empty($levels[2])) {
					$this->main = $this->generalSettings();
				} else {
					switch ($levels[2]) {
						case 'languages': $this->main = $this->languages(); break;
						case 'cache': $this->main = Cache::instance()->preferences($level, $levels, $cache); break;
						case 'components': $this->main = Components::instance()->index($level, $levels, $cache); break;
						case 'trackbacks': $this->main = Trackback::instance()->preferencesPage($level, $levels, $cache); break;
						case 'blocked-ips': $this->main = $this->blockedIps($level, $levels, $cache); break;
						case 'multisite': $this->main = $this->multisite($level, $levels, $cache); break;
					}
					if (isset(Core::$preferences[$levels[2]])) {
						$p = Core::$preferences[$levels[2]];
						$this->main = $p->actor->{$p->call}($level, $levels, $cache);
					}
				}
				return $this;
			} else {
				return new Page(§('Please login'), §('To access the preferences section you need do be logged in.').'<br /><br />'.$this->access->getLoginDialog());
			}
		}

		function view()
		{
			return $this->t->get('backend.php', array(
				'main' => $this->main,
				'menu' => $this->getMenu(),
				'backendID' => 'preferences'
			));
		}

		function getMenu()
		{
			$menu = array(
				array('general', §('General Settings'), $this->a->assigned('system.preferences', 2), true),
				array('cache', §('Cache'), $this->a->assigned('system.preferences.cache', 2)),
				array('components', §('Components'), $this->a->assigned('system.preferences.components', 2)),
				array('languages', §('Languages'), $this->a->assigned('system.preferences.languages', 2)),
				array('trackbacks', §('Trackbacks'), $this->a->assigned('system.preferences.trackbacks', 2), false, Trackback::instance()->getPendingTrackbacks()),
				array('blocked-ips', §('Blocked IPs'), $this->a->assigned('system.preferences.blockedIps', 2))
			);
			if (Access::$user->rights == 'OVERLOARD' && file_exists(PLX_STORAGE.'multi/')) {
				$menu[] = array('multisite', §('Multi site'), $this->a->assigned('system.preferences.multisite', 2));
			}
			foreach (Core::$preferences as $p) {
				$name = $this->a->transform(strtolower($p->name));
				$menu[] = array($name, $p->name, $this->a->assigned('system.preferences.'.$name));
			}
			foreach (Site::$components as $component) {
				$name = strtolower($component->address);
				$menu[] = array($name, $component->label, $this->a->assigned('system.preferences.'.$name, 2));
			}
			return $menu;
		}

		function generalSettings()
		{
			if (!empty($_POST['plexusForm'])) {
				foreach ($_POST as $name => $value) {
					if (substr($name, 0, 5) == 'site_') {
						if (empty($value)) {
							Core::delOption('site.'.substr($name, 5));
						} else {
							Core::setOption('site.'.substr($name, 5), $value);
						}
					}
					if (substr($name, 0, 8) == 'content_') {
						if (empty($value)) {
							Core::delOption('content.'.substr($name, 8));
						} else {
							Core::setOption('content.'.substr($name, 8), $value);
						}
					}
					if (substr($name, 0, 8) == 'gallery_') {
						if (empty($value)) {
							Core::delOption('gallery.'.substr($name, 8));
						} else {
							Core::setOption('gallery.'.substr($name, 8), $value);
						}
					}
				}
				if (!isset($_POST['site_pingGoogle'])) {
					Core::delOption('site.pingGoogle');
				}
				if (!isset($_POST['site.trackbacks'])) {
					Core::delOption('site.trackbacks');
				}
			}

			$form = new Form(array('type' => 'preferences',
				array('type' => 'string', 'name' => 'site_name', 'required' => TRUE, 'options' => array('label' => §('Site name'), 'caption' => §('This website\'s main title.'))),
				array('type' => 'string', 'name' => 'site_owner', 'required' => FALSE, 'options' => array('label' => §('Site Owner'), 'caption' => §('Appearing in the footer beside the copyright notice.'))),
				array('type' => 'string', 'name' => 'site_ownerLink', 'required' => FALSE, 'options' => array('label' => §('Site Owner Homepage'), 'caption' => §('Will link the owner name beside the footer copyright notice.'))),
				array('type' => 'string', 'name' => 'site_mail', 'required' => FALSE, 'options' => array('label' => §('Site email'), 'caption' => §('The email address appearing as sender at system mailings.'))),
				array('type' => 'string', 'name' => 'site_language', 'required' => FALSE, 'options' => array('label' => §('Main language'), 'caption' => §('The sites main language.'))),
				array('type' => 'string', 'name' => 'site_theme', 'required' => FALSE, 'options' => array('label' => §('Theme'), 'caption' => §('The way this site looks.'))),
				array('type' => 'checkbox', 'name' => 'site_pingGoogle', 'required' => FALSE, 'options' => array('label' => §('Ping Google'), 'caption' => §('Ping Google when a content is published.'))),
				array('type' => 'checkbox', 'name' => 'site_trackbacks', 'required' => FALSE, 'options' => array('label' => §('Enable Trackbacks'), 'caption' => §('Will display a trackback link in the info field beneath all data types.'))),
				array('type' => 'text', 'name' => 'site_code', 'required' => FALSE, 'options' => array('label' => §('Header Codes'), 'caption' => §('Insert code to be inserted in the head of every page (f.e. Google Analytics)'))),
				array('type' => 'string', 'name' => 'content_width', 'required' => TRUE, 'options' => array('label' => §('Content width'), 'caption' => §('Width of main content.'))),
				array('type' => 'string', 'name' => 'content_fullsize', 'required' => TRUE, 'options' => array('label' => §('Content fullsize'), 'caption' => §('Width of fullsize content.'))),
				array('type' => 'string', 'name' => 'gallery_thumbSize', 'required' => TRUE, 'options' => array('label' => §('Thumb size'), 'caption' => §('Width of gallery thumbs.')))
			), array(
				'site_name' => Core::getOption('site.name'),
				'site_owner' => Core::getOption('site.owner'),
				'site_ownerLink' => Core::getOption('site.ownerLink'),
				'site_mail' => Core::getOption('site.mail'),
				'site_language' => Core::getOption('site.language'),
				'site_theme' => Core::getOption('site.theme'),
				'site_pingGoogle' => Core::getOption('site.pingGoogle'),
				'site_trackbacks' => Core::getOption('site.trackbacks'),
				'site_code' => Core::getOption('site.code'),
				'content_width' => Core::getOption('content.width'),
				'content_fullsize' => Core::getOption('content.fullsize'),
				'gallery_thumbSize' => Core::getOption('gallery.thumbSize')
			));
			
			ob_start();
?>
			<h1><?=§('General Settings')?></h1>
			<?=$form?>
<?php
			return ob_get_clean();
		}

		function languages()
		{
			if (!empty($_POST['plexusLanguageSettings'])) {
				$langs = array();
				foreach ($_POST['prefix'] as $key => $prefix) {
					if (empty($_POST['prefix'][$key]) || empty($_POST['lang'][$key])) {
						unset($_POST['prefix'][$key], $_POST['lang'][$key]);
					} else {
						$_POST['prefix'][$key] = $this->a->transform($_POST['prefix'][$key]);
					}
				}
				$this->setOption('system.languages', json_encode(array(
					'prefix' => $_POST['prefix'],
					'lang' => $_POST['lang']
				)));
				$success = 1;
			}
			$langs = $this->getOption('system.languages');
			ob_start();
?>
	<div class="generalSettings">
		<h1><?=§('Languages')?></h1>
<? if (isset($success)) : ?>
		<div class="infos"><?=§('Data saved successfully.')?></div>
		<script type="text/javascript" >
			jQuery('#plxAdminContainer .infos').delay(5000).fadeOut();
		</script>
<? endif; ?>
		<p><?=§('Here you can set custom root paths like {{<strong>'.$this->a->getHome('de').'</strong>}} for your multiple languages.')?></p>
		<form method="post" class="plexusPreferencesForm plexusForm" action="<?=$this->a->current()?>">
			<ul id="languageEditorBody" style="list-style-type: none; margin: 0; padding: 0;">
<?php
	if (!empty($langs)) {
		$langs = json_decode($langs);
		foreach ($langs->prefix as $key => $prefix) {
?>
				<li>
					<span class="handle" style="background: #070; cursor: move;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
					<label for=""><?=§('Language name')?></label>
					<input type="text" name="lang[]" value="<?=$langs->lang[$key]?>" />
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label><?=$this->a->getHome()?></label>
					<input type="text" name="prefix[]" value="<?=$prefix?>" style="width: 50px;" />
				</li>
<?php

		}
	}
?>
				<li>
					<span class="handle" style="background: #070; cursor: move;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
					<label for=""><?=§('Language name')?></label>
					<input type="text" name="lang[]" value="" />
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label><?=$this->a->getHome()?></label>
					<input type="text" name="prefix[]" value="" style="width: 50px;" />
				</li>
			</ul>
			<div id="languageEditorDefault" style="display: none;">
				<li>
					<span class="handle" style="background: #070; cursor: move;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
					<label for=""><?=§('Language name')?></label>
					<input type="text" name="lang[]" value="" />
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label><?=$this->a->getHome()?></label>
					<input type="text" name="prefix[]" value="" style="width: 50px;" />
				</li>
			</div>
			<br />
			<button type="submit" style="float: right;"><?=§('Save')?></button>
			<button id="languageEditorButton" type="button"><?=§('+ Add Language')?></button>
			<input type="hidden" name="plexusLanguageSettings" value="1" />
		</form>
		<script type="text/javascript">
			jQuery('#languageEditorButton').click(function(e) {
				jQuery('#languageEditorBody').append(
					jQuery('#languageEditorDefault').html()
				);
			});
			jQuery('ul#languageEditorBody').sortable({
				handle: 'span.handle',
				cursor: 'crosshair'
			});
		</script>
	</div>
<?php
			return ob_get_clean();
		}

		function getTitle()
		{
		}

		function getDescription($words = '')
		{
		}

		function blockedIPs()
		{
			if (!empty($_POST['block'])) {
				$this->blockIP($_POST['block']);
				header('Location: '.$this->a->current());
				exit;
			}
			if (!empty($_GET['unblock'])) {
				$this->unblockIP($_GET['unblock']);
				header('Location: '.$this->a->current('', false, '', 0, array('unblock')));
				exit;
			}
			if (!empty($_GET['block'])) {
				$this->blockIP($_GET['block']);
				header('Location: '.$this->a->current('', false, '', 0, array('block')));
				exit;
			}

			ob_start();
			$ips = $this->getBLockedIPs();
?>
			<h1><?=§('Blocked IP addresses')?></h1>
			<div class="blockIpForm">
				<form method="post" action="">
					<input type="text" name="block" />
					<button type="submit"><?=§('Block IP')?></button>
				</form>
			</div>
			<br />
			<br />
			<div class="connections">
				<ul>
<?php
			if (empty($ips)) {
?>
				<li><?=§('Currently there are no blocked IPs.')?></li>
<?php
			} else {
				foreach ($ips as $ip) {
?>
					<li>
						<?=$ip?> <a href="?unblock=<?=$ip?>"><?=§('unblock')?></a>
					</li>
<?php
				}
			}
?>
				</ul>
			</div>
<?php
			return ob_get_clean();
		}

		function blockIP($ip)
		{
			$trackbacks = $this->getOption('trackback', '', 2);
			foreach ($trackbacks as $trackback) {
				$id = $trackback->id;
				$trackback = json_decode($trackback->value);
				if ($ip == $trackback->ip) {
					$this->delOption($id);
				}
			}

			$ips = $this->getOption('blockedIPs', '', true);
			if (empty($ips)) {
				$this->setOption('blockedIPs', json_encode(array($ip)));
			} else {
				$id = $ips->id;
				$ips = json_decode($ips->value);
				if (!in_array($ip, $ips)) {
					$ips[] = $ip;
				}
				$this->setOption($id, json_encode($ips));
				$trackbacks = $this->getOption('trackback');
			}
		}

		function getBlockedIPs()
		{
			$ips = $this->getOption('blockedIPs', '', true);
			if (empty($ips)) {
				return array();
			} else {
				return json_decode($ips->value);
			}
		}

		function unblockIP($ip)
		{
			$ips = $this->getOption('blockedIPs', '', true);
			if (!empty($ips)) {
				$id = $ips->id;
				$ips = json_decode($ips->value);
				$key = array_search($ip, $ips);
				if ($key !== false) {
					unset($ips[$key]);
					$this->setOption($id, json_encode(array_merge($ips)));
				}
			}
		}

		function isBlockedIP($ip)
		{
			$ips = $this->getOption('blockedIPs', '', true);
			if (empty($ips)) {
				return false;
			} else {
				$ips = json_decode($ips->value);
				if (in_array($ip, $ips)) {
					return true;
				}
				return false;
			}
		}

		function multisite($level, $levels, $cache)
		{
			$clear = (object) array(
				'fail' => array(),
				'success' => array()
			);

			$sites = array();
			$p = PLX_STORAGE.'multi/';
			$d = opendir($p);
			while ($c = readdir($d)) {
				if ($c != '.' && $c != '..') {
					$sites[] = $c;
				}
			}
			sort($sites);

			if (!empty($_GET['cpc'])) {
				if ($_GET['cpc'] == '__all__') {
					foreach ($sites as $site) {
						$c = Cache::clearPageCache($site);
						$clear->fail = array_merge($c->fail, $clear->fail);
						$clear->success = array_merge($c->success, $clear->success);
					}
				} else {
					$clear = Cache::clearPageCache(urldecode($_GET['cpc']));
				}
			}
			if (!empty($_GET['cic'])) {
				if ($_GET['cic'] == '__all__') {
					foreach ($sites as $site) {
						$c = Cache::clearImageCache($site);
						$clear->fail = array_merge($c->fail, $clear->fail);
						$clear->success = array_merge($c->success, $clear->success);
					}
				} else {
					$clear = Cache::clearImageCache(urldecode($_GET['cic']));
				}
			}

			foreach ($sites as $k => $site) {
				$pageCache = 0;
				if (file_exists($p.$site.'/page-cache/')) {
					$d = opendir($p.$site.'/page-cache/');
					while ($c = readdir($d)) {
						if ($c != '.' && $c != '..') {
							$pageCache++;
						}
					}
				} else {
					$pageCache = §('No page cache');
				}

				$imageCache = 0;
				if (file_exists($p.$site.'/cache/')) {
					$d = opendir($p.$site.'/cache/');
					while ($c = readdir($d)) {
						if ($c != '.' && $c != '..') {
							$imageCache++;
						}
					}
				} else {
					$imageCache = §('No image cache');
				}

				$sites[$k] = (object) array(
					'name' => $site,
					'pageCache' => $pageCache,
					'imageCache' => $imageCache
				);
			}

			return $this->t->get('preferences-multisite.php', array(
				'sites' => $sites,
				'clear' => $clear
			));
		}
	}
?>
