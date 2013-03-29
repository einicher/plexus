<?php
	class Plexus extends Page
	{
		static $instance;

		static function instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function control($level, $levels, $cache)
		{
			Control::$standalone = TRUE;
			$this->levels = $levels;
			return $this;
		}

		static function getIndicators()
		{
			$indicator = 0;
			$indicator += self::getUnreadCount();
			$indicator += self::getOpenRequests();
			$indicator += self::getPendingTrackbacks();
			return $indicator;
		}

		function getUnreadCount()
		{
			$r = Database2::instance()->query('SELECT * FROM '.Database2::instance()->table('pushes').' WHERE status=0');
			if (isset($r->num_rows)) {
				return $r->num_rows;
			}
			return 0;
		}

		function getOpenRequests()
		{
			$connections = Core::getOption('plexus.connect.connection');
			if (empty($connections)) {
				return 0;
			}
			if (!is_array($connections)) {
				$connections = array($connections);
			}
			$indicator = 0;
			foreach ($connections as $connection) {
				$connection = json_decode($connection->value);
				if ($connection->status == 1 && !isset($connection->validated)) {
					$indicator++;
				}
			}
			return $indicator;
		}

		static function getPendingTrackbacks()
		{
			return Core::getOption('pendingTrackbacks');
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
			return $this->t->get('system', 'plexus.php', array(
				'main' => $main,
				'requests' => self::getOpenRequests(),
				'unread' => self::getUnreadCount(),
				'trackbacks' => self::getPendingTrackbacks()
			));
		}

		function dashboard()
		{
			$show = @$this->getOption('system.plexus.show', Access::$user->id)->value;
			ob_start();
?>
			<div class="plxPushes">
				<div class="panel">
					<h1><?=§('Plexus')?></h1>
					<div id="refresh" class="button plxGuiElement"><?=§('Refresh')?></div>
					<div id="markAllRead" class="button plxGuiElement"><?=§('Mark all read')?></div>
					<div class="toggleButtons plxGuiElement">
						<div id="showAll"<?= !empty($show) ? ' class="active"' : '' ?>><?=§('All')?></div>
						<div id="showUnread"<?= empty($show) ? ' class="active"' : '' ?>><?=§('Unread')?></div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="pushes">
<?php
			if (!$this->db->checkForTable($this->d->table('pushes', FALSE))) {
?>
				<p><?=§('Currently there are no items available.')?></p>
<?php
			} else {
				$pushes = $this->api->getPushes();
				echo $pushes['content'];
			}
?>
				</div>
			</div>
			<script type="text/javascript">
				jQuery('.plxPushes .panel #showAll').click(function() {
					jQuery.get('<?=$this->addr->getHome('plx-api/push-view-mode?mode=1')?>');
					jQuery('.plxPushes .panel div').removeClass('active');
					jQuery(this).addClass('active');
					jQuery.getJSON('<?=$this->addr->getHome('plx-api/push-get-pushes')?>', function(data) {
						jQuery('.plxPushes .pushes').html(data.content);
					});
				});
				jQuery('.plxPushes .panel #showUnread').click(function() {
					jQuery.get('<?=$this->addr->getHome('plx-api/push-view-mode?mode=0')?>');
					jQuery('.plxPushes .panel div').removeClass('active');
					jQuery(this).addClass('active');
					jQuery.getJSON('<?=$this->addr->getHome('plx-api/push-get-pushes')?>', function(data) {
						jQuery('.plxPushes .pushes').html(data.content);
					});
				});
				jQuery('.plxPushes .panel #refresh').click(function() {
					jQuery.getJSON('<?=$this->addr->getHome('plx-api/push-get-pushes')?>', function(data) {
						jQuery('.plxPushes .pushes').html(data.content);
						plexusRefreshIndicators();
					});
				});
				jQuery('.plxPushes #markAllRead').click(function () {
					jQuery.get('<?=$this->addr->getHome('plx-api/push-all-read')?>');
					jQuery('.pushes div.result').removeClass('unread');
					jQuery('.pushes div.result').addClass('read');
					plexusRefreshIndicators();
				});
				function plexusRefreshIndicators()
				{
					jQuery('#panelMenuItem-plexus .plexusPanelIndicator span').html('0');
					jQuery('#panelMenuItem-plexus .plexusPanelIndicator').css('display', 'none');
					jQuery.getJSON('<?=$this->addr->getHome('plx-api/push-get-requests-count')?>', function(data) {
						if (data.count) {
							jQuery('#requestsIndicator').html('(' + data.count + ')');
							jQuery('#panelMenuItem-plexus .plexusPanelIndicator span').html(parseInt(jQuery('#panelMenuItem-plexus .plexusPanelIndicator span').html())+parseInt(data.count));
							jQuery('#panelMenuItem-plexus .plexusPanelIndicator').css('display', 'block');
						} else {
							jQuery('#requestsIndicator').html('');
						}
					});
					jQuery.getJSON('<?=$this->addr->getHome('plx-api/push-get-unread-count')?>', function(data) {
						if (data.count) {
							jQuery('#unreadIndicator').html('(' + data.count + ')');
							jQuery('#panelMenuItem-plexus .plexusPanelIndicator span').html(parseInt(jQuery('#panelMenuItem-plexus .plexusPanelIndicator span').html())+parseInt(data.count));
							jQuery('#panelMenuItem-plexus .plexusPanelIndicator').css('display', 'block');
						} else {
							jQuery('#unreadIndicator').html('');
						}
					});
				}
			</script>
<?php
			return ob_get_clean();
		}

		function requests()
		{
			if (!empty($_POST['plexusConnectionRequest'])) {
				$send = $this->api->connectionRequest($_POST['plexusConnectionRequest']);
			}

			if (!empty($_GET['cancel'])) {
				$o = $this->getOption($_GET['cancel']);
				$c = json_decode($o->value);
				if ($c->status > 0) {
					$url = parse_url($c->url);
					$r = json_decode($this->tools->httpPostRequest($url['host'], @$url['path'].'/plx-api/cancel', array(
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
				$r = json_decode($this->tools->httpPostRequest($url['host'], @$url['path'].'/plx-api/accept', array(
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
				$r = json_decode($this->tools->httpPostRequest($url['host'], @$url['path'].'/plx-api/refuse', array(
					'connectionToken' => $o->association
				)));
				if (empty($r)) {
					$send = (object) array(
						'status' => '0',
						'message' => 'Invalid JSON code.'
					);
				} elseif ($r->status == 1) {
					$this->delOption($o->id);
					unset($_GET['refuse']);
					header('Location: '.$this->addr->current('', 0, '', 1));
				}
			}

			if (!empty($_GET['remove'])) {
				$this->delOption($_GET['remove']);
				unset($_GET['remove']);
				header('Location: '.$this->addr->current('', 0, '', 1));
				exit;
			}

			ob_start();
?>
<? if (isset($send)) : ?>
	<? if (empty($send->status)) : ?>
			<div class="errors">
		<? if (isset($send->connectionStatus)) :
				switch ($send->connectionStatus) :
					case 0: echo §('A connection request for {{'.$send->url.'}} was made, but the target did not respond. Maybe its not a Plexus driven website?.'); break;
					case 1: echo §('There already is a pending connection request for {{'.$send->url.'}}.'); break;
					case 2: echo §('There already is an established connection for {{'.$send->url.'}}.'); break;
					case 3: echo §('There already has been a connection request for {{'.$send->url.'}}. Sorry, but it has been refused.'); break;
				endswitch;
		   elseif (isset($send->message)) : ?>
				<?=§($send->message)?>
		<? else : ?>
				<?=§('Something went wrong.')?>
		<? endif; ?>
			</div>
	<? else : ?>
			<div class="infos"><?=§('Connection request successfull. You now need to wait until the target accepts/refuses to connect with you.')?></div>
	<? endif; ?>
<? endif; ?>
			<form id="request" method="post" action="">
				<p><?=§('Send a connection request to another Plexus website.')?></p>
				<input type="text" id="plexusConnectionRequest" name="plexusConnectionRequest" />
				<button type=""><?=§('Send request')?></button>
			</form>
			<br />
			<br />
			<h2><?=§('Pending connection requests')?></h2>
			<div class="connections">
<?php			
			$connections = $this->api->getConnections(false, 2);
			if (empty($connections)) {
				echo §('Currently there are no pending connection requests.');
			} else {
?>
			<ul>
<? foreach ($connections as $connection) : ?>
	<? if ($connection->status != 2) : ?>
				<li>
					<a href="<?=$connection->url?>" target="blank"><?=$connection->name?></a>
					<span><?=date(§('d.m.Y H:i:s'), $connection->requested)?></span>
	<? if ($connection->status == 0) : ?>
					<span><?=§('requested by you and not confirmed by them. Maybe not a Plexus driven website?')?></span>
					<a href="<?=$this->addr->current('?cancel='.$connection->id)?>" class="cancel"><?=§('Cancel')?></a>
	<? elseif ($connection->status == 1 && isset($connection->validated)) : ?>
					<span><?=§('requested by you')?></span>
					<a href="<?=$this->addr->current('?cancel='.$connection->id)?>" class="cancel"><?=§('Cancel')?></a>
	<? elseif ($connection->status == 2) : ?>

	<? elseif ($connection->status == 3) : ?>
					<a href="<?=$this->addr->current('?remove='.$connection->id)?>" class="remove"><?=§('Remove')?></a>
					<span class="requestCanceled"><?=§('Request refused on {{'.date(§('d.m.Y H:i:s'), $connection->refused).'}}.')?></span>
	<? elseif ($connection->status == 4) : ?>
					<span><?=§('requested by them')?></span>
					<a href="<?=$this->addr->current('?remove='.$connection->id)?>" class="remove"><?=§('Remove')?></a>
					<span class="requestCanceled"><?=§('Request canceled on {{'.date(§('d.m.Y H:i:s'), $connection->canceled).'}}.')?></span>
	<? elseif ($connection->status == 5) : ?>
					<a href="<?=$this->addr->current('?remove='.$connection->id)?>" class="remove"><?=§('Remove')?></a>
					<span class="requestCanceled"><?=§('Connection terminated on {{'.date(§('d.m.Y H:i:s'), $connection->disconnected).'}}.')?></span>
	<? else : ?>
					<span><?=§('requested by them')?></span>
					<a href="<?=$this->addr->current('?refuse='.$connection->id)?>" class="refuse"><?=§('Refuse')?></a>
					<a href="<?=$this->addr->current('?accept='.$connection->id)?>" class="accept"><?=§('Accept')?></a>
	<? endif; ?>
				</li>
	<? endif; ?>
<? endforeach; ?>			
			</ul>
<?php
			}
?>
			</div>
<?php
			return ob_get_clean();
		}

		function connections()
		{
			if (!empty($_GET['disconnect'])) {
				$o = $this->getOption($_GET['disconnect']);
				$c = json_decode($o->value);
				$url = parse_url($c->url);
				$r = json_decode($this->tools->httpPostRequest($url['host'], @$url['path'].'/plx-api/disconnect', array(
					'connectionToken' => $o->association
				)));
				if (empty($r)) {
					$send = (object) array(
						'status' => '0',
						'message' => 'Invalid JSON code.'
					);
				} else {
					$this->delOption($o->id);
					unset($_GET['disconnect']);
					header('Location: '.$this->addr->current('', 0, '', 1));
				}
			}

			ob_start();
?>
<? if (isset($send)) : ?>
	<? if (empty($send->status)) : ?>
			<div class="errors">
		<? if (isset($send->connectionStatus)) :
				switch ($send->connectionStatus) :
					case 0: echo §('A connection request for {{'.$send->url.'}} was made, but the target did not respond. Maybe its not a Plexus driven website?.'); break;
					case 1: echo §('There already is a pending connection request for {{'.$send->url.'}}.'); break;
					case 2: echo §('There already is an established connection for {{'.$send->url.'}}.'); break;
					case 3: echo §('There already has been a connection request for {{'.$send->url.'}}. Sorry, but it has been refused.'); break;
				endswitch;
		   elseif (isset($send->message)) : ?>
				<?=§($send->message)?>
		<? else : ?>
				<?=§('Something went wrong.')?>
		<? endif; ?>
			</div>
	<? else : ?>
			<div class="infos"><?=§('Connection request successfull. You now need to wait until the target accepts/refuses to connect with you.')?></div>
	<? endif; ?>
<? endif; ?>
			<h1><?=§('Active connections')?></h1>
			<div class="connections">
<?php			
			$connections = $this->api->getConnections(2);
			if (empty($connections)) {
				echo §('Currently there are no active connections.');
			} else {
?>
				<ul>
<? foreach ($connections as $connection) : ?>
					<li>
						<a href="<?=$connection->url?>" target="blank"><?=$connection->name?></a>
						<span><?=date(§('d.m.Y H:i:s'), $connection->requested)?></span>
						<a href="<?=$this->addr->current('?disconnect='.$connection->id)?>" class="cancel"><?=§('Disconnect')?></a>
					</li>
<? endforeach; ?>			
				</ul>
<?php
			}
?>
			</div>
<?php
			return ob_get_clean();
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

		function getTitle()
		{
		}

		function getDescription()
		{
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
	}
?>
