<div class="plxPushes">
	<div class="panel">
		<h1><?=§('Plexus')?></h1>
		<div id="refresh" class="button plxGuiElement"><?=§('Refresh')?></div>
		<div id="markAllRead" class="button plxGuiElement"><?=§('Mark all read')?></div>
		<div class="toggleButtons plxGuiElement">
			<div id="showAll"<?= !empty($show) ? ' class="active"' : '' ?>><?=§('All')?></div>
			<div id="showUnread"<?= empty($show) ? ' class="active"' : '' ?>><?=§('Unread')?></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="pushes">
<?php
if (!$this->db->checkForTable($this->d->table('pushes', FALSE))) {
?>
	<p><?=§('Currently there are no items available.')?></p>
<?php
} else {
	$pushes = PlexusApi::instance()->getPushes();
	echo $pushes['content'];
}
?>
	</div>
</div>
<script type="text/javascript">
	jQuery('.plxPushes .panel #showAll').click(function() {
		jQuery.get('<?=$this->addr->getHome('plx-api/connect/push-view-mode?mode=1')?>');
		jQuery('.plxPushes .panel div').removeClass('active');
		jQuery(this).addClass('active');
		jQuery.getJSON('<?=$this->addr->getHome('plx-api/connect/push-get-pushes')?>', function(data) {
			jQuery('.plxPushes .pushes').html(data.content);
		});
	});
	jQuery('.plxPushes .panel #showUnread').click(function() {
		jQuery.get('<?=$this->addr->getHome('plx-api/connect/push-view-mode?mode=0')?>');
		jQuery('.plxPushes .panel div').removeClass('active');
		jQuery(this).addClass('active');
		jQuery.getJSON('<?=$this->addr->getHome('plx-api/connect/push-get-pushes')?>', function(data) {
			jQuery('.plxPushes .pushes').html(data.content);
		});
	});
	jQuery('.plxPushes .panel #refresh').click(function() {
		jQuery.getJSON('<?=$this->addr->getHome('plx-api/connect/push-get-pushes')?>', function(data) {
			jQuery('.plxPushes .pushes').html(data.content);
			plexusRefreshIndicators();
		});
	});
	jQuery('.plxPushes #markAllRead').click(function () {
		jQuery.get('<?=$this->addr->getHome('plx-api/connect/push-all-read')?>');
		jQuery('.pushes div.result').removeClass('unread');
		jQuery('.pushes div.result').addClass('read');
		plexusRefreshIndicators();
	});
	function plexusRefreshIndicators()
	{
		jQuery('#panelMenuItem-plexus .plexusPanelIndicator span').html('0');
		jQuery('#panelMenuItem-plexus .plexusPanelIndicator').css('display', 'none');
		jQuery.getJSON('<?=$this->addr->getHome('plx-api/connect/push-get-requests-count')?>', function(data) {
			if (data.count) {
				jQuery('#requestsIndicator').html('(' + data.count + ')');
				jQuery('#panelMenuItem-plexus .plexusPanelIndicator span').html(parseInt(jQuery('#panelMenuItem-plexus .plexusPanelIndicator span').html())+parseInt(data.count));
				jQuery('#panelMenuItem-plexus .plexusPanelIndicator').css('display', 'block');
			} else {
				jQuery('#requestsIndicator').html('');
			}
		});
		jQuery.getJSON('<?=$this->addr->getHome('plx-api/connect/push-get-unread-count')?>', function(data) {
			if (data.count) {
				jQuery('#sidebar-menu-dashboard a .indicator').html('(' + data.count + ')');
				jQuery('#panelMenuItem-plexus .plexusPanelIndicator span').html(parseInt(jQuery('#panelMenuItem-plexus .plexusPanelIndicator span').html())+parseInt(data.count));
				jQuery('#panelMenuItem-plexus .plexusPanelIndicator').css('display', 'block');
			} else {
				jQuery('#sidebar-menu-dashboard a .indicator').html('');
			}
		});
	}
</script>
