<tpl name="install">

<? if (!empty($message)) : ?>
	<div id="error" style="border-bottom: 1px solid #CCC;">
		<div class="errors"><?=$message?></div>
	</div>
<? endif; ?>

	<br />
	<h2><?=§('Find components')?></h2>
	<form id="componentSearch" method="post" action="">
		<input type="text" name="searchComponent" value="<?=@$searchComponent?>" />
		<button type="submit"><?=$this->lang->get('Find')?></button>
	</form>

<? if (empty($searchComponent)) : ?>
	<br />
	<h2><?=§('Browse Components')?></h2>
	<div class="component">
		<h3 class="name">name</h3>
		<span class="version">version</span>
		<a href="">Install</a> <a href="">Link</a>
	</div>
<? endif; ?>

	<tpl name="noResults">
		<br />
		<?=§('Your pattern matched no results.')?>
	</tpl>

	<tpl name="results">
		<br />
		<div class="resultsMeta">
<? if ($results->matches == 1) : ?>
		<?=§('Your pattern matched {{'.$results->matches.'}} result.')?><br />
<? else : ?>
		<?=§('Your pattern matched {{'.$results->matches.'}} results.')?>
<? endif; ?>
		</div>
		<tpl name="result">
			<div class="result component">
				<h2><a class="title external" href="<?=$result->href?>" target="_blank"><?=$result->name?></a></h2>
				<p class="description"><?=$this->tools->cutByWords(strip_tags($result->description))?></p>
				<div class="meta">
					<a class="noAjax" href="<?=$this->addr->assigned('system.preferences.components.install')?>/<?=$result->homedir?>/<?=base64_encode($result->source)?>"><?=§('Install')?></a>
					<a class="external" href="<?=$result->href?>" target="_blank"><?=§('Homepage')?></a>
				</div>
			</div>
		</tpl>
	</tpl>
</tpl>
