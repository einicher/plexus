<div id="plxDB">
	<div id="plxDbMain">
<?=$plxDbMain?>
	</div>
</div>
<script type="text/javascript">
	function plxListType(type)
	{
		jQuery('#plxDbMain')<?=$plxDbLoaderJS?>.load(root + '<?=$this->a->assigned('system.database')?>/' + type);
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
			alert('<?=§('No rows selected. Select the checkbox on the beginning of a row if you want to delete it.')?>');
		} else {
			jQuery('#plxDbMain')<?=$plxDbLoaderJS?>.load(root + '<?=$this->a->assigned('system.database.delete')?>/' + values + '?back=' + backlink + '<?= isset($_GET['ajax']) ? '&ajax='.$_GET['ajax'] : '' ?>');
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
						jQuery('#plxDbMain').load('<?=$this->a->path?>');
					}
				} else {
					jQuery('#plxDbMain').html(data);
					plxDbAjaxForm();
				}
			}
		}); 
	}
</script>
