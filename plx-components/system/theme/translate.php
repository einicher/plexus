<ul class="chooseLanguage">
	<? foreach ($languages as $prefix => $language) : ?>
		<? if ($prefix == $current->language) : ?>
	<li><strong><?=$language?> (<?=$prefix?>)</strong> <a href="<?=$current->getLink()?>"><?=$current->getTitle()?></a></li>
		<? else : ?>
		<? if (isset($translations[$prefix])) : ?>
	<li><?=$language?> (<?=$prefix?>) <a href="<?=$translations[$prefix]->getLink()?>"><?=$translations[$prefix]->getTitle()?></a></li>
		<? else : ?>
	<li><?=$language?> (<?=$prefix?>) <a href="<?=$this->addr->current().'/'.$prefix?>"><?=§('translate')?></a></li>
		<? endif; ?>
		<? endif; ?>
	<? endforeach; ?>
</ul>
