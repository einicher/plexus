<?php
	Core::resource('jquery');
	Observer::connect('site.getHeader', 'loadFancybox', $this, FALSE);

	function loadFancybox($siteHeader, $actor)
	{
		return $siteHeader.'
		<script type="text/javascript" src="'.$actor->a->getRoot(PLX_RESOURCES.'fancybox/jquery.mousewheel-3.0.2.pack.js').'"></script>
		<script type="text/javascript" src="'.$actor->a->getRoot(PLX_RESOURCES.'fancybox/jquery.fancybox-1.3.1.pack.js').'"></script>
		<link type="text/css" rel="stylesheet" href="'.$actor->a->getRoot(PLX_RESOURCES.'fancybox/jquery.fancybox-1.3.1.css').'" media="screen" />';
	}
?>
