<?php
	Observer::connect('site.getHeader', 'loadjQuery', $this, FALSE);

	function loadjQuery($siteHeader, $actor)
	{
		return $siteHeader.'
<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="'.Address::instance()->getRoot().PLX_RESOURCES.'jquery/jquery.min.js"></script>
<!--<![endif]-->
<!--[if lt IE 9]>
		<script src="'.Address::instance()->getRoot().PLX_RESOURCES.'jquery/jquery-1.11.0.min.js"></script>
<![endif]-->';
	}
?>
