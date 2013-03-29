<div id="plexusPanel">
	<ul class="menuLeft">
		<? foreach ($menu['left'] as $key => $item) : ?>
		<li id="panelMenuItem-<?=$item->name?>">
			<? if (!empty($item->indicator)) : ?>
			<span style="position: relative; padding: 0 10px 0 0; float: right;"><span class="plexusPanelIndicator"><span><?=$item->indicator?></span></span></span>
			<? endif; ?>
			<a href="<?=$item->link?>"<? if (isset($menu[$item->name])) { echo ' onclick="var event = arguments[0] || window.event; panelToggle(event, jQuery(this), jQuery(this).siblings(\'ul\')); return false;"';} ?><?= isset($item->popup) ? ' class="plexusPreferences"' : '' ?>><?=$item->label?></a>
			
			<? if (isset($menu[$item->name])) : ?>
			<ul>
				<? foreach ($menu[$item->name] as $subkey => $subitem) : ?>
				<li id="panelMenuItem-<?=$subitem->name?>">
					<? if (!empty($subitem->indicator)) : ?>
					<span style="position: relative; padding: 0 10px 0 0; float: right;"><span class="plexusPanelIndicator"><span><?=$submenuItem->indicator?></span></span></span>
					<? endif; ?>
					<a href="<?=$subitem->link?>"<?= isset($subitem->popup) ? ' class="plexusPreferences"' : '' ?>><?=$subitem->label?></a>
				</li>
				<? endforeach; ?>
			</ul>
			<? endif; ?>
		</li>
		<? endforeach; ?>
	</ul>
	<ul class="menuRight">
		<? foreach ($menu['right'] as $key => $item) : ?>
		<li id="panelMenuItem-<?=$item->name?>">
			<? if (!empty($item->indicator)) : ?>
			<span style="position: relative; padding: 0 10px 0 0; float: left;"><span class="plexusPanelIndicator"><span><?=$item->indicator?></span></span></span>
			<? endif; ?>
			<a href="<?=$item->link?>"<? if (isset($menu[$item->name])) { echo ' onclick="var event = arguments[0] || window.event; panelToggle(event, jQuery(this), jQuery(this).siblings(\'ul\')); return false;"';} ?><?= isset($item->popup) ? ' class="plexusPreferences"' : '' ?>><?=$item->label?></a>
			
			<? if (isset($menu[$item->name])) : ?>
			<ul>
				<? foreach ($menu[$item->name] as $subkey => $subitem) : ?>
				<li id="panelMenuItem-<?=$subitem->name?>">
					<? if (!empty($subitem->indicator)) : ?>
					<span style="position: relative; padding: 0 10px 0 0; float: left;"><span class="plexusPanelIndicator"><span><?=$submenuItem->indicator?></span></span></span>
					<? endif; ?>
					<a href="<?=$subitem->link?>"<?= isset($subitem->popup) ? ' class="plexusPreferences"' : '' ?>><?=$subitem->label?></a>
				</li>
				<? endforeach; ?>
			</ul>
			<? endif; ?>
		</li>
		<? endforeach; ?>
<? if (!empty($content->id)) : ?>
		<li style="padding: 0;"><a id="plexusPanelId" href="<?=$this->addr->assigned('system.permalink', '', 1)?>/<?=$content->id?>" title="<?=§('ID of current content, Permalink')?>">#<span id="plexusPanelCurrentContentId"><?=$content->id?></span></a></li>
		<li style="padding: 0;"><img id="plexusPanelLamp" class="iconset <?=(empty($_COOKIE['systemButtonsOff']) ? 'lampOn' : 'lampOff')?>" src="<?=$site->getRoot('blank.gif')?>" width="16" height="16" /></li>
<? if (ContentControls::$editMode) : ?>
		<li style="padding: 0;"><a id="plexusPanelEdit" class="cancel" href="<?=$content->getCancelLink()?>" title="<?=§('Cancel')?>"><img class="iconset" src="<?=$site->getRoot('blank.gif')?>" width="16" height="16" alt="<?=§('Edit')?>" /></a></li>
<? else : ?>
		<li style="padding: 0;"><a id="plexusPanelEdit" class="edit" href="<?= ($content->type == 'ERROR404') ? $this->addr->assigned('system.create') : $content->getEditLink()?>" title="<?=§('Edit')?>"><img class="iconset" src="<?=$site->getRoot('blank.gif')?>" width="16" height="16" alt="<?=§('Edit')?>" /></a></li>
<? endif; ?>
<? endif; ?>
	</ul>

	<a id="plexus" href="<?=$site->getGeneratorHomepage()?>" target="_blank"><img src="<?=$site->getRoot('plx-resources/plexus-button-transparent.png')?>" alt="Plexus"></a>
</div>
<!--[if lt IE 9]>
	<script type="text/javascript">
		jQuery('#plexusPanel ul li ul').each(function() {
			var width = 0;
			jQuery(this).children('li').each(function() {
				if (jQuery(this).width() > width) {
					width = jQuery(this).css('width');
				}
				console.log(this);
			});
		});
	</script>
<![endif]-->
		<script type="text/javascript">
			jQuery(document).bind('keydown', 'p', function() { togglePanel(); });
			jQuery(document).bind('keydown', 'c', function() { togglePlexusControls(jQuery('#plexusPanelLamp')); });

			jQuery('html').click(function() {
				jQuery('#plexusPanel a').removeClass('active');
				jQuery('#plexusPanel ul li ul').css('display', 'none');
			});

			plexusPreferencesBoxOptions = {
				width: 800,
				height: 500,
				autoDimensions: false,
				centerOnScroll: true,
				overlayOpacity: 0.5,
				overlayColor: '#000',
				transitionIn: 'elastic',
				transitionOut: 'elastic',
				onComplete: function(link) {
					jQuery('#fancybox-inner').css({ position: 'absolute', top: 0, left: 0, bottom: 0, right: 0, width: 'auto', height: 'auto', overflow: 'none'})
					plxHtml2AjaxForm(link);
					plexusFancyboxAjaxForm(link);
				}
			};

			jQuery('.plexusPreferences').fancybox(plexusPreferencesBoxOptions);

			function plxHtml2AjaxForm(link)
			{
			    jQuery('form.plexusPreferencesForm').attr('action', link);
			    jQuery('form.plexusPreferencesForm').ajaxForm({
					success: function(data, statusText) {
						if (data == 'CLOSE') {
							jQuery.fancybox.close();
						} else {
							jQuery('#fancybox-inner').html(data);
							plxHtml2AjaxForm(link);
						}
					}
			    });
			}

			function panelToggle(e, a, ul)
			{
				if (e.stopPropagation) {
					e.stopPropagation();
				} else {
					window.event.cancelBubble = true;
				}
				var link = a.hasClass('active') ? false : true;
				var menu = ul.css('display') == 'block' ? false : true;
				jQuery('#plexusPanel ul li a').removeClass('active');
				jQuery('#plexusPanel ul li ul').css('display', 'none');
				if (link) {
					a.addClass('active');
				}
				if (menu) {
					ul.css('display', 'block');
				}
				return false;
			}

			function togglePlexusControls(lamp)
			{
				jQuery(lamp).toggleClass('lampOff').toggleClass('lampOn');
				if (jQuery(lamp).hasClass('lampOff')) {
					jQuery('.plexusControls').css('display', 'none');
					var exdate = new Date();
					exdate.setDate(exdate.getDate() + 365);
	                document.cookie = 'systemButtonsOff=TRUE;path=/;expires=' + exdate.toUTCString();
				} else {
					jQuery('.plexusControls').css('display', 'block');
                	document.cookie = 'systemButtonsOff=TRUE;path=/;expires=Thu, 01-Jan-1970 00:00:01 GMT';
				}		
			}
			
			function togglePanel()
			{
				if (jQuery('#plexusPanel').css('display') != 'block') {
					jQuery('#plexusPanel').css('display', 'block');
					jQuery('html').css('margin-top', '40px');
                	document.cookie = 'systempanelOff=TRUE;path=/;expires=Thu, 01-Jan-1970 00:00:01 GMT';
				} else {
					jQuery('#plexusPanel').css('display', 'none');
					jQuery('html').css('margin-top', '0');
					var exdate = new Date();
					exdate.setDate(exdate.getDate() + 365);
	                document.cookie = 'systempanelOff=TRUE;path=/;expires=' + exdate.toUTCString();
				}
			}

			function reloadPanel()
			{
				jQuery('#plexusPanelContainer').load(plxRoot + 'plxAjax/reloadPanel/' + jQuery('#plexusPanelCurrentContentId').html());			
			}

			jQuery('#plexusPanelLamp').click(function() {
				togglePlexusControls(this);			
			});
		</script>
<? if (!empty($_COOKIE['systemButtonsOff'])) : ?>
<style type="text/css">
	.plexusControls, #container .plexusControls { display: none; }
</style>
<? endif; ?>
<? if (!empty($_COOKIE['systempanelOff'])) : ?>
<style type="text/css">
	#plexusPanel { display: none; }
</style>
<? else : ?>
<style type="text/css">
	html { margin: 40px 0 0 0; }
</style>
<? endif; ?>
