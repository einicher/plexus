<? if ($site->hasInfoMessages()) : ?>
	<div class="infos">
		<?=$site->getInfoMessages()?>
	</div>
<? endif; ?>
<? if ($site->hasErrorMessages()) : ?>
	<div class="errors">
		<?=$site->getErrorMessages()?>
	</div>
<? endif; ?>

<article id="article-<?=$content->id?>" class="main <?=strtolower($content->getType())?>" style="position:relative">

<? if ($site->showEditPanel()) : ?>
	<div class="plxEditPanel plexusControls">
	<? if ($site->showEditLink()) : ?>
		<a href="<?=$content->getEditLink()?>" class="edit plexusControls"><?=§('Edit')?></a>
	<? if ($this->access->granted('system.copy')) : ?>
		<a href="<?=$content->getCopyLink()?>" class="copy plexusControls"><?=§('Copy')?></a>
	<? endif; ?>
	<? if (count(Control::$languages) > 1) : ?>
		<a href="<?=$content->getTranslateLink()?>" class="translate plexusControls"><?=§('Translate')?></a>
	<? endif; ?>
	<? endif; ?>
	<? if ($site->showCancelLink()) : ?>
		<a href="<?=$content->getCancelLink()?>" class="cancel plexusControls"><?=§('Cancel')?></a>
	<? endif; ?>
	</div>
<? endif; ?>

<? if ($content->showTitle() && $content->getTitle() != '') : ?>
	<h1 class="main"><?=$content->getTitle()?></h1>
<? endif; ?>

<?=$main?>

	<script type="text/javascript" >
		jQuery('a.lightThumb').fancybox();
	</script>
	<div class="clear"></div>
</article>
