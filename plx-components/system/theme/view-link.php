<? if (!empty($link->comment)) : ?>
<div class="comment">
	<?=$link->comment?>
</div>
<? endif; ?>

<a href="<?=$link->link?>" class="link">
	<h2><?=$link->title?></h2>
<? if (!empty($link->thumbSrc)) : $hasThumb = 1; ?>
	<div class="thumb">
		<img src="<?=$link->thumbSrc?>" width="<?=$thumbWidth?>" alt="" />
	</div>
<? endif ?>
	<div class="description<?=empty($hasThumb) ? '' : ' hasThumb'?>">
		<?=$link->description?>
		<div class="external more"><?=§('Read more')?> »</div>
	</div>
	<div class="clear"></div>
</a>

<div class="source">
	<h2><?=§('Source')?></h2>
	<a href="<?=$link->link?>" title="<?=$link->link?>"><?=$this->tools->cutByChars(str_replace('http://', '', $link->link), 56, FALSE)?></a>
</div>
<div class="info">
	<?=§('Published {{'.$this->tools->detectTime($link->published).'}}')?>
	<?=$link->tools->detectTags($link->tags)?>
<? if ($this->getOption('site.trackbacks')) : ?>
	<div class="trackbacks">
		<a href="<?=$link->getTrackbackUrl()?>" rel="nofollow"><?=§('Trackbacks')?> <? if ($link->getTrackbacksCount() > 0) : ?>(<?=$link->getTrackbacksCount()?>)<? endif; ?></a>
	</div>
<? endif; ?>
</div>
<div class="chronological">
	<? $next = $link->next(); if (!empty($next)) : ?>
	<a href="<?=$next->getLink()?>" class="next" title="<?=$next->getTitle()?>">« <?=§('Next Post')?></a>
	<? endif; ?>
	<? $prev = $link->previous(); if (!empty($prev)) : ?>
	<a href="<?=$prev->getLink()?>" class="prev" title="<?=$prev->getTitle()?>"><?=§('Previous Post')?> »</a>
	<? endif; ?>
	<div class="clear"></div>
</div>
