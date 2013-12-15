<?=$post->content?>
<div class="info">
	<?=§('Published {{'.$this->tools->detectTime($post->published).'}}')?>
	<?=$post->tools->detectTags($post->tags)?>
<? if ($this->getOption('site.trackbacks')) : ?>
	<div class="trackbacks" rel="nofollow">
		<a href="<?=$post->getTrackbackUrl()?>"><?=§('Trackbacks')?> <? if ($post->getTrackbacksCount() > 0) : ?>(<?=$post->getTrackbacksCount()?>)<? endif; ?></a>
	</div>
<? endif; ?>
	<?= $this->o->notify('system.data.info') ?>
	<?= $this->o->notify('system.post.info') ?>
	<div class="clear"></div>
</div>
<div class="chronological">
	<? $next = $post->next(); if (!empty($next)) : ?>
	<a href="<?=$next->getLink()?>" class="next" title="<?=$next->getTitle()?>">« <?=§('Next Post')?></a>
	<? endif; ?>
	<? $prev = $post->previous(); if (!empty($prev)) : ?>
	<a href="<?=$prev->getLink()?>" class="prev" title="<?=$prev->getTitle()?>"><?=§('Previous Post')?> »</a>
	<? endif; ?>
	<div class="clear"></div>
</div>
