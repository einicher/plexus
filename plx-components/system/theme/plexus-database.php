<div id="plxDB">
	<div id="plxDbMain">
	<tpl name="index">
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
					<a id="plxDbPlexusPreferences" href="<?=$this->addr->assigned('system.preferences')?>"><?=§('Change Settings')?></a>
				</div>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
			<div id="plxDbDataOverview" class="box">
				<h1><?=§('Existing Data')?></h1>
				<table>
					<tpl name="boxType">
					<tr>
						<td class="click" onclick="plxListType('<?=$type->type?><?= isset($_GET['ajax']) ? '?ajax='.$_GET['ajax'] : '' ?>')"><?=$type->name?></td>
						<td><?=$type->count?></td>
						<td class="click" onclick="plxDbEdit('<?=$type->type?>')"><?=§('Create new {{'.$type->name.'}}')?></td>
					</tr>
					</tpl>
				</table>
			</div>
			<div class="clear"></div>
			<div id="plxDbComponentOverview" class="box">
				<h1><?=§('Active Components')?></h1>
				<table>
					<tpl name="plxDbComponent">
					<tr>
						<td><?=$component->name?></td>
						<td><?=$this->tools->cutByWords($component->description)?></td>
					</tr>
					</tpl>
				</table>
				<div class="right">
					<a id="plxDbPlexusComponents" href="<?=$this->addr->assigned('system.preferences.components')?>"><?=§('Manage Components')?></a>
				</div>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</article>
	</tpl>
	<tpl name="plxDbBrowse">
		<div id="plxDbScroller">
		<div id="plxDbTopPanel">
			<button type="button" onclick="var c = confirm('<?=$this->lang->get('Are you sure you want to delete the selected rows?')?>'); if (c) { plxDbDelete('<?=urlencode($this->addr->path)?>'); } else { return false; }" style="margin: 5px;"><?=§('Delete selected')?></button>
			<button type="button" id="plexusDbExportSelected" style="margin: 5px 5px 5px 0;"><?=§('Export selected')?></button>
			<span class="click" onclick="plxDbEdit('<?=$browse->type?>')"><?=§('Create new {{'.§($browse->type).'}}')?></span>
		</div>
		<table class="browser" cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr>
				<th width="1"><input type="checkbox" onclick="jQuery('table.browser td input:checkbox').attr('checked', !toggleCheckboxes); toggleCheckboxes = !toggleCheckboxes;"></th>
			<tpl name="typeTH">	<th><?=$col?></th></tpl>
			</tr>
		<tpl name="item">
			<tr class="<?=$item->class?>">
				<td><input type="checkbox" class="plxDbCheckbox" name="multi" value="<?=$item->id?>"></td>
			<tpl name="typeTD">	<td onclick="plxDbEdit('<?=$item->id?>')"><?=$data?></td></tpl>
			</tr>
		</tpl>
		</table>
		</div>

		<div id="plxDbMainBottomPanel">
			<form method="post" onsubmit="jQuery('#plxDbMain').load(root + 'PlexusDatabase/<?=$browse->type?>/<?=$browse->current?>', { search: jQuery('#plxDbSearch').val() }); return false;">
				<input type="text" id="plxDbSearch" name="plxDbSearch" value="<?=$browse->search?>" />
				<button type="submit"><?=§('Search')?></button>
			</form>
			<div id="plxDbMainBottomPanelPages">
				<div id="display"><strong><?=$browse->from?></strong> <?=§('to')?> <strong><?=$browse->to?></strong> <?=§('out of')?> <strong><?=$browse->overall?></strong></div>
				<tpl name="plxDbPrev"><span class="click<?=$class?>" onclick="<?=$action?>">&lt;</span></tpl>
				<tpl name="pages">
					<div id="plxDbPages">
						<div id="plxDbPageChooser"><tpl name="plxDbPage"><span class="click" onclick="<?=$action?>"><?=$page?></span></tpl></div>
						<div id="plxDbPageCurrent" onclick="jQuery('#plxDbPageChooser').toggle();"><?=$browse->current?></div>
					</div>
				</tpl>
				<tpl name="plxDbNext"><span class="click<?=$class?>" onclick="<?=$action?>">&gt;</span></tpl>
			</div>
			<div class="clear"></div>
		</div>
		<script type="text/javascript">
			var toggleCheckboxes = false;
			function plxDbEdit(id)
			{
				jQuery('#plxDbMain')<?=$plxDbLoaderJS?>.load(root + '<?=$this->addr->assigned('system.database.edit')?>/' + id + '?back=<?=urlencode($this->addr->path)?>&ajax=' + root,
				function(data, status, ajax) {
					plxDbAjaxForm('<?=$this->addr->path?>');
				});
			}
			jQuery('#plexusDbExportSelected').click(function(e) {
				var values = [];
				jQuery('.plxDbCheckbox:checked').each(function() {
					values.push($(this).val());
				});
				if (values == '') {
					alert('<?=$this->lang->get('No rows selected. Select the checkbox on the beginning of a row if you want to export it.')?>');
				} else {
					window.location.href = '<?=$this->addr->assigned('system.export', '', 1)?>/'+values;
				}
			});
		</script>
	</tpl>
	</div>
</div>
<script type="text/javascript">
	function plxListType(type)
	{
		jQuery('#plxDbMain')<?=$plxDbLoaderJS?>.load(root + '<?=$this->addr->assigned('system.database')?>/' + type);
		toggleCheckboxes = false;
		jQuery('table.browser td input:checkbox').attr('checked', false);
	}

	function plxDbDelete(backlink)
	{
		var values = [];
		jQuery('.plxDbCheckbox:checked').each(function() {
			values.push($(this).val());
		});
		if (values == '') {
			alert('<?=$this->lang->get('No rows selected. Select the checkbox on the beginning of a row if you want to delete it.')?>');
		} else {
			jQuery('#plxDbMain')<?=$plxDbLoaderJS?>.load(root + '<?=$this->addr->assigned('system.database.delete')?>/' + values + '?back=' + backlink + '<?= isset($_GET['ajax']) ? '&ajax='.$_GET['ajax'] : '' ?>');
		}
	}

	function plxDbAjaxForm(back)
	{
		jQuery('#plxDbMain form.plexusForm').ajaxForm({
			success: function(data) { 
				if (data == 'DELETED' || data == '<head></head><body>DELETED</body>') {
					if (back) {
						jQuery('#plxDbMain').load(back);
					} else {
						jQuery('#plxDbMain').load('<?=$this->addr->path?>');
					}
				} else {
					jQuery('#plxDbMain').html(data);
					plxDbAjaxForm();
				}
			}
		}); 
	}
</script>
