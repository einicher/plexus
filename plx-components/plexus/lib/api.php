<?php
	class PlexusAPI extends Api
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
			if (empty($levels[3])) {
				$merge = array(
					'message' => 'Welcome to the plexus connect api. Please choose a section.'
				);
			} else {
				$merge = false;
				switch ($levels[3]) {
					case 'validate': $merge = $this->connectionValidate((object) $_POST); break;
					case 'cancel': $merge = $this->connectionCancel((object) $_POST); break;
					case 'accept': $merge = $this->connectionAccept((object) $_POST); break;
					case 'refuse': $merge = $this->connectionRefuse((object) $_POST); break;
					case 'disconnect': $merge = $this->connectionDisconnect((object) $_POST); break;
					case 'push': $merge = $this->getPush((object) $_POST, @$_GET['token']); break;
					case 'push-read': $merge = $this->setPushStatus(@$_GET['id'], 1); break;
					case 'push-unread': $merge = $this->setPushStatus(@$_GET['id'], 0); break;
					case 'push-all-read': $merge = $this->setPushStatus(-1, 0); break;
					case 'push-view-mode': $merge = $this->setPushViewMode(@$_GET['mode']); break;
					case 'push-get-pushes': $merge = $this->getPushes(); break;
					case 'push-get-unread-count': $merge = $this->getPushesUnreadCount(); break;
					case 'push-get-requests-count': $merge = $this->getPushesRequestsCount(); break;
				}
			}

			return $merge;
		}

		function connectionRequest($url)
		{
			if (substr($url, 0, 4) != 'http') {
				$url = 'http://'.$url;
			}
			$check = $this->checkForConnection($url);
			if (!empty($check)) {
				return (object) array(
					'status' => 0,
					'connectionStatus' => $check->status,
					'url' => $check->url
				);
			}

			$u = parse_url($url);
			$token = sha1(md5(uniqid()));

			$this->setOption('plexus.connect.connection', json_encode(array(
				'url' => $url,
				'status' => 0,
				'token' => $token,
				'requested' => microtime(1),
				'name' => $url
			)), $token);

			$r = json_decode($this->tools->httpPostRequest($u['host'], @$u['path'], array(
				'plexusConnectionRequest' => $this->addr->getHome(),
				'plexusConnectionToken' => $token,
				'plexusConnectionName' => $this->getOption('site.name'),
				'plexusConnectionDescription' => $this->getOption('site.description')
			)));

			if (empty($r)) {
				$error = 'Invalid JSON code.';
			} elseif (!isset($r->status)) {
				$error = 'No status received.';
			} else {
				return $r;
			}

			return (object) array(
				'status' => 0,
				'message' => $error
			);
		}

		function connectionReceive($data)
		{
			$check = $this->checkForConnection($data->plexusConnectionRequest);
			if (!empty($check)) {
				return $this->response(array(
					'status' => 0,
					'connectionStatus' => $check->status,
					'url' => $check->url
				));
			}

			$url = parse_url($data->plexusConnectionRequest);
			$token = sha1(md5(uniqid()));

			$v = json_decode($this->tools->httpPostRequest($url['host'], @$url['path'].'/plx-api/connect/validate', array(
				'connectionToken' => $data->plexusConnectionToken,
				'confirmRequest' => $this->addr->getHome(),
				'confirmToken' => $token,
				'confirmName' => $this->getOption('site.name'),
				'confirmDescription' => $this->getOption('site.description')
			)));

			if (empty($v)) {
				$error = 'Invalid JSON code.';
			} elseif (empty($v->confirmToken) || $v->confirmToken != $token) {
				$error = 'Empty or wrong confirm token.';
			} elseif (empty($v->status) || $v->status != 1) {
				$error = 'Empty status or an error happend.';
			} elseif ($v->status == 1) {
				$this->setOption('plexus.connect.connection', json_encode(array(
					'url' => $data->plexusConnectionRequest,
					'name' => $data->plexusConnectionName,
					'status' => 1,
					'requested' => microtime(1),
					'description' => $data->plexusConnectionDescription,
				)), $data->plexusConnectionToken);
				return $this->response(array(
					'status' => 1
				));
			}
			return $this->response(array(
				'status' => 0,
				'error' => $error
			));
		}

		function connectionValidate($data)
		{
			$connection = $this->getOption('plexus.connect.connection', $data->connectionToken);
			if (empty($connection)) {
				return array(
					'status' => 0,
					'message' => 'Invalid connection token.'
				);
			} else {
				$c = json_decode($connection->value);
				$c->url = $data->confirmRequest;
				$c->name = $data->confirmName;
				$c->status = 1;
				$c->validated = microtime(1);
				$c->description = $data->confirmDescription;
				$this->setOption($connection->id, json_encode($c));
				return array(
					'status' => 1,
					'confirmToken' => $data->confirmToken
				);
			}
		}

		function connectionAccept($o)
		{
			$o = $this->getOption('plexus.connect.connection', $o->connectionToken);
			if (empty($o)) {
				$error = 'Invalid token. This connection does not exist in the database of the target website.';
			} else {
				$c = json_decode($o->value);
				$c->status = 2;
				$c->accepted = microtime(1);
				$this->setOption($o->id, json_encode($c));
				return array(
					'status' => 1
				);
			}	
			return array(
				'status' => 0,
				'message' => $error
			);		
		}

		function connectionRefuse($o)
		{
			$o = $this->getOption('plexus.connect.connection', $o->connectionToken);
			if (empty($o)) {
				$error = 'Invalid token. This connection does not exist in the database of the target website.';
			} else {
				$c = json_decode($o->value);
				$c->status = 3;
				$c->refused = microtime(1);
				$this->setOption($o->id, json_encode($c));
				return array(
					'status' => 1
				);
			}	
			return array(
				'status' => 0,
				'message' => $error
			);		
		}

		function connectionCancel($o)
		{
			$o = $this->getOption('plexus.connect.connection', $o->connectionToken);
			if (empty($o)) {
				$error = 'Invalid token. This connection does not exist in the database of the target website.';
			} else {
				$c = json_decode($o->value);
				$c->status = 4;
				$c->canceled = microtime(1);
				$this->setOption($o->id, json_encode($c));
				return array(
					'status' => 1
				);
			}
			return array(
				'status' => 0,
				'message' => $error
			);
		}

		function connectionDisconnect($o)
		{
			$o = $this->getOption('plexus.connect.connection', $o->connectionToken);
			if (empty($o)) {
				$error = 'Invalid token. This connection does not exist in the database of the target website.';
			} else {
				$c = json_decode($o->value);
				$c->status = 5;
				$c->disconnected = microtime(1);
				$this->setOption($o->id, json_encode($c));
				return array(
					'status' => 1
				);
			}	
			return array(
				'status' => 0,
				'message' => $error
			);		
		}

		function getConnections($status = false, $exclude = '')
		{
			$connections = array();
			$r = $this->d->query('SELECT * FROM '.$this->d->table('options').' WHERE name="plexus.connect.connection"');
			if ($r->num_rows) {
				while ($o = $r->fetch_object()) {
					$c = json_decode($o->value);
					$c->id = $o->id;
					$c->token = $o->association;
					if (($status === false || $status == $c->status || (is_array($status) && in_array($c->status, $status)))
					&& (empty($exclude) || (is_numeric($exclude) && $exclude != $c->status) || (is_array($exclude) && !in_array($c->status, $exclude)))
					) {
						$connections[] = $c;
					}
				}
			}
			return $connections;
		}

		function checkForConnection($url)
		{
			$r = $this->d->query('SELECT * FROM '.$this->d->table('options').' WHERE name="plexus.connect.connection"');
			if ($r->num_rows) {
				while ($o = $r->fetch_object()) {
					$c = json_decode($o->value);
					if (stripos($url, $c->url) !== FALSE) {
						$c->id = $o->id;
						$c->token = $o->association;
						return $c;
					}
				}
			}
			return FALSE;
		}

		function getPush($data, $token)
		{
			$o = $this->getOption('plexus.connect.connection', $token);
			if (empty($o) || empty($token)) {
				$error = 'Unknown connection.';
			} else {
				$c = json_decode($o->value);
				if ($c->status != 2) {
					$error = 'Connection status is {{'.$c->status.'}} on this side!';
				} else {
					if (!$this->db->checkForTable($this->d->table('pushes', FALSE))) {
						mysql_query('
							CREATE TABLE '.$this->d->table('pushes').' (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `type` varchar(255) NOT NULL,
							  `title` varchar(255) NOT NULL,
							  `description` text NOT NULL,
							  `tags` varchar(255) NOT NULL,
							  `link` varchar(255) NOT NULL,
							  `published` int(11) NOT NULL,
							  `received` int(11) NOT NULL,
							  `connection_id` int(11) NOT NULL,
							  `host` varchar(255) NOT NULL,
							  `hostname` varchar(255) NOT NULL,
							  `status` int(11) NOT NULL,
							  PRIMARY KEY (`id`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8;
						') OR exit(mysql_error());
					}

					$this->d->query('
						INSERT INTO
							'.$this->d->table('pushes').'
						SET
							`type`="'.$this->d->escape($data->type).'",
							`title`="'.$this->d->escape($data->title).'",
							`description`="'.$this->d->escape($data->description).'",
							`tags`="'.$this->d->escape($data->tags).'",
							`link`="'.$this->d->escape($data->link).'",
							`published`="'.$this->d->escape($data->published).'",
							`received`='.time().',
							`connection_id`='.$o->id.',
							`host`="'.$this->d->escape($c->url).'",
							`hostname`="'.$this->d->escape($c->name).'"
					') OR exit(mysql_error());

					return array(
						'status' => 1
					);
				}
			}
			return array(
				'status' => 0,
				'message' => $error
			);	
		}

		function setPush($connection, $data)
		{
			$url = parse_url($connection->url);
			$r = $this->tools->httpPostRequest($url['host'], '/plx-api/connect/push?token='.$connection->token, array(
				'type' => $data->type,
				'title' => $data->getTitle(),
				'description' => $data->getDescription(),
				'tags' => $data->getKeywords(),
				'link' => $data->getLink(true),
				'published' => $data->published
			));
			return $r;
		}

		function setPushStatus($id, $status = 1)
		{
			if ($this->access->granted('system.push.status')) {
				if ($id == -1) {
					$this->d->query('UPDATE '.$this->d->table('pushes').' SET status=1');
				} else {
					$this->d->query('UPDATE '.$this->d->table('pushes').' SET status='.$status.' WHERE id='.$id);
				}
				return array(
					'status' => 1
				);
			}
			return array(
				'status' => 0,
				'message' => 'You do not have the necessary rights to perform this action.'
			);	
		}

		function setPushViewMode($mode)
		{
			if ($this->access->granted('system.plexus.show')) {
				$this->setOption('system.plexus.show', $mode, Access::$user->id);
				return array(
					'status' => 1
				);
			}
			return array(
				'status' => 0,
				'message' => 'You do not have the necessary rights to perform this action.'
			);	
		}

		function getPushes()
		{
			ob_start();
			$show = @$this->getOption('system.plexus.show', Access::$user->id)->value;
			$sql = '';
			if (empty($show)) {
				$sql = ' WHERE status=0';
			}
			$r = $this->d->query('SELECT * FROM '.$this->d->table('pushes').$sql.' ORDER BY received DESC LIMIT 20');
			if ($r && $r->num_rows) {
				while ($fetch = $r->fetch_object()) {
?>
					<div id="push-<?=$fetch->id?>" class="result <?php echo $fetch->status == 0 ? 'unread' : 'read' ?>">
						<div class="col1">
							<div class="status" title="<? echo $fetch->status == 0 ? §('Mark as read') : §('Mark as unread') ?>"></div>
							<a href="<?=$fetch->host?>" class="hostname" target="_blank"><?=$fetch->hostname?></a>
						</div>
						<a href="<?=$fetch->link?>" class="title" target="_blank">
							<span class="type"><?=$fetch->type?></span>
							<?=$fetch->title?>
							<span class="description"><?=$fetch->description?></span>
						</a>
						<a href="<?=$fetch->link?>" class="received" target="_blank"><?=$this->tools->detectTime($fetch->received, 2)?></a>
						<div class="clear"></div>
					</div>
<?php
				}
?>
				<script type="text/javascript">
					plxPushesBlocker = 0;
					jQuery('.pushes div.result').click(function() {
						if (plxPushesBlocker) {
							plxPushesBlocker = 0;
						} else {
							if (jQuery(this).hasClass('unread')) {
								jQuery.get('<?=$this->addr->getHome('plx-api/connect/push-read?id=')?>' + jQuery(this).attr('id').replace('push-', ''));
							}
							jQuery(this).removeClass('unread');
							jQuery(this).addClass('read');
							plexusRefreshIndicators();
						}
					});
					jQuery('.pushes div.result .status').click(function() {
						if (jQuery(this).parent().parent().hasClass('unread')) {
						} else {
							jQuery.get('<?=$this->addr->getHome('plx-api/connect/push-unread?id=')?>' + jQuery(this).parent().parent().attr('id').replace('push-', ''));
							jQuery(this).parent().parent().removeClass('read');
							jQuery(this).parent().parent().addClass('unread');
							plxPushesBlocker = 1;
							plexusRefreshIndicators();
						}
					});
				</script>
<?php
			} else {
?>
		<p><?=§('No unread items.')?></p>
<?php					
			}
			return array(
				'status' => 1,
				'content' => ob_get_clean()
			);
		}

		function getPushesUnreadCount()
		{
			return array(
				'status' => 1,
				'count' => Plexus::getUnreadCount()
			);
		}

		function getPushesRequestsCount()
		{
			return array(
				'status' => 1,
				'count' => Plexus::getOpenRequests()
			);
		}
	}
?>
