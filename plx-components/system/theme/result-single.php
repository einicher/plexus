	<article class="result <?=strtolower($result->type)?><?= empty($result->hasThumb) ? '' : ' hasThumb' ?>">
		<? if (!empty($result->resultHeader)) : ?>
			<header class="result"><?=$header?></header>
		<? endif; ?>
		<? if (isset($result->pre)) : ?>
		<?=$result->pre?>
		<? endif; ?>
		<a class="body" href="<?=$result->link()?>">
			<h1><?= htmlspecialchars(empty($result->titleLength) ? $result->title : $this->tools->cutByChars($result->title, $result->titleLength)) ?></h1>
		<? if (!empty($result->hasThumb)) : ?>
			<img class="thumb" src="<?=htmlspecialchars($this->imageScaleLink($result->thumbSrc, $siteFeedThumb, $siteFeedThumb))?>" width="<?=$siteFeedThumb?>" alt="" />
		<? endif; ?>
		<? if (!empty($image)) : ?>
			<img class="image" src="<?=$image->src?>" width="<?=$image->width?>" alt="" />
		<? endif; ?>
		<? if (!empty($result->excerpt)) : ?>
			<p class="excerpt<?= isset($result->hasThumb) ? ' hasThumb' : '' ?>"<?= isset($result->hasThumb) ? ' style="margin-left: <?=($siteFeedThumb+10)?>px"' : '' ?>><?=$this->tools->cutByWords($result->excerpt, $result->excerptLength)?></p>
		<? endif; ?>
			<span class="clear cl0" style="display: block;"></span>
		</a>
		<div class="clear cl1"></div>
		<? if (!empty($result->footer)) : ?>
		<footer class="meta">
			<span class="type <?=strtolower($result->getType())?>"><?=§($result->getType())?></span>
			<a class="date" href="<?=$result->link()?>"><?=$this->tools->detectTime($result->published/*, empty($result->hasTime) ? TRUE : FALSE*/)?></a>
			<?=@$metaItem?>
		</footer>
		<div class="clear cl2"></div>
		<? endif; ?>
	</article>
