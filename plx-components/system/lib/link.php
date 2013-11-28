<?php
	class Link extends PlexusCrud
	{
		public $type = 'LINK';
		public $thumbTarget = 'linkThumbs';

		function construct()
		{
			$this->add('string', 'link', TRUE, array(
				'label' => $this->lang->get('Link'),
				'transformToAddress' => 1
			));

			$this->add('custom', 'NODATA', FALSE, array(
				'call' => 'analyseLink',
				'actor' => &$this 
			));

			$this->add('file', 'thumb', FALSE, array(
				'label' => $this->lang->get('Thumb'),
				'target' => $this->thumbTarget
			));

			$this->add('string', 'title', FALSE, array(
				'label' => $this->lang->get('Title'),
				'transformToAddress' => 1
			));

			$this->add('wysiwyg', 'description', FALSE, array(
				'label' => $this->lang->get('Description'),
				'rows' => 5
			));

			$this->add('text', 'comment', FALSE, array(
				'label' => $this->lang->get('Your comment'),
				'rows' => 5,
				'counter' => TRUE
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
			if (!empty($this->thumb)) {
				$this->thumbSrc = $this->addr->getRoot($this->getStorage($this->thumbTarget).'/'.$this->thumb);
				$this->thumbSource = $this->thumbSrc;
			}		
		}

		function getTitle()
		{
			return $this->title;
		}

		function getDescription()
		{
			if (empty($this->comment)) {
				return $this->tools->cutByWords($this->description);
			} else {
				return $this->tools->cutByWords($this->comment);
			}
		}

		function getContent()
		{
			$width = $this->getOption('linkThumbWidth');
			if (empty($width)) {
				$width = 100;
			}
			if (!empty($this->thumb)) {
				$this->thumbSrc = $this->imageScaleLink($this->getStorage($this->thumbTarget).'/'.$this->thumb, $width);
			}
			return Template::get2('view-link.php', array('link' => $this, 'thumbWidth' => $width));
		}

		function analyseLink($fields)
		{
			ob_start();
?>
<div id="customAnalyse" class="formField">
	<button type="button" onclick="analyseLink(this);"><?=§('Analyze Link')?></button>

	<div id="customAnalyseImageContainer" style="display: none; margin: 1em 0 0 0;">
		<label><?=$this->lang->get('Select Preview Image')?></label><br />
		<div style="width: 250px; height: 150px; float: left; margin: 0 20px 0 0;"><img id="customAnalyseImage" src="" alt="" style="max-width: 250px; max-height: 150px;" /></div>
		<?=$this->lang->get('Image {{<span id="customAnalyseImageCurrent"></span>}} of {{<span id="customAnalyseImageCount"></span>}}')?>
		<br /><br />
		<span id="customAnalyseImagePrev" class="link"><?=$this->lang->get('Previous')?></span>
		<span id="customAnalyseImageNext" class="link"><?=$this->lang->get('Next')?></span>
		<br /><br />
		<span id="customAnalyseImageNone" class="link"><?=$this->lang->get('No preview image')?></span>
		<div class="clear"></div>
	</div>

	<script type="text/javascript" >
		// <![CDATA[
			customAnalyseLinkPointer = 0;
			var analyseLink = function(button)
			{
				button.innerHTML = '<?=§('Analyse ...')?>';
				button.disabled = true;
				var link = jQuery('#link').val();
				if (link == '') {
					alert('<?=$this->lang->get('Please fill in a link first in the field above!')?>');
					button.disabled = false;
					return;
				}
				if (link.substr(0,7) != 'http://') {
					link = 'http://' + link;
				}
				jQuery.get(root + 'plxAjax/analyseLink?url=' + encodeURI(link), function(data) {
					data = jQuery.parseJSON(data);
					jQuery('#title').val(data.title);

					if (data.description != undefined) {
						var id = jQuery('.plexusFormAjax textarea[name=description]').attr('id');
						if (id) {
							tinyMCE.execCommand('mceRemoveControl', false, id);
							jQuery('.plexusFormAjax textarea[name=description]').val(data.description);
							tinyMCE.execCommand('mceAddControl', false, id);
						} else {
							tinyMCE.execCommand('mceRemoveControl', false, document.plexusForm.description.id);
							document.plexusForm.description.value = data.description;
							tinyMCE.execCommand('mceAddControl', false, document.plexusForm.description.id);
						}
					}

					if (data.keywords != undefined) {
						var id = jQuery('.plexusFormAjax textarea[name=tags]').attr('id');
						if (id) {
							jQuery('.plexusFormAjax textarea[name=tags]').val(data.keywords);
						} else {
							document.plexusForm.tags.value = data.keywords;
						}
					}

					if (data.images != undefined) {
						customAnalyseLinkPointer = 0;
						jQuery('#customAnalyseImageCurrent').html(1);
						jQuery('#customAnalyseImageCount').html(data.images.length);
						jQuery('#customAnalyseImage').attr('src', data.images[customAnalyseLinkPointer]);
						jQuery('#thumbURL').val(data.images[customAnalyseLinkPointer]);
						jQuery('#customAnalyseImageContainer').css('display', 'block');
						jQuery('#customAnalyseImageNone').click(function(e) {
							jQuery('#customAnalyseImageContainer').css('display', 'none');
							jQuery('#thumbURL').val('');
						});
						jQuery('#customAnalyseImageNext').click(function(e) {
							customAnalyseLinkPointer++;
							if (customAnalyseLinkPointer >= data.images.length) {
								customAnalyseLinkPointer = 0;
							}
							jQuery('#customAnalyseImage').attr('src', data.images[customAnalyseLinkPointer]);
							jQuery('#customAnalyseImageCurrent').html(customAnalyseLinkPointer+1);
							jQuery('#thumbURL').val(data.images[customAnalyseLinkPointer]);
						});
						jQuery('#customAnalyseImagePrev').click(function(e) {
							customAnalyseLinkPointer--;
							if (customAnalyseLinkPointer < 0) {
								customAnalyseLinkPointer = data.images.length-1;
							}
							jQuery('#customAnalyseImage').attr('src', data.images[customAnalyseLinkPointer]);
							jQuery('#customAnalyseImageCurrent').html(customAnalyseLinkPointer+1);
							jQuery('#thumbURL').val(data.images[customAnalyseLinkPointer]);
						});
					}
					button.disabled = false;
					button.innerHTML = '<?=§('Analyze Link')?>';
				})
			}
		// ]]>
	</script>
</div>
<?php
			return ob_get_clean();
		}

		function analyse($url)
		{
			$data = new stdClass;

			if (substr($url, -4) == '.jpg') {
				$data->images[0] = $url;
				return $data;
			}

			$page = @file_get_contents(urldecode($url));
			if (!empty($page)) {
				$page = mb_convert_encoding($page, 'UTF-8', mb_detect_encoding($page));
				if (!empty($page)) {
					$page = preg_replace('/\<script[^\>]*\>.*\<\/script\>/isU', '', $page);
					$page = preg_replace('/\<noscript[^\>]*\>.*\<\/noscript\>/isU', '', $page);

					preg_match('/\<title\>([^\<]*)\<\/title\>/', $page, $title);
					if (!empty($title)) {
						$data->title = $title[1];
					}
				
					preg_match_all('/\<meta([^\>]*)>/', $page, $meta);
					foreach ($meta[1] as $m) {
						preg_match_all('/(\S*)\=\"([^"]*)\"/isU', $m, $m);
						$use = 0;
						$atts = array();
						foreach ($m[1] as $key => $n) {
							if ($n == 'name' && $m[2][$key] == 'description') {
								$atts[$n] = $m[2][$key];
								$use = 1;
							}
							if ($n == 'property' && $m[2][$key] == 'og:description') {
								$atts[$n] = $m[2][$key];
								$use = 2;
							}
							if ($n == 'name' && $m[2][$key] == 'keywords') {
								$atts[$n] = $m[2][$key];
								$use = 3;
							}
							if ($n == 'property' && $m[2][$key] == 'og:image') {
								$atts[$n] = $m[2][$key];
								$use = 4;
							}
							if ($n == 'property' && $m[2][$key] == 'og:title') {
								$atts[$n] = $m[2][$key];
								$use = 5;
							}
							if ($n == 'content') {
								$atts[$n] = $m[2][$key];
							}
						}
						if ($use) {
							switch ($use) {
								case 1:
								case 2:
									$data->description = str_replace("\r", '', str_replace("\n", '', $atts['content']));
								break;
								case 3:
									$data->keywords = str_replace("\r", '', str_replace("\n", '', $atts['content']));
								break;
								case 4:
									if (empty($data->images)) {
										$data->images = array(str_replace("\r", '', str_replace("\n", '', $atts['content'])));
									} else {
										$data->images[] = str_replace("\r", '', str_replace("\n", '', $atts['content']));
									}
								break;
								case 5:
									$data->title = str_replace("\r", '', str_replace("\n", '', $atts['content']));
								break;
							}
						}
					}

					if (empty($data->images)) {
						preg_match_all('/\<img[^\>]*src="([^\"]*)"[^\>]*>/', $page, $images);

						$data->images = array();
						foreach ($images[1] as $img) {
							$img = trim($img);
							if (empty($img)) {
								continue;
							} elseif (substr($img, 0, 1) == '/') {
								$host = explode('/', $url);
								$img = 'http://'.$host[2].$img;
							} elseif (substr($img, 0, 7) != 'http://') {
								$host = explode('/', $url);
								array_pop($host);
								$host = implode('/', $host);
								$img = $host.'/'.$img;
							}

							$size = @getimagesize($img);
							if (!empty($size) && $size[0] >= 100 || $size[1] >= 100) {
								$data->images[] = $img;
							}
						}
					}

					@$data->title = strip_tags(html_entity_decode(@$data->title, ENT_QUOTES, 'UTF-8'));
				   	@$data->description = strip_tags(html_entity_decode(@$data->description, ENT_QUOTES, 'UTF-8'));
					@$data->keywords = strip_tags(html_entity_decode(@$data->keywords, ENT_QUOTES, 'UTF-8'));
				}
				return $data;
			}
		}

		function result()
		{
			if (!empty($this->thumb)) {
				$this->hasThumb = TRUE;
				$this->thumbSrc = $this->getStorage($this->thumbTarget.'/'.$this->thumb);
			}
			if (!empty($this->comment)) {
				$this->pre = '<p class="comment">'.strip_tags($this->comment).'</p>';
			}
			if (!empty($this->description)) {
				$this->excerpt = $this->tools->cutByWords(strip_tags($this->tools->detectSpecialSyntax($this->description)), $this->excerptLength);
			}
			$this->footer = 1;
			return $this->tpl->get2('result-single.php', array('result' => $this));
		}
	}
?>
