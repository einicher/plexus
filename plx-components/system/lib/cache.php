<?php
	class Cache extends Core
	{
		static $instance;

		static function instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function preferences($level, $levels, $cache)
		{
			if (isset($levels[3]) && $levels[3] == 'clear') {
				return $this->clear();
			}
			ob_start();
?>
			<h1><?=§('Cache')?></h1>
			<p><?=§('In cache parts of a webpage are temporarily saved to increase the loading time and to reduce the number of accesses on the database.')?></p>
			<p><?=§('If changes you made to the website are not visible to the public, you might need to clear the cache so the outdated data will be deleted from the cache.')?></p>
			<p><?=§('{{'.$this->getPageCacheCount().'}} page cache files')?> <a href="<?=$this->addr->assigned('system.preferences.cache.clear')?>?exclude=images" class="button"><?=§('Clear page cache')?></a></p>
			<p><?=§('{{'.$this->getImageCacheCount().'}} image cache files')?> <a href="<?=$this->addr->assigned('system.preferences.cache.clear')?>?exclude=pages" class="button"><?=§('Clear image cache')?></a></p>
			<p><a href="<?=$this->addr->getHome()?>?crawl=recursive" target="_blank"><?=§('Autocrawl (experimental)')?></a><br />
			<?=§('Calling this link will start a self crawl of your website, simulating a search crawler like those from Google. It will create cache files of all your pages and images recursivly, meaning following all links on your site to create a complete cache of your site.')?>
			</p>
			<p><a class="button" href="<?=$this->addr->assigned('system.preferences.cache.clear')?>"><?=§('Clear cache')?></a></p>
<?php
			return ob_get_clean();
		}

		function clearPageCache()
		{
			$success = array();
			$fail = array();
			$p = Core::getStorage('page-cache/');
			$d = opendir($p);
			while ($c = readdir($d)) {
				if ($c != '..' && $c != '.') {
					if (unlink($p.$c)) {
						$success[] = $p.$c;
					} else {
						$fail[] = $p.$c;
					}
				}
			}
			return (object) array(
				'success' => $success,
				'fail' => $fail
			);
		}

		function clearImageCache()
		{
			$success = array();
			$fail = array();
			$p = Core::getStorage('cache/');
			$d = opendir($p);
			while ($c = readdir($d)) {
				if ($c != '..' && $c != '.') {
					if (unlink($p.$c)) {
						$success[] = $p.$c;
					} else {
						$fail[] = $p.$c;
					}
				}
			}
			return (object) array(
				'success' => $success,
				'fail' => $fail
			);
		}

		function clear()
		{
			if (@$_GET['exclude'] != 'pages') {
				$clear = $this->clearPageCache();
				ob_start();
?>
				<h1><?=§('Clear page cache')?></h1>
				<p><?=§('The cache was cleared.')?></p>
				<h2 style="margin-bottom: 10px;"><?=§('Files that could not be deleted')?> (<?=count($clear->fail)?>)</h2>
				<div style="padding: 5px; min-height: 20px; max-height: 500px; overflow: auto; border: 1px solid #CCC;">
				<?=implode('<br />', $clear->fail);?>
				</div>
				<br />
				<h2 style="margin-bottom: 10px;"><?=§('Successfully deleted files')?> (<?=count($clear->success)?>)</h2>
				<div style="padding: 5px; min-height: 20px; max-height: 500px; overflow: auto; border: 1px solid #CCC;">
				<?=implode('<br />', $clear->success);?>
				</div>
<?php
			}
			if (@$_GET['exclude'] != 'images') {
				$clear = $this->clearImageCache();
?>
				<br />
				<h1><?=§('Clear image cache')?></h1>
				<p><?=§('The cache was cleared.')?></p>
				<h2 style="margin-bottom: 10px;"><?=§('Images that could not be deleted')?> (<?=count($clear->fail)?>)</h2>
				<div style="padding: 5px; min-height: 20px; max-height: 500px; overflow: auto; border: 1px solid #CCC;">
				<?=implode('<br />', $clear->fail);?>
				</div>
				<br />
				<h2 style="margin-bottom: 10px;"><?=§('Successfully deleted images')?> (<?=count($clear->success)?>)</h2>
				<div style="padding: 5px; min-height: 20px; max-height: 500px; overflow: auto; border: 1px solid #CCC;">
				<?=implode('<br />', $clear->success);?>
				</div>
<?php
			}
			return ob_get_clean();
		}

		function getPageCacheCount()
		{
			$i = 0;
			$p = Core::getStorage('page-cache/');
			$d = opendir($p);
			while ($c = readdir($d)) {
				if ($c != '..' && $c != '.') {
					$i++;
				}
			}
			return $i;
		}

		function getImageCacheCount()
		{
			$i = 0;
			$p = Core::getStorage('cache/');
			$d = opendir($p);
			while ($c = readdir($d)) {
				if ($c != '..' && $c != '.') {
					$i++;
				}
			}
			return $i;
		}
	}
?>
