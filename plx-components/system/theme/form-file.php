	<div id="file<?=ucfirst($field->name)?>" class="formField formFieldFile">
<? if (!empty($field->options->label)) : ?>
		<label for="<?=$field->name?>" class="formFieldFileLabel"><?=$field->options->label?></label>
<? endif; ?>
<? if ($field->isImage) : ?>
<a href="<?=$field->image->enlargedSrc?>" class="fancybox"><img class="imageFile" src="<?=$field->image->src?>" alt="" /></a>
<script type="text/javascript">
	jQuery('.fancybox').fancybox();
</script>
<? endif; ?>
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
<? if (!empty($caption)) : ?>
		<p class="caption"><?=$caption?></p>
<? endif; ?>
	</div>
