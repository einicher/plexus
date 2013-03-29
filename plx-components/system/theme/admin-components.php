<div id="componentsContainer">

	<h1><?=§('Components')?></h1>
	<span class="coreVersion"><?=§('Plexus Core Version')?>: <?=$this->system->version?></span>
	<div class="clear"></div>

	<div class="tabs">
		<ul>
			<li><a href="<?=$this->addr->assigned('system.preferences.components')?>"<?= $this->addr->isActive($this->addr->assigned('system.preferences.components', 2)) || !empty($message) ? ' class="active noAjax"' : ' class="noAjax"' ?>><?=$this->lang->get('Overview')?></a></li>
			<li><a href="<?=$this->addr->assigned('system.preferences.components.install')?>"<?= $this->addr->isActive($this->addr->assigned('system.preferences.components.install', 2), false) ? ' class="active noAjax"' : ' class="noAjax"' ?>><?=$this->lang->get('Install Components')?></a></li>
		</ul>
		<div class="clear"></div>
	</div>

<div class="adminContent">
<tpl name="overview">
<tpl name="message">
	<div id="message" style="border-bottom: 1px solid #CCC;">
		<div class="infos"><?=$message?></div>
	</div>
	<script type="text/javascript" >
		setTimeout(function() {
			jQuery("#message").fadeOut();
		}, 5000);
	</script>
</tpl>
<? if (!empty($error)) : ?>
	<div id="error" style="border-bottom: 1px solid #CCC;">
		<div class="errors"><?=$message?></div>
	</div>
<? endif; ?>
<tpl name="plexusUpgrade">
	<div class="upgrade" style="margin: 20px;"><?=§('Plexus {{<strong>'.$newVersion.'</strong>}} available. Backup your database, then {{<a href="'.$this->addr->getHome('PlexusComponents/PlexusInstall/Upgrade/plexus').'">'.§('click here to start the automatic upgrade process').'</a>}}.')?></div>
</tpl>
<tpl name="overviewUpgrades">
	<tpl name="overviewUpgradesSingular">
		<div class="infos" style="margin: 20px;"><?=§('There is 1 component upgrade available.')?></div>
	</tpl>
	<tpl name="overviewUpgradesPlural">
		<div class="infos" style="margin: 20px;"><?=§('There are {{'.$upgrades.'}} component upgrades available.')?></div>
	</tpl>
	<div style="border-top: 1px solid #CCC;"></div>
</tpl>
<tpl name="component">
<div class="component">
	<div class="controls">
		<tpl name="upgrade"><a href="<?=$this->addr->assigned('system.preferences.components.upgrade').'/'.$component?>" class="upgrade"><?=§('Upgrade')?></a></tpl>
		<tpl name="activate"><a href="<?=$this->addr->assigned('system.preferences.components.activate').'/'.$component->class.'/'.$component->file?>" class="activate"><?=§('Activate')?></a></tpl>
		<tpl name="deactivate"><a href="<?=$this->addr->assigned('system.preferences.components.deactivate').'/'.$component->class?>" class="deactivate"><?=§('Deactivate')?></a></tpl>
		<a href="<?=$this->addr->assigned('system.preferences.components.remove').'/'.$component->file?>" class="remove"><?=§('Remove')?></a>
	</div>
	<h2 class="name"><?=$component->name?></h2>
	<span class="version"><?=$component->version?></span>
	<tpl name="upgradeAvailable"><div class="upgradeAvailable"><?=§('Version {{<strong>'.$version.'</strong>}} of {{<strong>'.$component->name.'</strong>}} available.')?></a></div></tpl>
	<p><?=$component->description?></p>
	<div class="link"><a href="<?=$component->link?>" class="external" target="_blank"><?=str_replace('http://', '', $component->link)?></a></div>
<? /* ?>
	<div class="author">by <a href="<?=$component->authorLink?>"><?=$component->author?></a> &lt;<a href="mailto:<?=$component->authorMail?>"><?=$component->authorMail?></a>&gt;</div>
<? */ ?>
</div>
</tpl>
</tpl>
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
</div>
<script type="text/javascript" >
	jQuery('#componentsContainer a').not('.external').not('.noAjax').click(function() {
		if (jQuery(this).hasClass('remove')) {
			if (!confirm('<?=§('Are you sure that you want to remove this component? All files will be deleted automatically.')?>')) {
				return false;
			}
		}
		jQuery('div.main').html('<div style="padding: 20px; font-size: xx-large;">Loading ...</div>').load(jQuery(this).attr('href'));
		return false;
	});
</script>
</div>
