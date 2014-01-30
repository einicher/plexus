<? if (!empty($message)) : ?>
	<div id="error" style="border-bottom: 1px solid #CCC;">
		<div class="errors"><?=$message?></div>
	</div>
<? endif; ?>
<? /*
	<br />
	<h2><?=§('Find components')?></h2>
	<form id="componentSearch" method="post" action="">
		<input type="text" name="searchComponent" value="<?=@$searchComponent?>" />
		<button type="submit"><?=§('Find')?></button>
	</form>
*/ ?>
<? if (!empty($matches)) : ?>
<? /*
	<? if (empty($results)) : ?>

		<br />
<?=§('Your pattern matched no results.')?>
	<? else : ?>
		<br />
		<div class="resultsMeta">
		<? if ($matches == 1) : ?>
<?=§('Your pattern matched {{'.$matches.'}} result.')?><br />
		<? else : ?>
<?=§('Your pattern matched {{'.$matches.'}} results.')?>
		<? endif; ?>
		</div>
*/ ?>
		<? foreach ($results as $result) : ?>
			<div class="result component">
				<h2><a class="title external" href="<?=$result->href?>" target="_blank"><?=$result->name?></a></h2>
				<p class="description"><?=$this->tools->cutByWords(strip_tags($result->description))?></p>
				<div class="meta">
					<a class="noAjax" href="<?=$this->a->assigned('system.preferences.components.install')?>/<?=$result->homedir?>/<?=base64_encode($result->source)?>"><?=§('Install')?></a>
					<a class="external" href="<?=$result->href?>" target="_blank"><?=§('Homepage')?></a>
				</div>
			</div>
		<? endforeach; ?>
	<? endif; ?>
<? /*
<? endif; ?>
*/ ?>
