<tpl name="search">
<form class="plexusSearchFrom" method="get" action="<?=$search->action?>" onsubmit="jQuery(this).attr('action', jQuery(this).attr('action') + '/' + this.pattern.value.replace(/ /g, '+'));">
	<input type="text" name="pattern" value="<?=@$search->pattern?>"<? if (isset($search->inputWidth)) : ?> style="width: <?=$search->inputWidth?>px;"<? endif; ?> />
	<button type="submit" class="plxGuiButton"<? if (isset($search->buttonWidth)) : ?> style="width: <?=$search->buttonWidth?>px; padding: 2px 15px;"<? endif; ?>><?=§('Find')?></button>
	<div class="clear"></div>
</form>
</tpl>
<tpl name="results">
	<br />
	<p><?=§('Your search for “{{<strong>'.$search->pattern.'</strong>}}” matched {{'.$search->results.'}} results.')?></p>
</tpl>
<tpl name="typeSelector">
	<div class="resultTypeSelector">
		<div class="selected"><?=$current?></div>
		<ul>
<tpl name="typeSelectorItem">
			<li><a href="<?=$item->href?>"><?=$item->label?></a></li></tpl>
		</ul>
	</div>
	<div class="clear"></div>
</tpl>
<?=$search->results?>
<tpl name="result">
	<article class="result <?=strtolower($result->type)?><?= isset($result->hasThumb) ? ' hasThumb' : '' ?>">
		<tpl name="resultHeader">
			<header class="result"><?=$header?></header>
		</tpl>
		<tpl name="pre"><?=$pre?></tpl>
		<a class="body" href="<?=$result->link()?>">
			<h1><?=$result->title?></h1>
			<tpl name="thumb"><img class="thumb" src="<?=$this->imageScaleLink($thumb->src, $siteFeedThumb, $siteFeedThumb)?>" alt="" /></tpl>
			<tpl name="image"><img class="image" src="<?=$image->src?>" width="<?=$image->width?>" alt="" /></tpl>
			<tpl name="excerpt"><p class="excerpt<?= isset($result->hasThumb) ? ' hasThumb' : '' ?>"<?= isset($result->hasThumb) ? ' style="margin-left: <?=($siteFeedThumb+10)?>px"' : '' ?>><?=$result->excerpt?></p></tpl>
			<span class="clear" style="display: block;"></span>
		</a>
		<div class="clear"></div>
		<footer class="meta">
			<span class="type <?=strtolower($result->getType())?>"><?=§($result->getType())?></span>
			<a class="date" href="<?=$result->link()?>"><?=$this->tools->detectTime($result->published, empty($result->hasTime) ? TRUE : FALSE)?></a>
			<tpl name="metaItem"> • <?=$metaItem?></tpl>
		</footer>
	</article>
</tpl>

<tpl name="tags">
	<div class="tags">
		<span class="title"><?=§('Tags')?>: </span>
		<tpl name="tag"><a href="<?=$tag->link?>"><?=$tag->name?></a><tpl name="tagSeparator">, </tpl></tpl>
	</div>
</tpl>