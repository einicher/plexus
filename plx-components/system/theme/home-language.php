<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title><?=$this->lang->get('Choose your language')?> - <?=$this->getOption('site.name')?></title>
		<style type="text/css">
			* { margin: 0; padding: 0; }
			html, body, #container { width: 100%; height: 100% }
			body { display: table; font-family: Verdana, sans-serif; }
			#container { display: table-row; }
			.main { display: table-cell; text-align: center; vertical-align: middle; }
			h1 { font-weight: normal; }
			ul { list-style-type: none; margin: 20px 0 0 0; }
		</style>
	</head>
	<body>
		<div id="container">
			<article class="main">
				<h1><?=$this->lang->get('Choose your language')?></h1>
				<ul>
<? foreach ($languages as $code => $language) : ?>
					<li><a href="<?=$this->addr->getHome($code)?>" title="<?=$language?>"><?=$language?></a></li>
<? endforeach; ?>
				</ul>
			</article>
		</div>
	</body>
</html>
