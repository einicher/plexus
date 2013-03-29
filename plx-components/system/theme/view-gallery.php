<? if (!empty($gallery->description)) :  ?>
<p class="description"><?=$gallery->description?></p>
<? endif; ?>
<div class="thumbs">
<tpl name="thumbs">
<tpl name="img">
	<a href="<?=$img->enlarge?>" class="fancyGallery" rel="fancyGallery" title="<?=$img->title?>"><img src="<?=$this->addr->getRoot($this->imageScaleLink($img->src, $this->getOption('gallery.thumbSize'), $this->getOption('gallery.thumbSize')))?>" alt="<?=$img->title?>" /></a>
</tpl>
<div class="clear"></div>
<script type="text/javascript" >
	jQuery('.fancyGallery').fancybox({
		overlayColor: '#000',
		overlayOpacity: 0.75
	});
</script>
</tpl>
</div>
<div class="info">
	<?=§('Published {{'.$this->tools->detectTime($gallery->published).'}}')?>
	<?=$gallery->tools->detectTags($gallery->tags)?>
<? if ($this->getOption('site.trackbacks')) : ?>
	<div class="trackbacks">
		<a href="<?=$gallery->getTrackbackUrl()?>" rel="nofollow"><?=§('Trackbacks')?> <? if ($gallery->getTrackbacksCount() > 0) : ?>(<?=$gallery->getTrackbacksCount()?>)<? endif; ?></a>
	</div>
<? endif; ?>
</div>
<div class="chronological">
	<? $next = $gallery->next(); if (!empty($next)) : ?>
	<a href="<?=$next->getLink()?>" class="next" title="<?=$next->getTitle()?>">« <?=§('Next Post')?></a>
	<? endif; ?>
	<? $prev = $gallery->previous(); if (!empty($prev)) : ?>
	<a href="<?=$prev->getLink()?>" class="prev" title="<?=$prev->getTitle()?>"><?=§('Previous Post')?> »</a>
	<? endif; ?>
	<div class="clear"></div>
</div>
