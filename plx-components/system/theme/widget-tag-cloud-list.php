<div class="tagList">
	<ul>
<? foreach ($tags as $tag) : ?>
		<li>
			<a href="<?=$tag->href?>" class="listTag">
				<?=$tag->name?>
<? if (!empty($showCounts)) : ?>
				<span class="count">(<?=$tag->count?>)</span>
<? endif; ?>
			</a>
		</li>
<? endforeach; ?>
	</ul>
	<div class="clear"></div>
</div>