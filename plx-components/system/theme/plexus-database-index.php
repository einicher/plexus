		<article class="plxDbOverview">
			<h1><?=§('Overview')?></h1>
			<div id="plxDbSettingsOverview" class="box">
				<table>
					<tr>
						<td><?=§('Name')?></td>
						<td><?=$this->getOption('site.name')?></td>
					</tr>
					<tr>
						<td><?=§('Site Owner')?></td>
						<td><?=$this->getOption('site.owner')?></td>
					</tr>
					<tr>
						<td><?=§('Site Owner Homepage')?></td>
						<td><?=$this->getOption('site.ownerLink')?></td>
					</tr>
					<tr>
						<td><?=§('Email')?></td>
						<td><?=$this->getOption('site.mail')?></td>
					</tr>
					<tr>
						<td><?=§('Main Language')?></td>
						<td><?=$this->getOption('site.language')?></td>
					</tr>
					<tr>
						<td><?=§('Theme')?></td>
						<td><?=$this->getOption('site.theme')?></td>
					</tr>
				</table>
				<div class="right">
					<a id="plxDbPlexusPreferences" href="<?=$this->a->assigned('system.preferences')?>"><?=§('Change Settings')?></a>
				</div>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
			<div id="plxDbDataOverview" class="box">
				<h1><?=§('Existing Data')?></h1>
				<table>
<? foreach ($types as $type) : ?>
					<tr>
						<td class="click" onclick="plxListType('<?=$type->type?><?= isset($_GET['ajax']) ? '?ajax='.$_GET['ajax'] : '' ?>')"><?=$type->name?></td>
						<td><?=$type->count?></td>
						<td class="click" onclick="plxDbEdit('<?=$type->type?>')"><?=§('Create new {{'.$type->name.'}}')?></td>
					</tr>
<? endforeach; ?>
				</table>
			</div>
			<div class="clear"></div>
			<div id="plxDbComponentOverview" class="box">
				<h1><?=§('Active Components')?></h1>
				<table>
<? foreach ($components as $component) : ?>
					<tr>
						<td><?=$component->name?></td>
						<td><?=$this->tools->cutByWords($component->description)?></td>
					</tr>
<? endforeach; ?>
				</table>
				<div class="right">
					<a id="plxDbPlexusComponents" href="<?=$this->a->assigned('system.preferences.components')?>"><?=§('Manage Components')?></a>
				</div>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</article>
