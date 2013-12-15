	<div class="tags">
		<span class="title"><?=§('Tags')?>: </span>
<? foreach ($tags as $tag) : ?>
		<a href="<?=$tag->link?>"><?=$tag->name?></a><? if ($tag->separator) : ?>, <? endif; ?>
<? endforeach; ?>
	</div>
