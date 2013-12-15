	<div id="map<?=ucfirst($field->name)?>" class="formField formFieldMap">
<? if (!empty($field->options->label)) : ?>
		<label for="<?=$field->name?>" class="formFieldMapLabel"><?=$field->options->label?></label>
<? endif; ?>
		<div class="fieldMapWrap">
			<div id="formFieldMap<?=$field->name?>" style="width: 100%; height: 400px;"></div>
			<input type="hidden" id="<?=$field->name?>" name="<?=$field->name?>" value="<?=$field->value?>" />
		</div>
<? if (!empty($caption)) : ?>
		<p class="caption"><?=$caption?></p>
<? endif; ?>
	</div>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
	<script type="text/javascript">
<? if (empty($field->value)) : ?>
		<? if (empty($field->options->center)) : ?>
		var center = new google.maps.LatLng(48.20849491009753,16.37308895587921);
		<? else : ?>
		var center = new google.maps.LatLng(<?=$field->options->center?>);
		<? endif; ?>
<? else : ?>
		var center = new google.maps.LatLng(<?=$field->value?>);
<? endif; ?>
		var map = new google.maps.Map(document.getElementById('formFieldMap<?=$field->name?>'), {
			zoom: <?= empty($field->options->zoom) ? 8 : $field->options->zoom ?>,
			center: center,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});

		var marker = new google.maps.Marker({
			position: center,
			map: map,
			title: '<?=§('Drag and drop this marker to set coordinates of a location')?>',
			draggable: true
		});

		google.maps.event.addListener(marker, 'position_changed', function() {
			jQuery('#<?=$field->name?>').val(this.getPosition().lat() + ',' + this.getPosition().lng());
		});
		google.maps.event.addListener(map, 'click', function(event) {
			marker.setPosition(event.latLng);
		});

<? if (empty($field->options->center) && empty($field->value)) : ?>
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
				center = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
				map.setCenter(center);
				marker.setPosition(center);
			});
		}
<? endif; ?>
	</script>
