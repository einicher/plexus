<?php

	define('PLX_START', microtime(TRUE));

	error_reporting(E_ALL);
	ini_set('display_errors', true);

	$path = str_replace(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']), '', $_SERVER['REQUEST_URI']);
	if (substr($path, 0, 1) == '/') {
		$path = substr($path, 1);
	}
	define('PLX_ADDR_PATH', $path);

	if (substr(PLX_ADDR_PATH, -1) == '/' && substr(PLX_ADDR_PATH, -3, 3) != '../' && substr(PLX_ADDR_PATH, -2, 2) != './') {
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.substr($_SERVER['REQUEST_URI'], 0, -1));
		header('Connection: close');
		exit;
	}

	if (!defined('PLX_ROOT')) define('PLX_ROOT', '');
	if (!defined('PLX_STORAGE')) define('PLX_STORAGE', PLX_ROOT.'plx-storage/');
	if (!defined('PLX_MULTI')) define('PLX_MULTI', PLX_STORAGE.'multi/');

	if (!empty($_GET['sid'])) {
		session_id($_GET['sid']);
	}
	session_start(PLX_ADDR_PATH);

	// PAGE CACHE
	if (empty($_POST) && !isset($_SESSION['user']) && !isset($_COOKIE['user']) && !isset($_GET['crawl'])) {
		$url = parse_url('http://'.$_SERVER['SERVER_NAME'].'/'.PLX_ADDR_PATH);
		$storage = PLX_STORAGE;
		if (file_exists(PLX_MULTI)) {
			$storage = PLX_MULTI.$_SERVER['SERVER_NAME'].'/';
		}
		if (file_exists($storage.'no-cache.txt')) {
			$noCache = file_get_contents($storage.'no-cache.txt');
			if (substr($noCache, 0, 8) == 'DISABLED') {
				$overrideCache = true;
			} else {
				$noCache = explode("\n", str_replace("\r", '', $noCache));
				if (in_array($url['path'], $noCache)) {
					$overrideCache = true;
				}
			}
		}
		if (!isset($overrideCache) && file_exists($storage)) {
			$pc = $storage.'page-cache/';
			if (!file_exists($pc)) {
				mkdir($pc);
				chmod($pc, 0777);
			}
			$pageCache = $pc.sha1($url['path']);
			if (isset($_SERVER['HTTP_PRAGMA']) && $_SERVER['HTTP_PRAGMA'] == 'no-cache') {
				// CTRL + F5
			} else {
				if (isset($pageCache) && file_exists($pageCache)) {
					if (substr($url['path'], -4) == '.css') {
						header('Content-type: text/css; charset=UTF-8');
					}
					if (substr($url['path'], -4) == '.xml') {
						header('Content-type: application/xml; charset=UTF-8');
					}
					if (substr($url['path'], -3) == '.js') {
						header('Content-type: text/javascript; charset=UTF-8');
					}
					echo file_get_contents($pageCache);
					exit;
				}
			}
		}
	}

	if (!defined('PLX_COMPONENTS')) define('PLX_COMPONENTS', PLX_ROOT.'plx-components/');
	if (!defined('PLX_SYSTEM')) define('PLX_SYSTEM', PLX_COMPONENTS.'system/');
	if (!defined('PLX_RESOURCES')) define('PLX_RESOURCES', PLX_ROOT.'plx-resources/');
	if (!defined('PLX_QUIET')) define('PLX_QUIET', FALSE);

	// PLEXUS IMAGE CACHE
	if (substr(PLX_ADDR_PATH, 0, 9) == 'plx-cache') {
        if (isset ($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) < time()) {
                header ('HTTP/1.1 304 Not Modified');
                exit;
			}
		}

		require_once PLX_RESOURCES.'plx-cache.php';
		$cache = new PlexusCache(array(
			'width' => @$_GET['w'],
			'height' => @$_GET['h'],
			'path' => substr(PLX_ADDR_PATH, 10)
		));

		echo $cache->view();
		exit;
	}

	include_once PLX_SYSTEM.'functions.php';

	$control = new Control;

	// PLEXUS FILES
	if (substr(PLX_ADDR_PATH, 0, 8) == 'plx-file') {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) < time()) {
                header ('HTTP/1.1 304 Not Modified');
                exit;
			}
		}
		$file = $control->getStorage(str_replace('plx-file/', '', PLX_ADDR_PATH));
		if (substr(PLX_ADDR_PATH, -4, 4) == '.pdf') {
			header('Content-Type: application/pdf');
		} else {
			header('Content-Type: application/octet-stream');
		}
		echo file_get_contents($file);
		exit;
	}

	if (!PLX_QUIET) {
		ob_start();
		echo $control->view();
		$cache = ob_get_clean();
		if (isset($pageCache)
			&& (isset(Control::$content->type) && Control::$content->type != 'ERROR404')
			&& !$control->a->assignedIsActive('system.login')
			&& !$control->a->assignedIsActive('system.logout')
			&& !$control->a->assignedIsActive('system.edit')
			&& !$control->a->assignedIsActive('system.create')
			&& !$control->a->assignedIsActive('system.create.type')
			&& !$control->a->assignedIsActive('system.new')
			&& !$control->a->assignedIsActive('system.new.type')
			&& !isset($_GET['crawl'])
			&& Control::$cache == true
		) {
			$control->cache($cache, $pageCache);
		}

		if (isset($_GET['crawl'])) {
			echo $control->crawl($cache);
		} else {
			echo $cache;
		}
	}

	if (isset($_GET['debug']) || isset($control->conf->debug)) {
		echo '<hr /><pre>';
		print_r(Core::$debug);
		echo 'READY: '.(microtime(1)-PLX_START).'</pre>';
	}
?>
