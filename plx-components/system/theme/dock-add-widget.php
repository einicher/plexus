<aside class="widget addNew plexusControls"<?=$forceEnabledEdit ? ' style="display: block;"' : ''?>>
	<span id="addNewWidget<?=$dock->name?>" class="edit addNewWidget plexusControls"<?=$forceEnabledEdit ? ' style="display: block;"' : ''?>><?=§('+ Add New Widget')?></span>
	<script type="text/javascript" >
		jQuery('#addNewWidget<?=$dock->name?>').fancybox({
			href: '<?=$dock->addWidget?>',
			centerOnScroll: true,
			overlayOpacity: 0.5,
			overlayColor: '#000',
			transitionIn: 'elastic',
			transitionOut: 'elastic'
		});
	</script>
</aside>