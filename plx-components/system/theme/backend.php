<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
<?=$site->getDefaultHead()?>
	</head>
	<body id="<?=$backendID?>" class="backend">
		<div class="container plexusGUI">
			<div class="sidebar">
				<div class="w3cSucksWrap">
					<ul class="menu">
<? foreach ($menu as $key => $m) : ?>
						<li id="sidebar-menu-<?=$m[0]?>"<?= $this->addr->isActive($m[2], isset($m[3]) ? true : false) ? ' class="active"' : '' ?>><a href="<?=$m[2]?>"><?=$m[1]?> <span class="indicator"><?= empty($m[4]) ? '' : '('.$m[4].')' ?></span></a></li>
<? endforeach; ?>
					</ul>
				</div>
			</div>
			<div class="main">
<?=$main?>
			</div>
		</div>
<?=$site->getFooter()?>
	</body>
</html>
