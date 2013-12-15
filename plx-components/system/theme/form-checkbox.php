	<div id="checkbox<?=ucfirst($field->name)?>" class="formField formFieldCheckbox">
		<input type="checkbox" id="<?=$field->name?>" name="<?=$field->name?>" value="true"<?= !empty($field->value) ? ' checked="checked"' : ''?> /> <label for="<?=$field->name?>"><?=$field->options->label?></label>
<? if (!empty($caption)) : ?>
		<p class="caption"><?=$caption?></p>
<? endif; ?>
	</div>
