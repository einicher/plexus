	<div id="radio<?=ucfirst($field->name)?>" class="formField formFieldRadio">
<? if (!empty($field->options->label)) : ?>
		<label for="<?=$field->name?>" class="formFieldRadioLabel"><?=$field->options->label?></label>
<? endif; ?>
<? foreach ($field->options->options as $option) : ?>
		<input type="radio" id="<?=$field->name?><?=$option->count?>" name="<?=$field->name?>" value="<?=$option->value?>"<?= $field->value == $option->value ? ' checked="checked"' : ''?> /> <label for="<?=$field->name?><?=$option->count?>"><?=$option->label?></label><br />
<? endforeach; ?>
<? if (!empty($caption)) : ?>
		<p class="caption"><?=$caption?></p>
<? endif; ?>
	</div>
