	<div id="string<?=ucfirst($field->name)?>" class="formField formFieldString">
<? if (!empty($field->options->label)) : ?>
		<label for="<?=$field->name?>" class="formFieldStringLabel"><?=$field->options->label?></label>
<? endif; ?>
		<div class="fieldStringWrap">
			<input type="<?= isset($field->password) ? 'password' : 'text' ?>" class="fieldString" id="<?=$field->name?>" name="<?=$field->name?>" value="<?=$field->value?>" size="30"/>
		</div>
<? if (!empty($caption)) : ?>
		<p class="caption"><?=$caption?></p>
<? endif; ?>
	</div>
