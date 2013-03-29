		<meta charset="utf-8" />
		<title><?=$site->getTitle()?></title>
		<meta name="generator" content="<?=$site->getGenerator()?>" />
<? if ($content->getAuthor()) : ?>
		<meta name="author" content="<?=$content->getAuthor()?>" />
<? endif; ?>
<? if ($content->getDate()) : ?>
		<meta name="date" content="<?=$content->getDate()?>" />
<? endif; ?>
<? if ($content->getDescription()) : ?>
		<meta name="description" content="<?=$content->getDescription()?>" />
<? endif; ?>
<? if ($content->getKeywords()) : ?>
		<meta name="keywords" content="<?=$content->getKeywords()?>" />
<? endif; ?>
		<!--[if lt IE 9]>
		<script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<link type="text/css" rel="stylesheet" href="<?=$site->getRoot('style.css'.( isset($args['excludeStyles']) ? '?exclude='.implode(',', $args['excludeStyles']) : '' ))?>" />
<?=$site->getHeader()?>
		<link rel="alternate" type="application/atom+xml" title="Atom Feed" href="<?=$site->getHome('atom.xml')?>" />
<?=$content->getMeta()?>
<?=$this->getOption('site.code')?>

