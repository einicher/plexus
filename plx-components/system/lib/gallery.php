<?php
	class Gallery extends PlexusDataModel
	{
		public $type = 'GALLERY';
		
		function construct()
		{
			$this->resource('swfupload');

			$this->add('string', 'title', FALSE, array(
				'label' => $this->lang->get('Title'),
				'transformToAddress' => 1
			));
			$this->add('wysiwyg', 'description', FALSE, array(
				'label' => $this->lang->get('Description'),
				'rows' => 5
			));
			$this->add('custom', 'images', FALSE, array(
				'actor' => $this,
				'call' => 'multiUpload',
				'type' => 'string'
			));
			$this->add('string', 'tags', FALSE, array(
				'label' => $this->lang->get('Tags'),
				'caption' => $this->lang->get('Separate with commas')
			));
			$this->add('datetime', 'published', TRUE, array(
				'label' => $this->lang->get('Published'),
				'caption' => $this->lang->get('May be in the future.')
			));
			$this->add('status', 'status', TRUE, array(
				'label' => $this->lang->get('Status')
			));
		
		}

		function init()
		{
			if (empty($this->images)) {
				$this->images = array();
			} else {
				$this->images = explode(',', $this->images);
			}
			if (isset($_GET['lite2'])) {
				$this->status = 2;
			}
		}

		function listThumbs($limit = -1)
		{
			$i = 0;
			$l = 0;
			foreach ($this->images as $img) {
				$i++;
				$l++;
				$img = $this->getData('IMAGE', $img);
				$img->enlarge = $this->imageScaleLink($img->src, $this->getOption('content.fullsize'));
				$img->class = 'thumb'.$i;
				$this->tpl->repeat('view-gallery.php', 'img', array('img' => $img));
				if ($i == 5) {
					$i = 0;
				}
				if ($limit > 0 && $l == $limit) {
					break;
				}
			}
			return $this->tpl->cut('view-gallery.php', 'thumbs');
		}

		function getDescription()
		{
			return $this->tools->cutByWords(strip_tags($this->description));
		}

		function getContent()
		{
			$this->listThumbs();
			$c = $this->tpl->get('view-gallery.php', array('gallery' => $this));
        	$this->tpl->set('view-image.php');
			return $c;
		}

		function listResultThumbs($limit = -1)
		{
			$i = 0;
			$collect = '';
			foreach ($this->images as $thumb) {
				$thumb = $this->type('IMAGE', $thumb);
				$width = $this->getOption('content.width');
				$width -= 5*$limit;
				$width = ceil($width/$limit);
				if (!empty($thumb->id)) {
					$i++;
					$collect .= '<img class="sthumb thumb'.$i.'" src="'.$this->imageScaleLink($thumb->src, $width, $width).'" alt="" style="float: left; margin-right: 5px;">';
					if ($limit > 0 && $i==$limit) {
						break;
					}
				}
			}
			return $collect;
		}

		function result()
		{
			if (empty($this->description)) {
				if (!empty($this->images)) {
					$i = 0;
					$this->excerpt = $this->listResultThumbs(5);
				}
			} else {
				if (!empty($this->images)) {
					foreach ($this->images as $thumb) {
						$thumb = $this->type('IMAGE', $thumb);
						if (!empty($thumb->id)) {
							break;
						}
					}
					if (!empty($thumb->id)) {
						$this->hasThumb = 1;
						$this->thumbSrc = $thumb->src;
					}
				}				
				$this->excerpt = $this->tools->cutByWords(strip_tags($this->tools->detectSpecialSyntax($this->description)), $this->excerptLength);
			}
			$this->footer = 1;
			return Template::get2(array('result-single.php', 'system'), array('result' => $this));
		}

		function multiUploadThumb($imgID, &$actor, $prefix = '')
		{
			ob_start();		
			$img = new Image($imgID);
			if (!empty($img->file)) {
?>
		<li class="plxDel" style="margin: 0 5px 5px 0; float: left; width: 96px; height: 96px;"><img src="<?=$prefix.$actor->imageScaleLink($img->src, 96, 96)?>" alt="" style="float: left;" /><input type="hidden" name="images[]" value="<?=$img->id?>" /><span class="plxDelIcon" onclick="jQuery(this).parent().remove()">X</span></li>
<?php
			}
			return ob_get_clean();
		}

		function multiUpload($action, $attributes, $field, $fields)
		{
			if (!is_array($field->value)) {
				$field->value = explode(',', $field->value);
			}
			ob_start();
?>
<div id="customMultiUpload" class="formField">
	<legend for="images"><?=$this->lang->get('Gallery Images')?></legend>
	<div class="galleryImages">
		<ul id="galleryImagesSortable" style="width: 505px; list-style-type: none; margin: 0; padding: 0;">
<?php
	foreach ($field->value as $imgID) {
		if (empty($imgID)) {
			continue;
		}
		echo $this->multiUploadThumb($imgID, $this, '');
	}
?>
		</ul>
		<div class="clear"></div>
		<script type="text/javascript" >
			jQuery('#galleryImagesSortable').sortable();
			jQuery('#galleryImagesSortable').disableSelection();
		</script>
	</div>
	<br />

	<div id="galleryImagesAdd" class="plxUiTabs">
		<ul>
			<li><a href="#galleryImagesUpload"><?=$this->lang->get('Upload Images')?></a></li>
			<li><a href="#galleryImagesExisting"><?=$this->lang->get('Add Existing Images')?></a></li>
		</ul>
		<div id="galleryImagesUpload" class="multiUpload" style="padding: 10px;">
			<style class="text/css">
				object.swfupload { background: #E6E2CF; border: 1px solid #ccc; cursor: pointer; }
				.progressContainer { margin: 5px; padding: 4px; border: solid 1px #E8E8E8; background-color: #F7F7F7; overflow: hidden; }
				/* Message */
				.message { margin: 1em 0; padding: 10px 20px; border: solid 1px #FFDD99; background-color: #FFFFCC; overflow: hidden; }
				/* Error */
				.red { border: solid 1px #B50000; background-color: #FFEBEB; }
				/* Current */
				.green { border: solid 1px #DDF0DD; background-color: #EBFFEB; }
				/* Complete */
				.blue { border: solid 1px #CEE2F2; background-color: #F0F5FF; }
				.progressName { font-size: 8pt; font-weight: 700; color: #555; width: 323px; height: 14px; text-align: left; white-space: nowrap; overflow: hidden; }
				.progressBarInProgress,
				.progressBarComplete,
				.progressBarError { font-size: 0; width: 0%; height: 2px; background-color: blue; margin-top: 2px; }
				.progressBarComplete { width: 100%; background-color: green; visibility: hidden; }
				.progressBarError { width: 100%; background-color: red; visibility: hidden; }
				.progressBarStatus { margin-top: 2px; width: 337px; font-size: 7pt; font-family: Arial; text-align: left; white-space: nowrap; }
				a.progressCancel { font-size: 0; display: block; height: 14px; width: 14px; background-image: url(../images/cancelbutton.gif); background-repeat: no-repeat; background-position: -14px 0px; float: right; }
				a.progressCancel:hover { background-position: 0px 0px; }
			</style>
			<div class="fieldset flash" id="fsUploadProgress">
				<span class="legend"><?=$this->lang->get('Multi Upload Query')?></span>
			</div>
			<div id="divStatus">0 Files Uploaded</div>
			<div style="margin: 0.5em 0 0 0;">
				<span id="spanButtonPlaceHolder"></span>
				<button id="btnCancel" type="button" onclick="swfu.cancelQueue();" disabled="disabled" style="margin-left: 2px; font-size: 8pt; height: 29px; vertical-align: top;"><?=$this->lang->get('Cancel All Uploads')?></button>
			</div>
		</div>
<?php
	if (isset($_GET['ajax'])) {
		include_once PLX_RESOURCES.'swfupload/load.php';
		define('PLX_SWFUPLOAD_PATH', $this->addr->getRoot(PLX_RESOURCES.'swfupload/'));
		echo swfUploadScripts();
	}
?>
		<script type="text/javascript">
			var extendedUploadSuccessHandler = function(data)
			{
				data = eval('(' + data + ')');
				jQuery('#galleryImagesSortable').append(data.image);
			}
		
			function generateSWFupload() {
				var swfu = new SWFUpload({
					flash_url : '<?=PLX_SWFUPLOAD_PATH?>swfupload.swf',
					upload_url: '<?=$this->addr->getHome('plxAjax/multiUpload'.(empty($this->id) ? '' : '/'.$this->id).'?sid='.session_id())?>',
					post_params: {
						prefix: "<?=$this->addr->getRoot()?>"
					},
					file_size_limit : "100 MB",
					file_types : "*.*",
					file_types_description : "All Files",
					file_upload_limit : 100,
					file_queue_limit : 0,
					custom_settings : {
						progressTarget : "fsUploadProgress",
						cancelButtonId : "btnCancel"
					},
					debug: false,
			
					// Button settings
					button_image_url: '',
					<?=$this->lang->get('button_width: 140')?>,
					button_height: 29,
					button_placeholder_id: "spanButtonPlaceHolder",
					button_text: '<span class="theFont"><?=$this->lang->get('Select images')?></span>',
					button_text_style: ".theFont { font-weight: bold; font-family: Verdana, sans-serif, sans-serif; font-size: 14px; }",
					button_text_left_padding: 12,
					button_text_top_padding: 3,
					
					// The event handler functions are defined in handlers.js
					file_queued_handler : fileQueued,
					file_queue_error_handler : fileQueueError,
					file_dialog_complete_handler : fileDialogComplete,
					upload_start_handler : uploadStart,
					upload_progress_handler : uploadProgress,
					upload_error_handler : uploadError,
					upload_success_handler : uploadSuccess,
					upload_complete_handler : uploadComplete,
					queue_complete_handler : queueComplete	// Queue plugin event
				});
			}
<?php
			if (isset($_GET['ajax']) || isset($_GET['lite2'])) {
				echo 'window.setTimeout(\'generateSWFupload();\', 1000);';
			} else {
				echo 'generateSWFupload();';
			}
?>
		</script>
		<div id="galleryImagesExisting" style="overflow:auto;max-height: 250px;">
<?php
	while ($fetch = $this->db->fetch('SELECT * FROM '.$this->db->table('index').' WHERE type="IMAGE" ORDER BY published DESC LIMIT 50', 1)) {
		$image = $this->type($fetch->type, $fetch);
		$image->thumb = $this->imageScaleLink($image->src, '100', '100');
?>
		<img src="<?=$image->thumb?>" alt="<?=$this->lang->get('Click to add this image to this gallery')?>" onclick="jQuery('#galleryImagesSortable').append('<?=str_replace("\n", '', htmlspecialchars($this->multiUploadThumb($fetch->id, $this, '')))?>');" />
<?php
	}
?>
		</div>
	</div>
	<script type="text/javascript" >
		jQuery('#galleryImagesAdd').tabs();
	</script>
</div>
<?php
			return ob_get_clean();
		}
		
		function beforeSave($data)
		{
			if (empty($data->images)) {
				$this->images = '';
			}
			if (is_array($this->images)) {
				$this->images = implode(',', $this->images);
			}
		}

		function save($data = '')
		{
			$id = parent::save($data);
			if (isset($_GET['lite2'])) {
				echo '<div class="gallery">'.$id.'</div>';
				exit;
			}
		}
	}
?>
