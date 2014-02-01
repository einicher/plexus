<div id="componentsContainer">
	<h1><?=§('Components')?></h1>
	<span class="coreVersion"><?=§('Plexus Core Version')?>: <?=$this->system->version?></span>
	<div class="clear"></div>

	<div class="tabs">
		<ul>
			<li><a href="<?=$this->a->assigned('system.preferences.components')?>"<?= $this->a->isActive($this->a->assigned('system.preferences.components', 2)) || !empty($message) ? ' class="active noAjax"' : ' class="noAjax"' ?>><?=§('Overview')?></a></li>
			<li><a href="<?=$this->a->assigned('system.preferences.components.install')?>"<?= $this->a->isActive($this->a->assigned('system.preferences.components.install', 2), false) ? ' class="active noAjax"' : ' class="noAjax"' ?>><?=§('Install Components')?></a></li>
		</ul>
		<div class="clear"></div>
	</div>

	<div class="adminContent">
<?=$plxContent?>
	</div>

	<script type="text/javascript" >
		jQuery('#componentsContainer a').not('.external').not('.noAjax').click(function() {
			jQuery(this).attr('href', jQuery(this).attr('href') + '?ajax=' + plxRoot);
			if (jQuery(this).hasClass('remove')) {
				if (!confirm('<?=§('Are you sure that you want to remove this component? All files will be deleted automatically.')?>')) {
					return false;
				}
			}
			jQuery('div.main').html('<div style="padding: 20px; font-size: xx-large;">Loading ...</div>').load(jQuery(this).attr('href'));
			return false;
		});
	</script>
</div>
