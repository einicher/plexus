<form class="plexusSearchFrom" method="get" action="<?=$search->action?>" onsubmit="jQuery(this).attr('action', jQuery(this).attr('action') + '/' + this.pattern.value.replace(/ /g, '+'));">
	<input type="text" name="pattern" value="<?=@$pattern?>"<? if (isset($search->inputWidth)) : ?> style="width: <?=$search->inputWidth?>px;"<? endif; ?> />
	<button type="submit" class="plxGuiButton"<? if (isset($search->buttonWidth)) : ?> style="width: <?=$search->buttonWidth?>px; padding: 2px 15px;"<? endif; ?>><?=§('Find')?></button>
	<div class="clear"></div>
</form>
<? if (isset($hits)) : ?>
	<br />
	<p><?=§('Your search for “{{<strong>'.$pattern.'</strong>}}” matched {{'.$hits.'}} results.')?></p>
<?=$results?>
<? endif; ?>
