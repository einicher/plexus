	<ul>
<? foreach ($items as $item) : ?>
		<li <?= !empty($item->classes) ? ' class="'.$item->classes.'"' : '' ?>><a href="<?=$item->href?>"<?= $item->external ? ' target="_blank"' : ''?>><?=$item->label?></a></li>
<? endforeach; ?>
	</ul>
	<div class="clear"><div></div></div>
