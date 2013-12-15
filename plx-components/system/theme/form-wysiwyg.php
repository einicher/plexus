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
<? if (!empty($field->options->label)) : ?>
		<label for="<?=$field->name?>" class="formFieldWysiwygLabel"><?=$field->options->label?></label>
<? endif; ?>
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
<? if (!empty($caption)) : ?>
		<p class="caption"><?=$caption?></p>
<? endif; ?>
	</div>
