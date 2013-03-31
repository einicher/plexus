<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta charset="utf-8" />
		<title><?=$setup->title?> • <?=$this->system->name?> <?=$this->system->version?> Setup</title>
		<link type="text/css" rel="stylesheet" href="<?=PLX_SYSTEM.'theme/system.css'?>" />
		<link type="text/css" rel="stylesheet" href="<?=PLX_SYSTEM.'theme/style.css'?>" />
	</head>
	<body>
		<div id="container">
			<div id="containerTop"></div>
			<header class="main" style="border-bottom: 10px solid #B1BF41;">
				<h1><?=$this->system->name?> <?=$this->system->version?> Setup</h1>
				<div class="clear"></div>
			</header>
			<div id="wrap">
				<div id="main">
					<article class="main">
						<h1><?=$setup->title?></h1>
<? if (!empty(Core::$errors)) : ?>
						<div class="errors">
							<?=Core::$errors?>
						</div>
<? endif; ?>
						<?=$setup->content?>
					</article>
				</div>
				<div id="sidebar">
					<aside>
						<h1><?=§('Welcome!')?></h1>
						<p><?=§('This is the {{'.$this->system->version.'}} release of Plexus, a content management system started with the goal to provide an easy customizable, flexible and extendable way to create and connect websites and share their contents on the internet of the future with an administration made as simple as possible.')?></p>
						<p><?=§('Plexus is made with best intentions but without any warranty.')?></p>
						<p><?=§('If you have problems during installation and need help, just create an issue on {{<a href="http://plexus.at/Install" target="_blank">plexus.at/Install</a>}}')?></p>
						<p><?=§('Plexus is released under the {{<a href="http://en.wikipedia.org/wiki/MIT_License" target="_blank">'.§('MIT license').'</a>}}.')?></p>
						<p><?=§('If you want to join the project visit {{<a href="http://plexus.at/Join">plexus.at/Join</a>}} to get further information.')?></p>
					</aside>
				</div>
				<div class="clear"></div>
			</div>
			<footer class="main" style="padding: 20px">
				<div class="left" style="line-height: 24px; color: #FFF;">© 2009 - <?=date('Y')?> <a href="http://setanodus.net" target="_blank">setanodus.net</a></div>
				<div class="right"><a href="<?=$this->system->home?>" target="blank"><img style="float: left;" src="<?=$this->addr->getRoot('plx-resources/plexus-button-86x24.png')?>" alt="<?=$this->system->home?>" /></a></div>
				<div class="clear"></div>
			</footer>
			<div id="containerBottom"></div>
		</div>
	</body>
</html>