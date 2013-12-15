<?php
	class Trackback extends Core
	{
		static $instance;

		static public function &instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function control($level, $levels, $cache)
		{
			if (Control::getInstance()->paginationActive || (empty($_POST) && !$this->getOption('site.trackbacks'))) {
				return;
			}
			$object = $cache[count($cache)-2];
			$object = $this->getData($object);
			if (!empty($_POST) && !isset($_POST['trackbackCaptcha'])) {
				if (!$this->getOption('site.trackbacks')) {
					echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<response>
	<error>1</error>
	<message>Trackbacks are disabled on <?=$this->addr->getHome()?>.</message>
</response>
<?php
					exit;
				}
				if (!isset($_GET['hash'])) {
					echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<response>
	<error>1</error>
	<message>Your trackback does not have a valid trackback hash.</message>
</response>
<?php
					exit;
				} else {
					$hash = $this->getOption('trackbackHash', $_GET['hash']);
					if (empty($hash)) {
						echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<response>
	<error>1</error>
	<message>Your trackback does not have a valid trackback hash.</message>
</response>
<?php
						exit;
					} else {
						$hash = explode(',', $hash->value);
						if ($hash[0] != $object->id || time() > $hash[1]+960) {
							echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<response>
	<error>1</error>
	<message>Your trackback hash is invalid, maybe it is timed out or belongs to another link, get a new one.</message>
</response>
<?php
							exit;
						} else {
							$this->delOption('trackbackHash', '', $_GET['hash']);
						}
					}
				}
				$receive = $this->receive($object, (object) $_POST);
				if ($receive == 1) {
					echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<response>
	<error>0</error>

	<rss version="2.0">
		<channel>
			<title><?=$this->getOption('site.name')?></title>
			<link><?=$this->addr->getHome()?></link>
			<language><?=$object->language?></language>

			<item>
				<title><?=$object->title?></title>
				<link><?=$object->getLink(true)?></link>
				<description><?=$object->getDescription()?></description>
			</item>
		</channel>
	</rss>
</response>
<?php
				} elseif ($receive == 2) {
					echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<response>
	<error>1</error>
	<message>This trackback already exists.</message>
</response>
<?php
				} else {
					echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<response>
	<error>1</error>
	<message>Something went wrong.</message>
</response>
<?php
				}
				exit;
			}

			$hash = '';
			if (!empty($_POST['trackbackCaptcha'])) {
				$hash = substr(sha1(md5(time())), 5, 5);
				$this->setOption('trackbackHash', $object->id.','.time(), $hash);
			}

			return new Page('Trackbacks', $this->t->get('system', 'trackbacks.php', array(
				'data' => $object,
				'hash' => $hash
			)));
		}

		function manageTrackbacks($data)
		{
			if (empty($data->trackbacks)) {
				return;
			}

			$trackbacks = array();

			foreach ($data->trackbacks['link'] as $key => $trackback) {
				if (empty($trackback)) {
					continue;
				}
				if ($data->trackbacks['status'][$key] == 1) {
					$trackbacks['link'][] = $trackback;
					$trackbacks['status'][] = 1;
				} else {
					$send = @explode("\r\n\r\n", $this->sendTrackback($trackback, $data));
					$send = @simplexml_load_string(@$send[1]);
					if (isset($send->error) && $send->error == 0) {
						$tb = $trackbacks['link'][] = $trackback;
						$trackbacks['status'][] = 1;
						$trackback = (object) array(
							'id' => $data->id,
							'target' => $trackback,
							'time' => time()
						);
						if (isset($send->rss->channel->item)) {
							$item = $send->rss->channel->item;
							$trackback->title = (string) $item->title;
							$trackback->url = (string) $item->link;
							$trackback->blog_name = (string) $send->rss->channel->title;
							$trackback->excerpt = (string) $item->description;
						}
						$this->setOption('trackback', json_encode($trackback), $data->id, true);
						$this->info(§('Trackback to {{'.$tb.'}} successfull.'), true);
					} else {
						$trackbacks['link'][] = $trackback;
						$trackbacks['status'][] = 0;
						$this->error($this->lang->get('Error on trackbacking “{{'.$trackback.'}}”: {{'.htmlspecialchars($send->message).'}}'), true);
					}
				}
			}

			$data->trackbacks = '';
			if (!empty($trackbacks)) {
				$data->trackbacks = json_encode($trackbacks);
			}
			PlexusDataControl::saveProperty($data->id, 'trackbacks', $data->trackbacks);
		}

		function sendTrackback($trackback, $data)
		{
			$target = $trackback;
			$parts = explode('/', $target);

			$buffer = '';
			$host = $parts[2];
			unset($parts[0], $parts[1], $parts[2]);
			$trackback_url = '/'.implode('/', $parts);

			$title = urlencode($data->getTitle());
			$url = urlencode($data->getLink(TRUE));
			$excerpt = urlencode($data->getDescription(-1));
			$blog_name = urlencode($this->getOption('site.name'));
			$data = 'title='.$title.'&url='.$url.'&excerpt='.$excerpt.'&blog_name='.$blog_name;

			$fp = fsockopen($host, 80, $errno, $errstr, 30);
			if (!$fp) {
				$buffer .= "$errstr ($errno)<br />\n";
			} else {
				$out  = "POST ".$trackback_url." HTTP/1.1\r\n";
				$out .= "Host: ".$host."\r\n";
				$out .= "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n";
				$out .= "Content-Length: ".strlen($data)."\r\n";
				$out .= "Connection: Close\r\n\r\n";
				$out .= $data;
				fwrite($fp, $out);
		
				while (!feof($fp)) {
					$buffer .= fgets($fp, 128);
				}
				fclose($fp);
			}
			return $buffer;
		}

		function receive($object, $data)
		{
			$check = $this->getOption('trackback', $object->id);
			if (!empty($check)) {
				if (!is_array($check)) {
					$check = array($check);
				}
				foreach ($check as $t) {
					$t = json_decode($t->value);
					if ($t->url == $data->url) {
						return 2;
					}
				}
			}

			$counter = $this->getOption('pendingTrackbacks', '', true);
			if (empty($counter)) {
				$this->setOption('pendingTrackbacks', 1);
			} else {
				$this->setOption($counter->id, $counter->value++);
			}

			$trackback = (object) array(
				'title' => $data->title,
				'url' => $data->url,
				'blog_name' => $data->blog_name,
				'excerpt' => $data->excerpt,
				'ip' => @$_SERVER['REMOTE_ADDR'],
				'origin' => @$_SERVER['HTTP_REFERER'],
				'time' => time()
			);
			$this->setOption('trackback', json_encode($trackback), $object->id, true);

			return 1;
		}

		static function getPendingTrackbacks()
		{
			return Core::getOption('pendingTrackbacks');
		}

		function preferencesPage()
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
	}
?>
