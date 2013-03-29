<? foreach ($tags as $tag) : ?>
	<a href="<?=$tag->href?>" class="cloudTag <?=$tag->class?>">
		<?=$tag->name?>
<? if (!empty($showCounts)) : ?>
				<span class="count">(<?=$tag->count?>)</span>
<? endif; ?>
	</a>
<? endforeach; ?>