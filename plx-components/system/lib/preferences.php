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
			return $this->t->get('system', 'backend.php', array(
				'main' => $this->main,
				'menu' => $this->getMenu(),
				'backendID' => 'preferences'
			));
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
			}

			$form = new Form(array('type' => 'preferences',
				array('type' => 'string', 'name' => 'site_name', 'required' => TRUE, 'options' => array('label' => $this->lang->get('Site name'), 'caption' => $this->lang->get('This website\'s main title.'))),
				array('type' => 'string', 'name' => 'site_owner', 'required' => FALSE, 'options' => array('label' => $this->lang->get('Site Owner'), 'caption' => $this->lang->get('Appearing in the footer beside the copyright notice.'))),
				array('type' => 'string', 'name' => 'site_ownerLink', 'required' => FALSE, 'options' => array('label' => $this->lang->get('Site Owner Homepage'), 'caption' => $this->lang->get('Will link the owner name beside the footer copyright notice.'))),
				array('type' => 'string', 'name' => 'site_mail', 'required' => FALSE, 'options' => array('label' => $this->lang->get('Site email'), 'caption' => $this->lang->get('The email address appearing as sender at system mailings.'))),
				array('type' => 'string', 'name' => 'site_language', 'required' => FALSE, 'options' => array('label' => $this->lang->get('Main language'), 'caption' => $this->lang->get('The sites main language.'))),
				array('type' => 'string', 'name' => 'site_theme', 'required' => FALSE, 'options' => array('label' => $this->lang->get('Theme'), 'caption' => $this->lang->get('The way this site looks.'))),
				array('type' => 'checkbox', 'name' => 'site_pingGoogle', 'required' => FALSE, 'options' => array('label' => $this->lang->get('Ping Google'), 'caption' => $this->lang->get('Ping Google when a content is published.'))),
				array('type' => 'checkbox', 'name' => 'site_trackbacks', 'required' => FALSE, 'options' => array('label' => $this->lang->get('Enable Trackbacks'), 'caption' => $this->lang->get('Will display a trackback link in the info field beneath all data types.'))),
				array('type' => 'text', 'name' => 'site_code', 'required' => FALSE, 'options' => array('label' => $this->lang->get('Header Codes'), 'caption' => $this->lang->get('Insert code to be inserted in the head of every page (f.e. Google Analytics)'))),
				array('type' => 'string', 'name' => 'content_width', 'required' => TRUE, 'options' => array('label' => $this->lang->get('Content width'), 'caption' => $this->lang->get('Width of main content.'))),
				array('type' => 'string', 'name' => 'content_fullsize', 'required' => TRUE, 'options' => array('label' => $this->lang->get('Content fullsize'), 'caption' => $this->lang->get('Width of fullsize content.'))),
				array('type' => 'string', 'name' => 'gallery_thumbSize', 'required' => TRUE, 'options' => array('label' => $this->lang->get('Gallery thumb size'), 'caption' => $this->lang->get('Width of gallery thumbs.')))
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
						$_POST['prefix'][$key] = $this->addr->transform($_POST['prefix'][$key]);
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
		<h1><?=$this->lang->get('Languages')?></h1>
<? if (isset($success)) : ?>
		<div class="infos"><?=$this->lang->get('Data saved successfully.')?></div>
		<script type="text/javascript" >
			jQuery('#plxAdminContainer .infos').delay(5000).fadeOut();
		</script>
<? endif; ?>
		<p><?=$this->lang->get('Here you can set custom root paths like {{<strong>'.$this->addr->getHome('de').'</strong>}} for your multiple languages.')?></p>
		<form method="post" class="plexusPreferencesForm plexusForm" action="<?=$this->addr->current()?>">
			<ul id="languageEditorBody" style="list-style-type: none; margin: 0; padding: 0;">
<?php
	if (!empty($langs)) {
		$langs = json_decode($langs);
		foreach ($langs->prefix as $key => $prefix) {
?>
				<li>
					<span class="handle" style="background: #070; cursor: move;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
					<label for=""><?=$this->lang->get('Language name')?></label>
					<input type="text" name="lang[]" value="<?=$langs->lang[$key]?>" />
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label><?=$this->addr->getHome()?></label>
					<input type="text" name="prefix[]" value="<?=$prefix?>" style="width: 50px;" />
				</li>
<?php

		}
	}
?>
				<li>
					<span class="handle" style="background: #070; cursor: move;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
					<label for=""><?=$this->lang->get('Language name')?></label>
					<input type="text" name="lang[]" value="" />
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label><?=$this->addr->getHome()?></label>
					<input type="text" name="prefix[]" value="" style="width: 50px;" />
				</li>
			</ul>
			<div id="languageEditorDefault" style="display: none;">
				<li>
					<span class="handle" style="background: #070; cursor: move;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
					<label for=""><?=$this->lang->get('Language name')?></label>
					<input type="text" name="lang[]" value="" />
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label><?=$this->addr->getHome()?></label>
					<input type="text" name="prefix[]" value="" style="width: 50px;" />
				</li>
			</div>
			<br />
			<button type="submit" style="float: right;"><?=§('Save')?></button>
			<button id="languageEditorButton" type="button"><?=$this->lang->get('+ Add Language')?></button>
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

		function getDescription()
		{
		}

		function blockedIPs()
		{
			if (!empty($_POST['block'])) {
				$this->blockIP($_POST['block']);
				header('Location: '.$this->addr->current());
				exit;
			}
			if (!empty($_GET['unblock'])) {
				$this->unblockIP($_GET['unblock']);
				header('Location: '.$this->addr->current('', false, '', 0, array('unblock')));
				exit;
			}
			if (!empty($_GET['block'])) {
				$this->blockIP($_GET['block']);
				header('Location: '.$this->addr->current('', false, '', 0, array('block')));
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

		function getMenu()
		{
			$menu = array(
				array('general', §('General Settings'), $this->addr->assigned('system.preferences', 2), true),
				array('cache', §('Cache'), $this->addr->assigned('system.preferences.cache', 2)),
				array('components', §('Components'), $this->addr->assigned('system.preferences.components', 2)),
				array('languages', §('Languages'), $this->addr->assigned('system.preferences.languages', 2)),
				array('trackbacks', §('Trackbacks'), $this->addr->assigned('system.preferences.trackbacks', 2), false, Trackback::instance()->getPendingTrackbacks()),
				array('blocked-ips', §('Blocked IPs'), $this->addr->assigned('system.preferences.blockedIps', 2))
			);
			foreach (Core::$preferences as $p) {
				$name = $this->addr->transform(strtolower($p->name));
				$menu[] = array($name, $p->name, $this->addr->assigned('system.preferences.'.$name));
			}
			foreach (Site::$components as $component) {
				$name = strtolower($component->address);
				$menu[] = array($name, $component->label, $this->addr->assigned('system.preferences.'.$name, 2));
			}
			return $menu;
		}
	}
?>
