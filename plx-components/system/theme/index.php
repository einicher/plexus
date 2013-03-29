<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
<?=$site->getDefaultHead()?>
		<meta name="viewport" content="width=1024" />
	</head>
	<body id="data-<?=$content->id?>" class="<?=$site->bodyClass()?>">
		<div id="container">
			<div id="containerTop"></div>
			<header class="main">
				<h1><a href="<?=$site->getHome()?>"><?=$site->getName()?></a></h1>
<?=$site->getWidget('SimpleBannerWidget', 'headerImage', array('width' => 900, 'height' => 200))?>
				<div class="clear"></div>
			</header>
			<nav>
<?=$site->getWidget('MenuWidget', 'headerMenu')?>
			</nav>
			<div class="clear"></div>
			<div id="wrap">
				<div id="main">
<?=$site->getContent()?>
				</div>
<? if ($content->disableSidebar == false) : ?>
				<div id="sidebar">
<?=$site->getDock('sidebar', array('width' => 300))?>
				</div>
<? endif; ?>
				<div class="clear"></div>
			</div>
			<footer class="main">
<?=$site->getDock('footer', array('addPositionFirst' => FALSE))?>
			</footer>
			<div id="containerBottom"></div>
		</div>
<?=$site->getFooter()?>
	</body>
</html>
