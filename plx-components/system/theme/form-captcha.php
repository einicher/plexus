	<div id="captcha<?=ucfirst($field->name)?>" class="formField formFieldCaptcha">
<? if (!empty($field->options->label)) : ?>
		<label for="<?=$field->name?>" class="formFieldCaptchaLabel"><?=$field->options->label?></label>
<? endif; ?>
		<div class="fieldCaptchaWrap">
			<label for="<?=$field->name?>"><?=§('Please type in the string “{{'.$field->captcha->string.'}}” in reverse order:')?></label>
			<input type="text" class="fieldCaptcha" id="<?=$field->name?>" name="<?=$field->name?>" value="<?=$field->value?>" size="5" />
			<div class="clear"></div>
		</div>
<? if (!empty($caption)) : ?>
		<p class="caption"><?=$caption?></p>
<? endif; ?>
	</div>
