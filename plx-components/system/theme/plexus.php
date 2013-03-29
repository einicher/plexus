<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
<?=$site->getDefaultHead()?>
	</head>
	<body id="plexus" class="backend">
		<div class="container plexusGUI">
			<div class="sidebar">
				<ul class="menu">
					<li<?= $this->addr->isActive('plx-plexus') ? ' class="active"' : '' ?>><a href="<?=$this->addr->getHome('plx-plexus')?>"><?=§('Dashboard')?> <span id="unreadIndicator"><?= empty($unread) ? '' : '('.$unread.')' ?></span></a></li>
					<li<?= $this->addr->isActive('plx-plexus/requests') ? ' class="active"' : '' ?>><a href="<?=$this->addr->getHome('plx-plexus/requests')?>"><?=§('Requests')?> <span id="requestsIndicator"><?= empty($requests) ? '' : '('.$requests.')' ?></span></a></li>
					<li<?= $this->addr->isActive('plx-plexus/connections') ? ' class="active"' : '' ?>><a href="<?=$this->addr->getHome('plx-plexus/connections')?>"><?=§('Connections')?></a></li>
<? if ($this->getOption('site.trackbacks')) : ?>
					<li<?= $this->addr->isActive('plx-plexus/trackbacks') ? ' class="active"' : '' ?>><a href="<?=$this->addr->getHome('plx-plexus/trackbacks')?>"><?=§('Trackbacks')?> <span id="trackbacksIndicator"><?= empty($trackbacks) ? '' : '('.$trackbacks.')' ?></span></a></li>
<? endif; ?>
					<li<?= $this->addr->isActive('plx-plexus/blocked-ips') ? ' class="active"' : '' ?>><a href="<?=$this->addr->getHome('plx-plexus/blocked-ips')?>"><?=§('Blocked IPs')?></a></li>
				</ul>
			</div>
			<div class="main">
<?=$main?>
			</div>
		</div>
<?=$site->getFooter()?>
	</body>
</html>
