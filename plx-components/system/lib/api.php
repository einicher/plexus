<?php
	class Api extends Core
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
			$merge = array(
				'error' => 'The API section you try to access does not exist.'
			);

			if (empty($levels[2])) {
				$merge = array(
					'name' => $this->getOption('site.name'),
					'owner' => $this->getOption('site.owner'),
					'ownerLink' => $this->getOption('site.ownerLink')
				);
			} else {
				switch (@$levels[2]) {
					case 'feed': $merge = $this->getFeed((object) $_POST); break;

					// PLEXUS CONNECT
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
					case 'editor': $merge = $this->editor(@$levels[3], @$levels[4]); break;
				}
			}

			header('Content-type: text/plain; charset=utf-8');
			echo $this->response($merge);
			exit;
		}

		function response($merge = array())
		{
			return $this->jsonFormat((object) array_merge(array(
				'plexus' => $this->addr->getHome(),
				'version' => $this->system->version,
				'time' => microtime(1)
			), $merge));
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

			$v = json_decode($this->tools->httpPostRequest($url['host'], @$url['path'].'/plx-api/validate', array(
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
			if (empty($o)) {
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
			$r = $this->tools->httpPostRequest($url['host'], '/plx-api/push?token='.$connection->token, array(
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
								jQuery.get('<?=$this->addr->getHome('plx-api/push-read?id=')?>' + jQuery(this).attr('id').replace('push-', ''));
							}
							jQuery(this).removeClass('unread');
							jQuery(this).addClass('read');
							plexusRefreshIndicators();
						}
					});
					jQuery('.pushes div.result .status').click(function() {
						if (jQuery(this).parent().parent().hasClass('unread')) {
						} else {
							jQuery.get('<?=$this->addr->getHome('plx-api/push-unread?id=')?>' + jQuery(this).parent().parent().attr('id').replace('push-', ''));
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

		function jsonFormat($json)
		{
			if (defined('JSON_PRETTY_PRINT')) {
				return json_encode($json, JSON_PRETTY_PRINT);
			}

			$tab = '	';
			$new_json = '';
			$indent_level = 0;
			$in_string = FALSE;

			if (is_object($json)) {
				$json_obj = $json;
			} else {
				$json_obj = json_decode($json);
			}

			if ($json_obj === FALSE) {
				return FALSE;
			}

			$json = json_encode($json_obj);
			$len = strlen($json);

			for ($c = 0; $c < $len; $c++) {
				$char = $json[$c];
				switch ($char) {

					case '{':
					case '[':
						if (!$in_string) {
							$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
							$indent_level++;
						} else {
							$new_json .= $char;
						}
					break;

					case '}':
					case ']':
						if (!$in_string) {
							$indent_level--;
							$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
						} else {
							$new_json .= $char;
						}
					break;

					case ',':
						if (!$in_string) {
							$new_json .= ",\n" . str_repeat($tab, $indent_level);
						} else {
							$new_json .= $char;
						}
					break;

					case ':':
						if (!$in_string) {
							$new_json .= ": ";
						} else {
							$new_json .= $char;
						}
					break;

					case '"':
						if ($c > 0 && $json[$c-1] != '\\') {
							$in_string = !$in_string;
						}

					default:
						$new_json .= $char;
					break;				   
				}
			}

			return $new_json;
		}

		function editor($section, $page)
		{
			if (empty($page)) {
				$page = 1;
			}
			if (!$this->access->granted()) {
				return array(
					'status' => 0,
					'message' => 'You do not have the necessary rights to access this api section.'
				);	
			}

			switch ($section) {
				case 'image':
				case 'gallery':
				case 'video':
				case 'file':
					return $this->editorBrowser($page, $section);
				break;
				case 'embeddings': return $this->editorEmbeddings($page); break;
				case 'upload':
					return $this->editorUpload();
				break;
			}
			return array(
				'status' => 0,
				'message' => 'No editor section called.'
			);	
		}

		function editorBrowser($page = 1, $section = 'image')
		{
			ob_start();
			$limit = 20;

			$sql = '';
			$search = '';
			$term = '';
			if (!empty($_GET['search'])) {
				$p = '"+'.str_replace(' ', ' +', $this->d->escape($_GET['search'])).'"';
				$sql = ' AND i.id=t.parent AND MATCH(t.value) AGAINST('.$p.' IN BOOLEAN MODE)';
				$search = '&search='.$_GET['search'];
				$term = $_GET['search'];
			}

			unset($_GET['search']);

			$sql = 'SELECT i.* FROM `'.$this->d->table('index').'` i'.(empty($sql) ? '' : ', `'.$this->d->table('textual').'` t').' WHERE i.type="'.strtoupper($section).'" '.$sql.' ORDER BY i.published DESC';
			$count = $this->d->query($sql);
			if (isset($count->num_rows)) {
				$count = $count->num_rows;
			} else {
				$count = 0;
			}
?>
			<div class="plxInterface plxEditorDialog plexusGUI">
			<?=$this->tools->pagination('plxImageSelector', $count, $page, $limit, 7);?>
			<div style="float: right; padding: 0 0 10px 0">
				<input type="text" style="font-size: 13px; width: 150px; height: 14px; vertical-align: top;" id="plxSelectorSearchField" value="<?=htmlspecialchars($term)?>" />
				<div id="plxSelectorSearchButton" class="button" style="vertical-align: top; height: 14px; line-height: 12px;"><?=§('Search')?></div>
			</div>
			<div class="clear"></div>
			<div style="width: 790px; height: 430px; overflow: hidden;">
				<div style="width: 800px; height: 440px;">
<?php
			if ($count) {
				$r = $this->d->query($sql.' LIMIT '.(($page*$limit)-$limit).','.$limit);
				if ($r->num_rows) {
					switch ($section) {
						case 'image':
							while ($i = $r->fetch_object('Image')) {
?>
						<img src="<?=$this->imageScaleLink($i->getOriginalLink(), 150, 100, $_GET['ajax'])?>" title="<?= empty($i->title) ? $i->file : $i->title ?>" onclick="tinyMCE.execCommand('mceInsertContent', false, '&lt;a class=&quot;lightThumb&quot; href=&quot;<?=$i->fullsize?>&quot; rel=&quot;lightboxPageContent&quot;&gt;&lt;img class=&quot;lightThumb&quot; src=&quot;<?=$i->resized?>&quot; alt=&quot;&quot; /&gt;&lt;/a&gt;'); jQuery.fancybox.close();" width="150" height="100" alt="" />
<?php
							}
						break;
						case 'gallery':
							while ($g = $r->fetch_object('Gallery')) {
								foreach ($g->images as $img) {
									$image = $this->type('IMAGE', $img);
									if (!empty($image->id)) {
										$g->thumb = $image->imageScaleLink($image->src, '150', '100', $_GET['ajax']);
										break;
									}
								}
?>
						<img src="<?=$g->thumb?>" onclick="tinyMCE.execCommand('mceInsertContent', false, '<div class=&quot;gallery&quot;><?=$g->id?></div>'); jQuery.fancybox.close(); tinyMCE.execCommand('mceRemoveControl', false, '<?=$_GET['editorId']?>');	tinyMCE.execCommand('mceAddControl', false, '<?=$_GET['editorId']?>');" width="150" height="100" alt="" />
<?php
							}
						break;
						case 'video':
							while ($v = $r->fetch_object('Video')) {
								$v->detect();
?>
						<img src="<?=$this->imageScaleLink($v->src, 100, 75)?>" onclick="tinyMCE.execCommand('mceInsertContent', false, '<div class=&quot;video&quot;><?=$v->id?></div>'); jQuery.fancybox.close(); tinyMCE.execCommand('mceRemoveControl', false, '<?=$_GET['editorId']?>');	tinyMCE.execCommand('mceAddControl', false, '<?=$_GET['editorId']?>');" width="150" height="100" alt="" />
<?php
							}
						break;
						case 'file':
							echo '<div id="wysiwygContentFiles"><ul>';
							while ($f = $r->fetch_object('File')) {
								if (empty($f->title)) {
									$f->title = $f->file;
								}
?>
						<li title="<?=$f->file?>" onclick="tinyMCE.execCommand('mceInsertContent', false, '<a class=&quot;download&quot; href=&quot;plx-file://<?=$f->target?>/<?=$f->file?>&quot;><?=htmlspecialchars($f->title)?></a>'); jQuery.fancybox.close();"><span class="plxWysiwygFileIcon"></span><?=$f->title?></li>
<?php
							}
							echo '</ul></div>';
						break;
					}
				}
			} else {
?>
			<p style="text-align: center; line-height: 300px; font-size: 4em; color: #D9D9D9;"><?=§('No matches')?></p>
<?php			
			}
?>
				</div>
			</div></div>
			<script type="text/javascript">
				jQuery('.plxInterface .plxPagination.plxImageSelector a').click(function() {
					var page = jQuery(this).attr('href').split('/');
					page = page[page.length-1];
					if (page.indexOf('?') != -1) {
						page = page.split('?');
						page = page[0];
					}
					if (isNaN(page)) {
						plxSelectorLoad(plxRoot + 'plx-api/editor/<?=$section?>?ajax=<?=urlencode($_GET['ajax'])?><?=$search?>');
					} else {
						plxSelectorLoad(plxRoot + 'plx-api/editor/<?=$section?>/' + page + '?ajax=<?=urlencode($_GET['ajax'])?><?=$search?>');
					}
					return false;
				});
				jQuery('#plxSelectorSearchField').keyup(function(event) {
					if (event.which == 13) {
						plxSelectorLoad(plxRoot + 'plx-api/editor/<?=$section?>' + '?ajax=<?=urlencode($_GET['ajax'])?>&search=' + jQuery(this).val());
					}  
				});
				jQuery('#plxSelectorSearchButton').click(function() {
					plxSelectorLoad(plxRoot + 'plx-api/editor/<?=$section?>' + '?ajax=<?=urlencode($_GET['ajax'])?>&search=' +jQuery('#plxSelectorSearchField').val());
				});
			</script>
<?php
			$data = ob_get_clean();

			return array(
				'status' => 1,
				'data' => $data
			);
		}

		function editorEmbeddings()
		{
			$dock = new Dock('plx.embedded', $_GET['contentId']);
			return array(
				'status' => 1,
				'data' => $dock->chooseWidget(TRUE, $_GET['editorId'])
			);
		}

		function editorUpload()
		{
			if ($this->access->granted('system.new')
				|| $this->access->granted('system.data.image')
				|| $this->access->granted('system.data.gallery')
				|| $this->access->granted('system.data.video')
				|| $this->access->granted('system.data.file')
			) {
				ob_start();
?>
				<ul class="editorChooseType">
<? if ($this->access->granted('system.new') || $this->access->granted('system.data.image')) : ?>
					<li class="image"><a href="<?=$this->addr->assigned('system.new')?>/<?=§('Image')?>?ajax=<?=urlencode($this->addr->getRoot())?>&lite2"><span class="icon"></span><?=$this->lang->get('Image')?></a></li>
<? endif; ?>
<? if ($this->access->granted('system.new') || $this->access->granted('system.data.gallery')) : ?>
					<li class="gallery"><a href="<?=$this->addr->assigned('system.new')?>/<?=§('Gallery')?>?ajax=<?=urlencode($this->addr->getRoot())?>&lite2"><span class="icon"></span><?=$this->lang->get('Gallery')?></a></li>
<? endif; ?>
<? if ($this->access->granted('system.new') || $this->access->granted('system.data.video')) : ?>
					<li class="video"><a href="<?=$this->addr->assigned('system.new')?>/<?=§('Video')?>?ajax=<?=urlencode($this->addr->getRoot())?>&lite2"><span class="icon"></span><?=$this->lang->get('Video')?></a></li>
<? endif; ?>
<? if ($this->access->granted('system.new') || $this->access->granted('system.data.file')) : ?>
					<li class="file"><a href="<?=$this->addr->assigned('system.new')?>/<?=§('File')?>?ajax=<?=urlencode($this->addr->getRoot())?>&lite2"><span class="icon"></span><?=$this->lang->get('File')?></a></li>
<? endif; ?>
				</ul>
				<script type="text/javascript">
					jQuery('ul.editorChooseType li a').fancybox({
						centerOnScroll: true,
						overlayOpacity: 0.5,
						overlayColor: '#000',
						transitionIn: 'elastic',
						transitionOut: 'elastic',
						onComplete: function(link) {
							jQuery('form.plexusFormAjax').ajaxForm({
								success: function(data) {
									jQuery.fancybox.close();
									tinyMCE.execInstanceCommand('<?=$_GET['editorId']?>', 'mceInsertContent', false, data);
									tinyMCE.execCommand('mceRemoveControl', false, '<?=$_GET['editorId']?>');
									tinyMCE.execCommand('mceAddControl', false, '<?=$_GET['editorId']?>');
									
								}
							});
						}
					});
				</script>
<?php
				$data = ob_get_clean();
				return array(
					'status' => 1,
					'data' => $data
				);	
			}
			return array(
				'status' => 0,
				'message' => 'You do not have the necessary rights to perform this action.'
			);		
		}
	}
?>
