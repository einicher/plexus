<p>
	<?=$video->code?>
</p>
<?=$video->description?>
<div class="info">
	<?=§('Published {{'.$this->tools->detectTime($video->published).'}}')?>
	<?=$video->tools->detectTags($video->tags)?>
<? if ($this->getOption('site.trackbacks')) : ?>
	<div class="trackbacks">
		<a href="<?=$video->getTrackbackUrl()?>" rel="nofollow"><?=§('Trackbacks')?> <? if ($video->getTrackbacksCount() > 0) : ?>(<?=$video->getTrackbacksCount()?>)<? endif; ?></a>
	</div>
<? endif; ?>
</div>
<div class="chronological">
	<? $next = $video->next(); if (!empty($next)) : ?>
	<a href="<?=$next->getLink()?>" class="next" title="<?=$next->getTitle()?>">« <?=§('Next Post')?></a>
	<? endif; ?>
	<? $prev = $video->previous(); if (!empty($prev)) : ?>
	<a href="<?=$prev->getLink()?>" class="prev" title="<?=$prev->getTitle()?>"><?=§('Previous Post')?> »</a>
	<? endif; ?>
	<div class="clear"></div>
</div>
