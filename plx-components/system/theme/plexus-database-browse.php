		<div id="plxDbScroller">
			<div id="plxDbTopPanel">
				<button type="button" onclick="var c = confirm('<?=§('Are you sure you want to delete the selected rows?')?>'); if (c) { plxDbDelete('<?=urlencode($this->a->path)?>'); } else { return false; }" style="margin: 5px;"><?=§('Delete selected')?></button>
				<button type="button" id="plexusDbExportSelected" style="margin: 5px 5px 5px 0;"><?=§('Export selected')?></button>
				<span class="click" onclick="plxDbEdit('<?=$browse->type?>')"><?=§('Create new {{'.§($browse->type).'}}')?></span>
			</div>
			<table class="browser" cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<th width="1"><input type="checkbox" onclick="jQuery('table.browser td input:checkbox').attr('checked', !toggleCheckboxes); toggleCheckboxes = !toggleCheckboxes;"></th>
<? foreach ($typeTH as $col) : ?>
					<th><?=$col?></th>
<? endforeach; ?>
				</tr>
<? foreach ($items as $item) : ?>
				<tr class="<?=$item->class?>">
					<td><input type="checkbox" class="plxDbCheckbox" name="multi" value="<?=$item->id?>"></td>
	<? foreach ($item->typeTD as $data) : ?>
					<td onclick="plxDbEdit('<?=$item->id?>')"><?=$data?></td>
	<? endforeach; ?>
				</tr>
<? endforeach; ?>
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
<? foreach ($plxDbPages as $page) : ?>
					<div id="plxDbPages">
						<div id="plxDbPageChooser"><tpl name="plxDbPage"><span class="click" onclick="<?=$action?>"><?=$page?></span></tpl></div>
						<div id="plxDbPageCurrent" onclick="jQuery('#plxDbPageChooser').toggle();"><?=$browse->current?></div>
					</div>
<? endforeach; ?>
				<tpl name="plxDbNext"><span class="click<?=$class?>" onclick="<?=$action?>">&gt;</span></tpl>
			</div>
			<div class="clear"></div>
		</div>
		<script type="text/javascript">
			var toggleCheckboxes = false;
			function plxDbEdit(id)
			{
				jQuery('#plxDbMain')<?=$plxDbLoaderJS?>.load(root + '<?=$this->a->assigned('system.database.edit')?>/' + id + '?back=<?=urlencode($this->a->path)?>&ajax=' + root,
				function(data, status, ajax) {
					plxDbAjaxForm('<?=$this->a->path?>');
				});
			}
			jQuery('#plexusDbExportSelected').click(function(e) {
				var values = [];
				jQuery('.plxDbCheckbox:checked').each(function() {
					values.push($(this).val());
				});
				if (values == '') {
					alert('<?=§('No rows selected. Select the checkbox on the beginning of a row if you want to export it.')?>');
				} else {
					window.location.href = '<?=$this->a->assigned('system.export', '', 1)?>/'+values;
				}
			});
		</script>
