<?php
	class PlexusBackend extends Page
	{
		function getTitle()
		{
			return 'Plexus Connect';
		}

		function getDescription()
		{
		}

		function view()
		{
			if (!$this->access->granted()) {
				$main = $this->access->getLoginDialog();
			} elseif (!$this->access->granted('system.manageConnect')) {
				$main = $this->lang->get('You do not have the necessary rights to see this section.');
			} else {
				if (empty($this->levels[2])) {
					$main = $this->dashboard();
				} else {
					switch ($this->levels[2]) {
						case 'requests':
							$main = $this->requests();
						break;
						case 'connections':
							$main = $this->connections();
						break;
						case 'trackbacks':
							$main = $this->trackbacks();
						break;
						case 'blocked-ips':
							$main = $this->blockedIPs();
						break;
					}
				}
			}
			$menu = array(
				array('dashboard', §('Dashboard'), $this->addr->assigned('system.plexus', 2), true, Plexus::getUnreadCount()),
				array('requests', §('Requests'), $this->addr->assigned('system.plexus.requests', 2), false, Plexus::getOpenRequests()),
				array('connections', §('Connections'), $this->addr->assigned('system.plexus.connections', 2), false),
				array('trackbacks', §('Trackbacks'), $this->addr->assigned('system.plexus.trackbacks', 2), false, Plexus::getPendingTrackbacks()),
				array('blockedIps', §('Blocked IPs'), $this->addr->assigned('system.plexus.blockedIps', 2), false)
			);
			return $this->t->get('system', 'backend.php', array(
				'main' => $main,
				'menu' => $menu,
				'requests' => Plexus::getOpenRequests(),
				'unread' => Plexus::getUnreadCount(),
				'trackbacks' => Plexus::getPendingTrackbacks(),
				'backendID' => 'plexus'
			));
		}

		function dashboard()
		{
			require_once PLX_COMPONENTS.'plexus/lib/api.php';
			$show = @$this->getOption('system.plexus.show', Access::$user->id)->value;
			return $this->t->get('plexus', 'dashboard.php', array(
				'show' => $show
			));
		}

		function requests()
		{
			require_once PLX_COMPONENTS.'plexus/lib/api.php';

			$send = '';

			if (!empty($_POST['plexusConnectionRequest'])) {
				$send = PlexusApi::instance()->connectionRequest($_POST['plexusConnectionRequest']);
			}

			if (!empty($_GET['cancel'])) {
				$o = $this->getOption($_GET['cancel']);
				$c = json_decode($o->value);
				if ($c->status > 0) {
					$url = parse_url($c->url);
					$r = json_decode($this->tools->httpPostRequest($url['host'], @$url['path'].'/plx-api/connect/cancel', array(
						'connectionToken' => $o->association
					)));
				}
				$this->delOption($_GET['cancel']);
				unset($_GET['cancel']);
				header('Location: '.$this->addr->current('', 0, '', 1));
				exit;
			}

			if (!empty($_GET['accept'])) {
				$o = $this->getOption($_GET['accept']);
				$c = json_decode($o->value);
				$url = parse_url($c->url);
				$r = json_decode($this->tools->httpPostRequest($url['host'], @$url['path'].'/plx-api/connect/accept', array(
					'connectionToken' => $o->association
				)));
				if (empty($r)) {
					$send = (object) array(
						'status' => '0',
						'message' => 'Invalid JSON code.'
					);
				} elseif ($r->status == 1) {
					$c->status = 2;
					$this->setOption($o->id, json_encode($c));
					unset($_GET['accept']);
					header('Location: '.$this->addr->current('', 0, '', 1));
				}
			}

			if (!empty($_GET['refuse'])) {
				$o = $this->getOption($_GET['refuse']);
				$c = json_decode($o->value);
				$url = parse_url($c->url);
				$r = json_decode($this->tools->httpPostRequest($url['host'], @$url['path'].'/plx-api/connect/refuse', array(
					'connectionToken' => $o->association
				)));
				$this->delOption($o->id);
				unset($_GET['refuse']);
				header('Location: '.$this->addr->current('', 0, '', 1));
				exit;
			}

			if (!empty($_GET['remove'])) {
				$this->delOption($_GET['remove']);
				unset($_GET['remove']);
				header('Location: '.$this->addr->current('', 0, '', 1));
				exit;
			}

			$connections = PlexusApi::instance()->getConnections(false, array(2, 5));

			return $this->t->get('plexus', 'requests.php', array(
				'send' => $send,
				'connections' => $connections
			));
		}

		function connections()
		{
			if (!empty($_GET['disconnect'])) {
				$o = $this->getOption($_GET['disconnect']);
				$c = json_decode($o->value);
				$url = parse_url($c->url);
				$r = json_decode($this->tools->httpPostRequest($url['host'], @$url['path'].'/plx-api/connect/disconnect', array(
					'connectionToken' => $o->association
				)));
				$this->delOption($o->id);
				unset($_GET['disconnect']);
				header('Location: '.$this->addr->current('', 0, '', 1));
			}

			if (!empty($_GET['remove'])) {
				$this->delOption($_GET['remove']);
				unset($_GET['remove']);
				header('Location: '.$this->addr->current('', 0, '', 1));
				exit;
			}

			require_once PLX_COMPONENTS.'plexus/lib/api.php';
			$connections = PlexusApi::instance()->getConnections(array(2,5));

			return $this->t->get('plexus', 'connections.php', array(
				'connections' => $connections
			));
		}

		function trackbacks()
		{
			ob_start();
			$this->delOption('pendingTrackbacks');
			$trackbacks = $this->getOption('trackback');
			if (empty($trackbacks)) {
?>
				<h1><?=§('Trackbacks')?></h1>				
				<p><?=§('Currently there are no trackbacks.')?></p>
<?php
			} else {
				if (!empty($_GET['delete'])) {
					$this->delOption($_GET['delete']);
					header('Location: '.$this->addr->current('', false, '', 0, array('delete')));
					exit;
				}
				if (!is_array($trackbacks)) {
					$trackbacks = array($trackbacks);
				}
?>
				<div class="hpanel">
					<h1><?=§('Trackbacks')?></h1>
					<div class="toggleButtons">
						<a href="?filter="<?=empty($_GET['filter']) ? ' class="active"' : ''?>><?=§('All')?></a>
						<a href="?filter=own"<?=!empty($_GET['filter']) && $_GET['filter'] == 'own' ? ' class="active"' : ''?>><?=§('Own')?></a>
						<a href="?filter=foreign"<?=!empty($_GET['filter']) && $_GET['filter'] == 'foreign' ? ' class="active"' : ''?>><?=§('Foreign')?></a>
					</div>
					<div class="clear"></div>
				</div>
				<div class="guiListWrap plexusTrackbacks">
					<ul class="guiList">
<?php
				foreach ($trackbacks as $trackback) {
					$association = $this->getData($trackback->association);
					$t = json_decode($trackback->value);
?>
<? if (empty($t->target)) : $empty = 0; ?>
	<? if (empty($_GET['filter']) || $_GET['filter'] == 'foreign') : $empty = 1; ?>
					<li class="guiListItem">
						<a href="<?=$t->url?>" title="<?=$t->title?>"><strong><?=$t->blog_name?></strong> » <?=$t->title?></a>
						<?=§('trackbacked')?>
						<a href="<?=$association->getLink()?>"><?=$association->getTitle()?></a>
						<span><?=$this->tools->detectTime($t->time)?></span>
						<a href="?delete=<?=$trackback->id?>"><?=§('Delete')?></a>
						<a href="<?=$this->addr->assigned('system.plexus')?>/blocked-ips?block=<?=$t->ip?>"><?=§('Block IP')?></a> (<?=$t->ip?>)
					</li>
	<? endif; ?>
<? else : $empty = 0; ?>
	<? if (empty($_GET['filter']) || $_GET['filter'] == 'own') : $empty = 1; ?>
					<li class="guiListItem own">
						<a href="<?=$association->getLink()?>"><?=$association->getTitle()?></a>
						<?=§('trackbacked')?>
						<a href="<?=$t->url?>" title="<?=$t->title?>"><strong><?=$t->blog_name?></strong> » <?=$t->title?></a>
						<span><?=$this->tools->detectTime($t->time)?></span>
						<a href="?delete=<?=$trackback->id?>"><?=§('Delete')?></a>
					</li>
	<? endif; ?>
<? endif; ?>
<?php
				}
?>
	<? if (!$empty) : ?>
					<li class="guiListItem"><?=§('No items in this view.')?></li>
	<? endif; ?>
					</ul>
				</div>
<?php
			}
			return ob_get_clean();
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
	}
?>
