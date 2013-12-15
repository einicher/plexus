<?php
	Observer::connect('site.getHeader', 'loadTinyMCE', $this, FALSE);

	function loadTinyMCE($actor, $siteHead)
	{
		return $siteHead.'
		<script type="text/javascript" src="'.$actor->a->getRoot(PLX_RESOURCES.'tinymce/tiny_mce.js').'"></script>
		<script type="text/javascript" src="'.$actor->a->getRoot(PLX_RESOURCES.'tinymce/full.js?get=22').'"></script>
';
	}
?>
