<p><?=$this->tools->detectLinkInText($micro->post)?><p>
<div class="info">
	<?=§('Published {{'.$this->tools->detectTime($micro->published).'}}')?>
<? if ($this->getOption('site.trackbacks')) : ?>
	<div class="trackbacks">
		<a href="<?=$micro->getTrackbackUrl()?>" rel="nofollow"><?=§('Trackbacks')?> <? if ($micro->getTrackbacksCount() > 0) : ?>(<?=$micro->getTrackbacksCount()?>)<? endif; ?></a>
	</div>
<? endif; ?>
</div>
<div class="chronological">
	<? $next = $micro->next(); if (!empty($next)) : ?>
	<a href="<?=$next->getLink()?>" class="next" title="<?=$next->getTitle()?>">« <?=§('Next Post')?></a>
	<? endif; ?>
	<? $prev = $micro->previous(); if (!empty($prev)) : ?>
	<a href="<?=$prev->getLink()?>" class="prev" title="<?=$prev->getTitle()?>"><?=§('Previous Post')?> »</a>
	<? endif; ?>
	<div class="clear"></div>
</div>
