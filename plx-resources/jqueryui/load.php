<?php
	Core::resource('jquery');
	Observer::connect('site.getHeader', 'loadjQueryUI', $this, FALSE);

	function loadjQueryUI($actor, $siteHead)
	{
		$addr = Address::getInstance();
		return $siteHead.'
		<script type="text/javascript" src="'.$addr->getRoot().PLX_RESOURCES.'jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="'.$addr->getRoot().PLX_RESOURCES.'jqueryui/jquery.form.js"></script>
		<script type="text/javascript" src="'.$addr->getRoot().PLX_RESOURCES.'jqueryui/jquery.hotkeys.js"></script>';
	}
?>