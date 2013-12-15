<?php
	Observer::connect('site.getHeader', 'loadSWFUpload', $this, FALSE);

	function loadSWFUpload($siteHead, $actor)
	{
		$path = $actor->a->getRoot(PLX_RESOURCES.'swfupload/');
		define('PLX_SWFUPLOAD_PATH', $path);
		return $siteHead.swfUploadScripts();
	}

	function swfUploadScripts()
	{
		ob_start();
?>
		<script type="text/javascript" src="<?=PLX_SWFUPLOAD_PATH?>swfupload.js"></script>
		<script type="text/javascript" src="<?=PLX_SWFUPLOAD_PATH?>swfupload.queue.js"></script>
		<script type="text/javascript" src="<?=PLX_SWFUPLOAD_PATH?>fileprogress.js"></script>
		<script type="text/javascript" src="<?=PLX_SWFUPLOAD_PATH?>handlers.js"></script>
<?php
		return ob_get_clean();
	}
?>
