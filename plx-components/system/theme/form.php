<tpl name="form">
	<form method="post" name="plexusForm" action="<?=$action?>"<?=$attributes?>>
<?=$form?>
<tpl name="formChecks">
	<div class="formChecks">
<?=$checks?>
	</div>
</tpl>
		<div class="clear"></div>
		<input type="hidden" name="plexusForm" value="TRUE" />
		<div class="fieldSaveWrap"><button type="submit" class="special save"><?=$saveButtonLabel?></button></div>
<tpl name="remove">
		<div class="fieldRemoveWrap"><button type="submit" class="special remove" name="plexusRemove" value="TRUE" onclick="var ccc = confirm('<?=§('Do you really want to remove this?')?>'); if (ccc) { return true; } else { return false; }"><?=§('Delete')?></button></div>
</tpl>
		<div class="clear"></div>
<tpl name="formAdvanced">
		<div class="plexusFormAdvancedToggle"><img class="off" src="<?=$this->addr->getRoot('blank.gif')?>" alt="+" /><span><?=§('Advanced')?></span></div>
		<div class="plexusFormAdvanced" style="display: none;">
<?=$advanced?>		
		</div>
		<script type="text/javascript">
			jQuery('.plexusFormAdvancedToggle').click(function() {
				jQuery(this).next('.plexusFormAdvanced').toggle();
				if (jQuery(this).children('img').hasClass('off')) {
					jQuery(this).children('img').removeClass('off');
					jQuery(this).children('img').addClass('on');
				} else {
					jQuery(this).children('img').removeClass('on');
					jQuery(this).children('img').addClass('off');
				}
			});
		</script>
</tpl>
	</form>
</tpl>

<tpl name="captcha">
	<div id="captcha<?=ucfirst($field->name)?>" class="formField formFieldCaptcha">
		<tpl name="captchaLabel"><label for="<?=$field->name?>" class="formFieldCaptchaLabel"><?=$field->options->label?></label></tpl>
		<div class="fieldCaptchaWrap">
			<label for="<?=$field->name?>"><?=§('Please type in the string “{{'.$field->captcha->string.'}}” in reverse order:')?></label>
			<input type="text" class="fieldCaptcha" id="<?=$field->name?>" name="<?=$field->name?>" value="<?=$field->value?>" size="5" />
			<div class="clear"></div>
		</div>
<tpl name="caption">		<p class="caption"><?=$caption?></p></tpl>
	</div>
</tpl>

<tpl name="string">
	<div id="string<?=ucfirst($field->name)?>" class="formField formFieldString">
		<tpl name="stringLabel"><label for="<?=$field->name?>" class="formFieldStringLabel"><?=$field->options->label?></label></tpl>
		<div class="fieldStringWrap">
			<input type="<?= isset($field->password) ? 'password' : 'text' ?>" class="fieldString" id="<?=$field->name?>" name="<?=$field->name?>" value="<?=$field->value?>" size="30"/>
		</div>
<tpl name="caption">		<p class="caption"><?=$caption?></p></tpl>
	</div>
</tpl>

<tpl name="text">
	<div id="text<?=ucfirst($field->name)?>" class="formField formFieldText">
<? if (isset($field->options->limit)) : ?>
		<span id="text<?=ucfirst($field->name)?>LimitCounter" class="formFieldTextLimitCounter"><?=$field->options->limit?></span>
<? elseif (isset($field->options->counter)) : ?>
		<span id="text<?=ucfirst($field->name)?>Counter" class="formFieldTextCounter"><?=$field->options->counter?></span>
<? endif; ?>
		<tpl name="textLabel"><label for="<?=$field->name?>"><?=$field->options->label?></label></tpl>
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
<tpl name="caption">		<p class="caption"><?=$caption?></p></tpl>
	</div>
</tpl>

<tpl name="date">
	<div id="date<?=ucfirst($field->name)?>" class="formField formFieldDate">
		<tpl name="dateLabel"><label for="<?=$field->name?>"><?=$field->options->label?></label></tpl>
		<div class="fieldDateWrap">
			<input type="<?=$field->datetype?>" id="<?=$field->name?>" class="fieldString" name="<?=$field->name?>" value="<?=date($field->format, $field->value)?>" size="30"/>
		</div>
<tpl name="caption">		<p class="caption"><?=$caption?></p></tpl>
	</div>
<tpl name="datepicker">
	<script type="text/javascript" >
		jQuery('#<?=$field->name?>').datepicker({
			dateFormat: '<?=§('yy-mm-dd')?>'
		});
	</script>
</tpl>
</tpl>

<tpl name="file">
	<div id="file<?=ucfirst($field->name)?>" class="formField formFieldFile">
	<tpl name="fileLabel"><label><?=$field->options->label?></label></tpl>
<tpl name="imageFile">
<a href="<?=$image->enlargedSrc?>" class="fancybox"><img class="imageFile" src="<?=$image->src?>" alt="" /></a>
<script type="text/javascript">
	jQuery('.fancybox').fancybox();
</script>
</tpl>
	<div id="plxUiTabs<?=ucfirst($field->name)?>" class="plxUiTabs">
<? if (!empty($field->value)) : ?>
	<span class="formFieldFileDelete"><?= isset($field->isImage) ? §('Delete image') : §('Delete file') ?></span>
	<script type="text/javascript">
		jQuery('#file<?=ucfirst($field->name)?> .formFieldFileDelete').click(function() {
			var c = confirm('<?=§('Are you sure you want to delete this?')?>');
			if (c) {
				jQuery.ajax({
					url: plxRoot + 'plxAjax/plxFormDeleteFile',
					type: 'POST',
					data: {
						id: '<?=$field->id?>',
						property: '<?=$field->name?>',
						target: '<?=$field->options->target?>'
					},
					success: function(data) {
						if (data == 'OK') {
							jQuery('#file<?=ucfirst($field->name)?> .formFieldFileDelete').remove();
							jQuery('#file<?=ucfirst($field->name)?> a.fancybox').remove();
						} else {
							console.log(data);
						}
					}
				});
			} else {
				return false;
			}
		});
	</script>
<? endif; ?>
		<ul class="clearfix">
			<li><a href="#file<?=ucfirst($field->name)?>Computer"><span>Computer</span></a></li>
			<li><a href="#file<?=ucfirst($field->name)?>URL"><span>URL</span></a></li>
		</ul>
		<div id="file<?=ucfirst($field->name)?>Computer">
			<label for="<?=$field->name?>File">Datei hochladen</label>
			<input type="file" id="<?=$field->name?>File" name="<?=$field->name?>" />
		</div>
		<div id="file<?=ucfirst($field->name)?>URL">
			<label for="<?=$field->name?>URL">Bildadresse</label>
			<input type="text" id="<?=$field->name?>URL" name="<?=$field->name?>URL" />
		</div>
	</div>
	<script type="text/javascript">
	  // <![CDATA[
		jQuery('#plxUiTabs<?=ucfirst($field->name)?>').tabs();
	  // ]]>
	</script>
	<input type="hidden" name="<?=$field->name?>" value="<?=$field->value?>" />
<tpl name="caption"><p class="caption"><?=$field->caption?></p></tpl>
	</div>
</tpl>

<tpl name="wysiwyg">
<? if (isset($field->options->mode) && $field->options->mode == 'simple') : ?>
	<script type="text/javascript">
		tinyMCE.init(plexusLightTinyMCE);
	</script>
<? else : ?>
	<script type="text/javascript">
		tinyMCE.init(plexusFullTinyMCE);
	</script>
<? endif; ?>
	<div id="wysiwyg<?=ucfirst($field->name)?>" class="formField formFieldWysiwyg">
		<div class="wysiwygCodeSwitcher">
			<a class="showSource" href="javascript:void(0);" onclick="tinyMCE.execCommand('mceRemoveControl', false, '<?=$field->id?>');jQuery(this).toggle().next('a').toggle()"><?=§('Show Source')?></a>
			<a class="showEditor" href="javascript:void(0);" onclick="tinyMCE.execCommand('mceAddControl', false, '<?=$field->id?>');jQuery(this).toggle().prev('a').toggle()" style="display: none"><?=§('Show Editor')?></a>
		</div>
		<tpl name="wysiwygLabel"><label for="<?=$field->id?>"><?=$field->options->label?></label></tpl>
		<textarea id="<?=$field->id?>" name="<?=$field->name?>" rows="<?=$field->options->rows?>" class="plexusFormWysiwyg"><?=htmlspecialchars($field->value)?></textarea>
<? if (isset($_GET['ajax'])) : ?>
		<script type="text/javascript">
			tinyMCE.execCommand('mceRemoveControl', false, '<?=$field->id?>'); // on ajax calls our editor sometimes already exists
			setTimeout("tinyMCE.execCommand('mceAddControl', false, '<?=$field->id?>')", 1000); // need to wait until fancybox expanding is ready
		</script> 
<? else : ?>
		<script type="text/javascript">
			tinyMCE.execCommand('mceAddControl', false, '<?=$field->id?>');
		</script> 
<? endif; ?>
<tpl name="caption">
		<p class="caption"><?=$field->caption?></p>
</tpl>
	</div>
</tpl>

<tpl name="select">
	<div id="select<?=ucfirst($field->name)?>" class="formField formFieldSelect">
		<tpl name="selectLabel"><label for="<?=$field->name?>"><?=$field->options->label?></label></tpl>
		<select id="<?=$field->name?>" name="<?=$field->name?>">
			<tpl name="selectOption"><option value="<?=$option->value?>"<?= $option->value == $field->value ? ' selected="selected"' : '' ?>><?=$option->label?></option></tpl>
		</select>
<tpl name="caption">		<p class="caption"><?=$caption?></p></tpl>
	</div>
</tpl>

<tpl name="radio">
	<div id="radio<?=ucfirst($field->name)?>" class="formField formFieldRadio">
		<tpl name="radioLabel"><label for="<?=$field->name?>" class="formFieldRadioLabel"><?=$field->options->label?></label></tpl>
<tpl name="radioOption">		<input type="radio" id="<?=$field->name?><?=$option->count?>" name="<?=$field->name?>" value="<?=$option->value?>"<?= $field->value == $option->value ? ' checked="checked"' : ''?> /> <label for="<?=$field->name?><?=$option->count?>"><?=$option->label?></label><br /></tpl>
<tpl name="caption">		<p class="caption"><?=$field->caption?></p></tpl>
	</div>
</tpl>

<tpl name="checkbox">
	<div id="checkbox<?=ucfirst($field->name)?>" class="formField formFieldCheckbox">
		<input type="checkbox" id="<?=$field->name?>" name="<?=$field->name?>" value="TRUE"<?= !empty($field->value) ? ' checked="checked"' : ''?> /> <label for="<?=$field->name?>"><?=$field->options->label?></label>
<tpl name="caption">		<p class="caption"><?=$field->caption?></p></tpl>
	</div>
</tpl>

<tpl name="hidden">
	<input type="hidden" name="<?=$field->name?>" value="<?=$field->value?>" />
</tpl>
