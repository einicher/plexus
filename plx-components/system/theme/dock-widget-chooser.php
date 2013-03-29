<div id="widgetChooser">
	<h1><?=§('Choose a widget')?></h1>
<? foreach ($widgets as $widget) : ?>
	<div class="widgetChooserItem">
		<a id="widget<?=$widget->class?>" href="<?=$widget->href?>"><span class="title"><?=$widget->name?></span><span class="desc"><?=$widget->description?></span></a>
		<script type="text/javascript">
			jQuery('#widget<?=$widget->class?>').fancybox({
				width: 600,
				autoDimensions: false,
				centerOnScroll: true,
				overlayOpacity: 0.5,
				overlayColor: '#000',
				transitionIn: 'elastic',
				transitionOut: 'elastic',
				onComplete: function(link) {
					plxWidgetHtml2AjaxForm(link, '<?=$widget->editor?>');
				}
			});
		</script>
	</div>
<? endforeach; ?>
</div>