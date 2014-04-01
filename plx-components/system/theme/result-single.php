	<article class="result <?=strtolower($result->type)?><?= empty($result->hasThumb) ? '' : ' hasThumb' ?>">
		<? if (!empty($result->resultHeader)) : ?>
			<header class="result"><?=$header?></header>
		<? endif; ?>
		<? if (isset($result->pre)) : ?>
		<?=$result->pre?>
		<? endif; ?>
		<a class="body clearfix" href="<?=$result->link()?>">
		<? if (empty($result->headingBelowThumb)) : ?>
			<h1><?= htmlspecialchars(empty($result->titleLength) || $result->titleLength == -1  ? $result->title : $this->tools->cutByChars($result->title, $result->titleLength)) ?></h1>
		<? endif; ?>
		<? if (!empty($result->hasThumb)) : ?>
			<img class="thumb" src="<?=htmlspecialchars($this->imageScaleLink($result->thumbSrc, isset($result->thumbWidth) ? $result->thumbWidth : 100, isset($result->thumbHeight) ? $result->thumbHeight : ''))?>" width="<?=$result->thumbWidth?>" alt="" />
		<? endif; ?>
		<? if (!empty($result->headingBelowThumb)) : ?>
			<h1><?= htmlspecialchars(empty($result->titleLength) || $result->titleLength == -1 ? $result->title : $this->tools->cutByChars($result->title, $result->titleLength)) ?></h1>
		<? endif; ?>
		<? if (!empty($image)) : ?>
			<img class="image" src="<?=$image->src?>" width="<?=$image->width?>" alt="" />
		<? endif; ?>
		<? if (!empty($result->excerpt)) : ?>
			<p class="excerpt<?= isset($result->hasThumb) ? ' hasThumb' : '' ?>"><?=$result->excerptLength == -1 ? $result->excerpt : $this->tools->cutByWords($result->excerpt, $result->excerptLength)?></p>
		<? endif; ?>
		</a>
		<? if (!empty($result->footer)) : ?>
		<footer class="meta">
			<span class="type <?=strtolower($result->getType())?>"><?=§($result->getType())?></span>
			<a class="date" href="<?=$result->link()?>"><?=$this->tools->detectTime($result->published/*, empty($result->hasTime) ? TRUE : FALSE*/)?></a>
			<?=@$metaItem?>
		</footer>
		<div class="clear cl2"></div>
		<? endif; ?>
	</article>
