<?php
	Observer::connect('site.getHeader', 'loadjQuery', $this, FALSE);

	function loadjQuery($siteHeader, $actor)
	{
		return $siteHeader.'
		<script type="text/javascript" src="'.Address::instance()->getRoot().PLX_RESOURCES.'jquery/jquery.min.js"></script>';
	}
?>
