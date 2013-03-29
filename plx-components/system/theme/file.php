<? if (!empty($file->description)) : ?>
	<p class="description"><?=nl2br($file->description)?></p>
<? endif; ?>
	<div class="download">
		<h2><?=§('Download')?></h2>
		<p>
			<span class="icon"></span>
			<a href="<?=$this->addr->getHome($file->src)?>"><?=$file->file?></a>
		</p>
	</div>
<? if (strtolower(substr($file->src, -4)) == '.pdf') : ?>
	<a href="<?=$this->imageScaleLink($file->src, $this->getOption('content.fullsize'))?>" class="fancybox"><img src="<?=$this->imageScaleLink($file->src, $this->getOption('content.width'))?>" alt="" /></a>
<? endif; ?>
