<tpl name="date">
	<div id="date<?=ucfirst($field->name)?>" class="formField formFieldDate">
<? if (!empty($field->options->label)) : ?>
		<label for="<?=$field->name?>" class="formFieldDateLabel"><?=$field->options->label?></label>
<? endif; ?>
		<div class="fieldDateWrap">
			<input type="<?=$field->datetype?>" id="<?=$field->name?>" class="fieldString" name="<?=$field->name?>" value="<?=date($field->format, $field->value)?>" size="30"/>
		</div>
<? if (!empty($caption)) : ?>
		<p class="caption"><?=$caption?></p>
<? endif; ?>
	</div>
<tpl name="datepicker">
	<script type="text/javascript" >
		jQuery('#<?=$field->name?>').datepicker({
			dateFormat: '<?=§('yy-mm-dd')?>'
		});
	</script>
</tpl>
</tpl>
