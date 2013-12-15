<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
<?=$site->getDefaultHead()?>
		<meta name="viewport" content="width=1024" />
	</head>
	<body id="data-<?=$content->id?>" class="<?=$site->bodyClass()?>">
		<div id="container">
			<header class="main">
				<div class="container clearfix">
					<h1><a href="<?=$site->getHome()?>"><?=$site->getName()?></a></h1>
<?=$site->getWidget('SearchWidget', 'headerSearch', array('width' => 302))?>
				</div>
			</header>
			<div id="feature">
				<div class="container clearfix">
<?=$site->getWidget('SimpleBannerWidget', 'featureImage', array('width' => 960, 'height' => 250))?>
				</div>
			</div>
			<nav>
				<div class="container clearfix">
<?=$site->getWidget('MenuWidget', 'headerMenu')?>
				</div>
			</nav>
			<div id="wrap" class="container clearfix">
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
				<div class="container clearfix">
<?=$site->getDock('footer', array('addPositionFirst' => FALSE))?>
				</div>
			</footer>
		</div>
<?=$site->getFooter()?>
	</body>
</html>
