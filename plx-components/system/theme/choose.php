<ul class="plexusDataTypeChooseDialog">
<? foreach ($types as $type) : ?>
	<li><a href="<?=$type->address?>"><?=$type->label?></a></li>
<? endforeach; ?>
</ul>
