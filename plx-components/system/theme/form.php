<form method="post" name="plexusForm" action="<?=$action?>"<?=$attributes?>>
<?=$form?>
<? if ($showChecks) : ?>
	<div class="formChecks">
<?=$checks?>
	</div>
<? endif; ?>
		<div class="clear"></div>
		<input type="hidden" name="plexusForm" value="TRUE" />
		<div class="fieldSaveWrap"><button type="submit" class="special save"><?=$saveButtonLabel?></button></div>
<? if ($showRemoveButton) : ?>
		<div class="fieldRemoveWrap"><button type="submit" class="special remove" name="plexusRemove" value="TRUE" onclick="var ccc = confirm('<?=§('Do you really want to remove this?')?>'); if (ccc) { return true; } else { return false; }"><?=§('Delete')?></button></div>
<? endif; ?>
		<div class="clear"></div>
<? if ($showFormAdvancedControls) : ?>
		<div class="plexusFormAdvancedToggle"><img class="off" src="<?=$this->a->getRoot('blank.gif')?>" alt="+" /><span><?=§('Advanced')?></span></div>
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
<? endif; ?>
</form>
