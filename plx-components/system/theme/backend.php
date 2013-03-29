<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
<?=$site->getDefaultHead()?>
	</head>
	<body id="<?=$backendID?>" class="backend">
		<div class="container plexusGUI">
			<div class="sidebar">
				<ul class="menu">
<? foreach ($menu as $key => $m) : ?>
					<li<?= $this->addr->isActive($m[2], isset($m[3]) ? true : false) ? ' class="active"' : '' ?>><a href="<?=$m[2]?>"><?=$m[1]?></a></li>
<? endforeach; ?>
				</ul>
			</div>
			<div class="main">
<?=$main?>
			</div>
		</div>
<?=$site->getFooter()?>
	</body>
</html>
