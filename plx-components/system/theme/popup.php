<?php
	header('Content-type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
<?=$site->getDefaultHead();?>
		<meta name="viewport" content="width=device-width" />
	</head>
	<body id="data-<?=$content->id?>" style="background: transparent; padding: 30px;" class="<?=$site->bodyClass()?>">
<?=$site->getContent()?>
	</body>
</html>
