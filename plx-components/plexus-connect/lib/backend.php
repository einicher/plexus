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
				array('dashboard', §('Dashboard'), $this->addr->assigned('system.plexus', 2), true, PlexusConnect::getUnreadCount()),
				array('requests', §('Requests'), $this->addr->assigned('system.plexus.requests', 2), false, PlexusConnect::getOpenRequests()),
				array('connections', §('Connections'), $this->addr->assigned('system.plexus.connections', 2), false),
			);
			return $this->t->get('system', 'backend.php', array(
				'main' => $main,
				'menu' => $menu,
				'requests' => PlexusConnect::getOpenRequests(),
				'unread' => PlexusConnect::getUnreadCount(),
				'backendID' => 'plexus'
			));
		}

		function dashboard()
		{
			require_once PLX_COMPONENTS.'plexus-connect/lib/api.php';
			$show = @$this->getOption('system.plexus.show', Access::$user->id)->value;
			return $this->t->get('plexus-connect', 'dashboard.php', array(
				'show' => $show
			));
		}

		function requests()
		{
			require_once PLX_COMPONENTS.'plexus-connect/lib/api.php';

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

			return $this->t->get('plexus-connect', 'requests.php', array(
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

			require_once PLX_COMPONENTS.'plexus-connect/lib/api.php';
			$connections = PlexusApi::instance()->getConnections(array(2,5));

			return $this->t->get('plexus-connect', 'connections.php', array(
				'connections' => $connections
			));
		}
	}
?>
