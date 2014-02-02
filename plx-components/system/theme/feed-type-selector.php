	<div class="resultTypeSelector">
		<div class="selected"><?=$current?></div>
		<ul>
<? foreach ($items as $item) : ?>
			<li><a href="<?=$item->href?>"><?=$item->label?></a></li>
<? endforeach; ?>
		</ul>
	</div>
	<div class="clear"></div>
