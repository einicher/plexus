<?php
	Observer::connect('site.getHeader', 'loadjQuery', $this, FALSE);

	function loadjQuery($actor, $siteHead)
	{
		$addr = Address::getInstance();
		return $siteHead.'
		<script type="text/javascript" src="'.$addr->getRoot().PLX_RESOURCES.'jquery/jquery.min.js"></script>';
	}
?>