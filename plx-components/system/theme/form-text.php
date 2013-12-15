	<div id="text<?=ucfirst($field->name)?>" class="formField formFieldText">
<? if (isset($field->options->limit)) : ?>
		<span id="text<?=ucfirst($field->name)?>LimitCounter" class="formFieldTextLimitCounter"><?=$field->options->limit?></span>
<? elseif (isset($field->options->counter)) : ?>
		<span id="text<?=ucfirst($field->name)?>Counter" class="formFieldTextCounter"><?=$field->options->counter?></span>
<? endif; ?>
<? if (!empty($field->options->label)) : ?>
		<label for="<?=$field->name?>" class="formFieldTextLabel"><?=$field->options->label?></label>
<? endif; ?>
		<div class="fieldTextareaWrap">
			<textarea id="<?=$field->name?>" name="<?=$field->name?>" rows="<?=$field->options->rows?>" cols="<?=$field->options->cols?>"<?= isset($field->options->limit) ? ' maxlength="'.$field->options->limit.'"' : '' ?>><?=$field->value?></textarea>
<? if (isset($field->options->limit)) : ?>
			<script type="text/javascript" >
				jQuery('#<?=$field->name?>').bind('blur focus focusin focusout resize click mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup', function() {
					if (this.value.length > <?=$field->options->limit?>) {
						this.value = this.value.substr(0, <?=$field->options->limit?>);
					} else {
						jQuery('#text<?=ucfirst($field->name)?>LimitCounter').text(<?=$field->options->limit?>-this.value.length);
					}
				});
			</script>
<? elseif (isset($field->options->counter)) : ?>
			<script type="text/javascript" >
				jQuery('#<?=$field->name?>').bind('blur focus focusin focusout resize click mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup', function() {
					jQuery('#text<?=ucfirst($field->name)?>Counter').text(this.value.length);
				});
			</script>
<? endif; ?>
		</div>
<? if (!empty($caption)) : ?>
		<p class="caption"><?=$caption?></p>
<? endif; ?>
	</div>
