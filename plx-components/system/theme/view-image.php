<p>
	<a href="<?=$image->enlargedSrc?>" class="fancybox"><img class="imageFile" src="<?=$image->src?>" alt="" /></a>
</p>
<p class="imageDescription">
	<?=$image->description?>
</p>
<script type="text/javascript">
	jQuery('.fancybox').fancybox();
</script>
<div class="info">
	<?=§('Published {{'.$this->tools->detectTime($image->published).'}}')?>
	<?=$image->tools->detectTags($image->tags)?>
<? if ($this->getOption('site.trackbacks')) : ?>
	<div class="trackbacks">
		<a href="<?=$image->getTrackbackUrl()?>" rel="nofollow"><?=§('Trackbacks')?> <? if ($image->getTrackbacksCount() > 0) : ?>(<?=$image->getTrackbacksCount()?>)<? endif; ?></a>
	</div>
<? endif; ?>
</div>
<div class="chronological">
	<? $next = $image->next(); if (!empty($next)) : ?>
	<a href="<?=$next->getLink()?>" class="next" title="<?=$next->getTitle()?>">« <?=§('Next Post')?></a>
	<? endif; ?>
	<? $prev = $image->previous(); if (!empty($prev)) : ?>
	<a href="<?=$prev->getLink()?>" class="prev" title="<?=$prev->getTitle()?>"><?=§('Previous Post')?> »</a>
	<? endif; ?>
	<div class="clear"></div>
</div>
