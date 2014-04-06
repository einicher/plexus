<div id="plexusDock<?=$dock->name?>" class="plxDock">
<? if (!empty($addWidget) && $addWidgetPosition == 1) : ?>
<?=$addWidget?>
<? endif; ?>
<?
	$count = count($widgets);
	foreach ($widgets as $key => $widget) :
		$class = '';
		if ($key == 0) $class = ' first'; 
		if ($key == $count-1) $class = ' last'; 
?>
	<aside id="widget-<?=$widget->id?>" class="widget<?=$class?> <?=get_class($widget)?><?= $widget->getTitle() == '' ? ' noTitle' : '' ?>">
<? if (!empty($widget->editWidget)) : ?>
	<span id="editWidget<?=$widget->id?>" class="edit plexusControls"<?=$forceEnabledEdit ? ' style="display: block;"' :''?>><?=§('Edit')?></span>
	<script type="text/javascript">
		jQuery('#editWidget<?=$widget->id?>').fancybox({
			href: '<?=$widget->href?>',
			width: 600,
			autoDimensions: false,
			centerOnScroll: true,
			overlayOpacity: 0.5,
			overlayColor: '#000',
			transitionIn: 'elastic',
			transitionOut: 'elastic',
			onComplete: function() {
				plxWidgetHtml2AjaxForm('<?=$widget->href?>');
				jQuery('form.plexusForm button.remove').click(function() {
					jQuery('form.plexusForm').attr('action', '<?=$widget->href?><?= strpos($widget->href, '?') == FALSE ? '?' : '&' ?>plexusRemove');
				});
			}
		});
	</script>
<? endif; ?>
<? if ($widget->getTitle()) : ?>
		<h1 class="widget"><?=$widget->getTitle()?></h1>
<? endif; ?>
		<div class="wrap">
<?=$widget->view?>
			<div class="clear"></div>
		</div>
	</aside>
<? endforeach; ?>
<? if (!empty($addWidget) && $addWidgetPosition == 0) : ?>
<?=$addWidget?>
<? endif; ?>
		<div class="clear"></div>
</div>
