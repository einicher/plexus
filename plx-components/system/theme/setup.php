<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta charset="utf-8" />
		<title><?=$setup->title?> • <?=$this->system->name?> <?=$this->system->version?> Setup</title>
		<link type="text/css" rel="stylesheet" href="<?=PLX_SYSTEM.'theme/system.css'?>" />
		<style type="text/css">
			body { background: #F2F2F2; font-family:sans-serif; }
			#container { border: 1px solid #DDD; width: 980px; margin: 50px auto; border-radius: 5px; }
			header.main { background: #000; color: #FFF; border-radius: 5px 5px 0 0; padding: 20px 30px; }
			header.main h1 img { float: left; }
			#main { padding: 30px; background: #FFF; }
			footer.main { background: #000; color: #FFF; border-radius: 0 0 5px 5px; color: #999; }
			footer.main a { color: #999; }
			input { border: 1px solid #CCC; font-size: inherit; padding: 10px; }
			article.main h1 { margin: 0 0 20px 0; font-weight: normal; }
			button { margin: 15px 0 0 0; color: #000; background: #F2F2F2; border: 1px solid #CCC; }
		</style>
	</head>
	<body>
		<div id="container">
			<header class="main clearfix" style="border-bottom: 10px solid #B1BF41;">
				<div class="container clearfix">
					<h1><img src="<?=$this->a->getRoot('plx-components/system/theme/plexus.png')?>" alt="<?=$this->system->name?> <?=$this->system->version?> Setup" /></h1>
				</div>
			</header>
			<div id="wrap" class="container clearfix">
				<div id="main" class="clearfix">
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
			</div>
			<footer class="main" style="padding: 20px">
				<div class="container clearfix">
					<div class="left">© 2009 - 2013 <a href="http://plexus-cms.org" target="_blank">plexus-cms.org</a></div>
					<div class="right"></div>
				</div>
			</footer>
		</div>
	</body>
</html>
