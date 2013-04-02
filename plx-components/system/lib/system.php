<?php
	class System extends Core
	{
		static $instance;

		function instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function permalink($level, $levels, $cache)
		{
			header('Location:'.$this->addr->getHomeLink($this->addr->getLevel(2, $levels)));
			exit;
		}

		function search($level, $levels, $cache)
		{
			$this->tpl->connect('siteFeedThumb', 100);
			if (empty($levels[2])) {
				$search = (object) array(
					'pattern' => '',
					'action' => $this->addr->getRoot($this->addr->assigned('system.search')),
					'results' => ''
				);
				$this->tpl->cut('result.php', 'search', array('search' => $search));
				$page = new Page($this->lang->get('Search'), $this->tpl->get('result.php', array(
					'search' => $search
				)));
			} else {
				$search = (object) array(
					'pattern' => urldecode(@$levels[2]),
					'action' => $this->addr->getRoot($this->addr->assigned('system.search')),
					'results' => $this->pdb->search(array(
						'pattern' => urldecode(@$levels[2]),
						'order' => ' ORDER BY score DESC'
					))
				);
				$this->tpl->cut('result.php', 'search', array('search' => $search));
				$page = new Page($this->lang->get('Search for “{{'.urldecode($levels[2]).'}}”'), $this->tpl->get('result.php', array(
					'search' => $search
				)));
			}
			$this->tpl->set('result.php');
			return $page;
		}

		function tags($level, $levels, $cache)
		{
			if (empty($levels[2])) {
				require_once PLX_SYSTEM.'lib/widget-tag-cloud.php';
				$tags = new TagCloudWidget;
				$tags = $this->observer->notify('system.tags', $tags);
				return new Page($this->lang->get('Tags'), $tags->view(1));
			} else {
				$this->tpl->connect('siteFeedThumb', 100);
				$tag = urldecode(str_replace('+', ' ', $levels[2]));
				$search = $this->pdb->search(array(
					'tags' => $tag
				));
				return new Page($this->lang->get('Tag “{{'.$tag.'}}”'), $search);
			}
		}

		function login($level, $levels, $cache)
		{
			if ($this->access->granted()) {
				return new Page($this->lang->get('Login'), $this->lang->get('You are currently logged in.'));
			} else {
				return $this->access->showLogin($level, $levels, $cache);
			}
		}

		function logout($level, $levels, $cache)
		{
			$this->access->logout();
			exit;
		}

		function plxUsersPassword()
		{
			if (!empty($_POST['request'])) {
				if (!empty($_POST['request'])) {
					$fetch = mysql_fetch_object(mysql_query('SELECT i.* FROM '.Database::table('index').' i,  '.Database::table('properties').' p WHERE 
						(p.name="email" OR p.name="name") AND p.value="'.Database::escape($_POST['request']).'"
						AND p.parent=i.id AND i.type="USER"'
					));
					if (empty($fetch)) {
						$this->error($this->lang->get('Sorry, but we found no account with a username or email like the value you entered.'));
					} else {
						$user = $this->type($fetch);

						$temporary = substr(md5(time()+rand(0,999)), 0, 6);
						$t = (object) array(
							'password' => sha1($temporary),
							'time' => time()
						);
						$this->setOption('temporary', json_encode($t), $user->id, TRUE);

						$header = 'From: '.$this->getOption('site.name').' <'.$this->getOption('site.mail').'>' . "\r\n" .
						'Content-type: text/plain; charset=UTF-8' . "\r\n" .
					    'X-Mailer: PHP/' . phpversion();
					    $subject = $this->lang->get('Your temporary password for {{'.$this->getOption('site.name').'}}');
					    $message = Template::get2('mail-lost-password.php', array(
					    	'user' => $user->name,
					    	'email' => $user->email,
					    	'password' => $temporary,
					    	'website' => $this->getOption('site.name'),
					    	'homepage' => str_replace('http://', '', substr($this->addr->getHome(), 0, -1)),
					    	'login' => $this->addr->getHome($this->addr->getAddress('system.login'))
					    ));
						mail($user->name.' <'.$user->email.'>', $subject, $message, $header);
						return new Page($this->lang->get('Success'), $this->lang->get('A temporary password was sent to you via email.'));
					}
				}
			}
			return new Page($this->lang->get('Lost password'), Template::get2('lost-password.php'));
		}

		function plxUsers($level, $levels, $cache)
		{
		}

		function plxGroups($level, $levels, $cache)
		{
		}

		function getFavicon($level, $levels, $cache)
		{
			$ico = $this->tpl->locateFile('favicon.ico');
			$create = getimagesize($ico);
			if ($create == FALSE) {
				return FALSE;
			} else {
				switch ($create['mime']) {
					case 'image/gif':
						$src = ImageCreateFromGif($ico);
					break;
					case 'image/jpeg':
						$src = ImageCreateFromJpeg($ico);
					break;
					case 'image/png':
						$src = ImageCreateFromPng($ico);
					break;
					default:
						return;
				}
				header('Content-type: image/png');
				$img = ImageCreateTrueColor($create[0], $create[1]);

				if ($create[2] == 1 || $create[2] == 3) {
					imagealphablending($img, FALSE);
					imagesavealpha($img, TRUE);
					$transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
					imagefilledrectangle($img, 0, 0, $create[0], $create[1], $transparent);
				}

				imagecopyresampled($img, $src, 0, 0, 0, 0, $create[0], $create[1], $create[0], $create[1]);
				ImagePng($img);
				ImageDestroy($img);
			}
		}

		function getCss()
		{
			$exclude = array();
			if (isset($_GET['exclude'])) {
				$exclude = explode(',', $_GET['exclude']);
			}
			$style = '';
			if (!in_array('system.css', $exclude)) {
				$style .= preg_replace('/url\(\'(.*)\'\)/ieU', '\'url(\\\'\'.$this->tpl->locateFile(\'\\1\').\'\\\')\'', $this->tpl->get('system.css'));
				$style .= "\n";
			}
			if ($exclude != 'all') {
				foreach (Control::$activeComponentsDirs as $c) {
					$file = $this->tpl->locateFile('style.css', $c);
					if (file_exists($file) && !in_array($c.'/style.css', $exclude)) {
						$style .= "\n/*".$file."*/\n\n".preg_replace('/url\(\'(.*)\'\)/ieU', '\'url(\\\'\'.$this->tpl->locateFile(\'\\1\', \''.$c.'\').\'\\\')\'', $this->tpl->get($file));
					}
					$style .= "\n";
				}
			}
			if (!in_array('style.css', $exclude)) {
				$style .= preg_replace('/url\(\'(.*)\'\)/ieU', '\'url(\\\'\'.$this->tpl->locateFile(\'\\1\').\'\\\')\'', $this->tpl->get('style.css'));
			}
			header('content-type: text/css; charset=utf-8');
			return $style;
		}

		function getAtom($level, $levels, $cache)
		{
			header('content-type: text/xml; charset=utf-8');
			$items = array();
			$updated = '';
			while ($fetch = $this->db->fetch('SELECT * FROM '.$this->db->table('index').' WHERE status=1 ORDER BY published DESC LIMIT 20', 1)) {
				$fetch = $this->type($fetch);
				if (empty($updated)) {
					$updated = $fetch->published;
				}
				if (empty($fetch->tags)) {
					$tags = array();
				} else {
					$tags = explode(',', $fetch->tags);
					foreach ($tags as $key => $tag) {
						$tag = trim($tag);
						if (empty($tag)) {
							unset($tags[$key]);
						} else {
							$tags[$key] = (object) array(
								'name' => htmlspecialchars($tag),
								'link' => str_replace(' ', '%20', $this->addr->assigned('system.tags', '', 1).'/'.urlencode($tag))
							);
						}
					}
				}

				$items[] = (object) array(
					'link' => $fetch->getLink(1),
					'title' => htmlspecialchars($fetch->getTitle()),
					'published' => date('c', $fetch->published),
					'updated' => date('c', $fetch->published),
					'summary' => htmlspecialchars($fetch->getDescription()),
					'tags' => $tags
				);
			}

			$feed = (object) array(
				'link' => $this->addr->getHome('atom.xml'),
				'title' => htmlspecialchars($this->getOption('site.name')),
				'updated' => date('c', $updated),
				'generatorURI' => htmlspecialchars($this->system->home),
				'generatorVersion' => htmlspecialchars($this->system->version),
				'generatorName' => htmlspecialchars($this->system->name),
				'authorName' => htmlspecialchars($this->getOption('site.owner')),
				'authorURI' => htmlspecialchars($this->getOption('site.ownerLink'))
			);

			return Template::get2('atom.xml', array(
				'feed' => $feed,
				'items' => $items
			));
		}

		function getSitemap($level, $levels, $cache)
		{
			header('content-type: text/xml; charset=utf-8');

			$xsl = $this->tpl->locateFile('sitemap.xsl');
			if (!file_exists($xsl)) {
				$xsl = PLX_SYSTEM.'theme/sitemap.xsl';
			}

			$xsl = '
<?xml-stylesheet type="text/xsl" href="'.$xsl.'"?>
';
			$exclude = array();
			foreach (Core::$types as $type => $values) {
				if (!empty($values['options']['excludeInSitemap'])) {
					$exclude[] = $type;
				}
			}
			if (empty($exclude)) {
				$exclude = '';
			} else {
				if (count($exclude) > 1) {
					$exclude = ' AND type NOT IN ("'.implode('","', $exclude).'")';
				} else {
					$exclude = ' AND type!="'.$exclude[0].'"';
				}
			}

			$urls = '';
			$sql = 'SELECT `id`,`published`,`type`,`status` FROM '.$this->db->table('index').' WHERE address!="" AND (status=1 OR status=2) AND published<='.time().$exclude.' ORDER BY published DESC';
			$qry = mysql_query($sql);
			while ($fetch = mysql_fetch_object($qry)) {
				if ($fetch->type == 'IMAGE' && $fetch->status == 2) {
					continue;
				}
				$urls .= '
   <url>
	<loc>'.htmlspecialchars($this->addr->getHomeLink($fetch->id)).'</loc>
	<lastmod>'.date('c', $fetch->published).'</lastmod>
   </url>';
			}
?><?='<?xml version="1.0" encoding="UTF-8"?>'.$xsl?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?=$urls?>
</urlset>
<?php
		}
	}
?>
