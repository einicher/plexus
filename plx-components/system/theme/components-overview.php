<? if (!empty($message)) : ?>
	<div id="message" style="border-bottom: 1px solid #CCC;">
		<div class="infos"><?=$message?></div>
	</div>
	<script type="text/javascript" >
		setTimeout(function() {
			jQuery("#message").fadeOut();
		}, 5000);
	</script>
<? endif; ?>
<? if (!empty($error)) : ?>
	<div id="error" style="border-bottom: 1px solid #CCC;">
		<div class="errors"><?=$message?></div>
	</div>
<? endif; ?>
<? if (!empty($plexusUpgrade)) : ?>
	<div class="upgrade" style="margin: 20px;"><?=§('Plexus {{<strong>'.$plexusUpgrade['newVersion'].'</strong>}} available. Backup your database, then {{<a href="'.$this->a->assigned('system.preferences.components.upgrade').'/plexus">'.§('click here to start the automatic upgrade process').'</a>}}.')?></div>
<? endif; ?>
<? if (!empty($overviewUpgrades)) : ?>

	<div class="infos" style="margin: 20px;">
<? if ($overviewUpgrades == 1) : ?>
		<?=§('There is 1 component upgrade available.')?>
<? else : ?>
		<?=§('There are {{'.$overviewUpgrades.'}} component upgrades available.')?>
<? endif; ?>
	</div>
	<div style="border-top: 1px solid #CCC;"></div>
<? endif; ?>
<? foreach ($components as $component) : ?>
<div class="component">
	<div class="controls">
<? if ($component->upgrade) : ?>
		<a href="<?=$this->a->assigned('system.preferences.components.upgrade').'/'.$component->file?>" class="upgrade"><?=§('Upgrade')?></a>
<? endif; ?>
<? if ($component->active) : ?>
		<a href="<?=$this->a->assigned('system.preferences.components.deactivate').'/'.$component->class?>" class="deactivate"><?=§('Deactivate')?></a>
<? else : ?>
		<a href="<?=$this->a->assigned('system.preferences.components.activate').'/'.$component->class.'/'.$component->file?>" class="activate"><?=§('Activate')?></a></tpl>
<? endif; ?>
		<a href="<?=$this->a->assigned('system.preferences.components.remove').'/'.$component->file?>" class="remove"><?=§('Remove')?></a>
	</div>
	<h2 class="name"><?=$component->name?></h2>
	<span class="version"><?=$component->version?></span>
<? if ($component->upgrade) : ?>
	<div class="upgradeAvailable"><?=§('Version {{<strong>'.$component->newVersion.'</strong>}} of {{<strong>'.$component->name.'</strong>}} available.')?></a></div></tpl>
<? endif; ?>
	<p><?=$component->description?></p>
<? if ($component->link != 'COMPONENT_LINK') : ?>
	<div class="link"><a href="<?=$component->link?>" class="external" target="_blank"><?=str_replace('http://', '', $component->link)?></a></div>
<? endif; ?>
<? /* ?>
	<div class="author">by <a href="<?=$component->authorLink?>"><?=$component->author?></a> &lt;<a href="mailto:<?=$component->authorMail?>"><?=$component->authorMail?></a>&gt;</div>
<? */ ?>
</div>
<? endforeach; ?>
