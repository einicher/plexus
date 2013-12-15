	<div id="select<?=ucfirst($field->name)?>" class="formField formFieldSelect">
<? if (!empty($field->options->label)) : ?>
		<label for="<?=$field->name?>" class="formFieldSelectLabel"><?=$field->options->label?></label>
<? endif; ?>
		<select id="<?=$field->name?>" name="<?=$field->name?>">
			<tpl name="selectOption"><option value="<?=$option->value?>"<?= $option->value == $field->value ? ' selected="selected"' : '' ?>><?=$option->label?></option></tpl>
		</select>
<? if (!empty($caption)) : ?>
		<p class="caption"><?=$caption?></p>
<? endif; ?>
	</div>
