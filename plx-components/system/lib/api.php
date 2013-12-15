<?php
	class Api extends Core
	{
		static $instance;

		static function instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function control($level, $levels, $cache)
		{
			$merge = array(
				'error' => 'The API section you try to access does not exist.'
			);

			if (empty($levels[2])) {
				$merge = array(
					'name' => $this->getOption('site.name'),
					'owner' => $this->getOption('site.owner'),
					'ownerLink' => $this->getOption('site.ownerLink')
				);
			} else {
				switch (@$levels[2]) {
					case 'feed': $merge = $this->getFeed((object) $_POST); break;
					case 'editor': $merge = $this->editor(@$levels[3], @$levels[4]); break;
				}
			}
			if (isset(Core::$extendedApis[$levels[2]])) {
				require_once Core::$extendedApis[$levels[2]]['file'];
				if (isset(Core::$extendedApis[$levels[2]]['callback'][0]) && strpos(Core::$extendedApis[$levels[2]]['callback'][0], '(') !== false) {
					Core::$extendedApis[$levels[2]]['callback'][0] = eval('return '.Core::$extendedApis[$levels[2]]['callback'][0].';');
				}
				$callback = call_user_func_array(Core::$extendedApis[$levels[2]]['callback'], array($level, $levels, $cache));
				if (!empty($callback)) {
					$merge = $callback;
				}
			}

			header('Content-type: text/plain; charset=utf-8');
			echo $this->response($merge);
			exit;
		}

		function response($merge = array())
		{
			return $this->jsonFormat((object) array_merge(array(
				'plexus' => $this->a->getHome(),
				'version' => $this->system->version,
				'time' => microtime(1)
			), $merge));
		}

		function jsonFormat($json)
		{
			if (defined('JSON_PRETTY_PRINT')) {
				return json_encode($json, JSON_PRETTY_PRINT);
			}

			$tab = '	';
			$new_json = '';
			$indent_level = 0;
			$in_string = FALSE;

			if (is_object($json)) {
				$json_obj = $json;
			} else {
				$json_obj = json_decode($json);
			}

			if ($json_obj === FALSE) {
				return FALSE;
			}

			$json = json_encode($json_obj);
			$len = strlen($json);

			for ($c = 0; $c < $len; $c++) {
				$char = $json[$c];
				switch ($char) {

					case '{':
					case '[':
						if (!$in_string) {
							$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
							$indent_level++;
						} else {
							$new_json .= $char;
						}
					break;

					case '}':
					case ']':
						if (!$in_string) {
							$indent_level--;
							$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
						} else {
							$new_json .= $char;
						}
					break;

					case ',':
						if (!$in_string) {
							$new_json .= ",\n" . str_repeat($tab, $indent_level);
						} else {
							$new_json .= $char;
						}
					break;

					case ':':
						if (!$in_string) {
							$new_json .= ": ";
						} else {
							$new_json .= $char;
						}
					break;

					case '"':
						if ($c > 0 && $json[$c-1] != '\\') {
							$in_string = !$in_string;
						}

					default:
						$new_json .= $char;
					break;				   
				}
			}

			return $new_json;
		}

		function editor($section, $page)
		{
			if (empty($page)) {
				$page = 1;
			}
			if (!$this->access->granted()) {
				return array(
					'status' => 0,
					'message' => 'You do not have the necessary rights to access this api section.'
				);	
			}

			switch ($section) {
				case 'image':
				case 'gallery':
				case 'video':
				case 'file':
					return $this->editorBrowser($page, $section);
				break;
				case 'embeddings': return $this->editorEmbeddings($page); break;
				case 'upload':
					return $this->editorUpload();
				break;
			}
			return array(
				'status' => 0,
				'message' => 'No editor section called.'
			);	
		}

		function editorBrowser($page = 1, $section = 'image')
		{
			ob_start();
			$limit = 20;

			$sql = '';
			$search = '';
			$term = '';
			if (!empty($_GET['search'])) {
				$p = '"+'.str_replace(' ', ' +', $this->d->escape($_GET['search'])).'"';
				$sql = ' AND i.id=t.parent AND MATCH(t.value) AGAINST('.$p.' IN BOOLEAN MODE)';
				$search = '&search='.$_GET['search'];
				$term = $_GET['search'];
			}

			unset($_GET['search']);

			$sql = 'SELECT i.* FROM `'.$this->d->table('index').'` i'.(empty($sql) ? '' : ', `'.$this->d->table('textual').'` t').' WHERE i.type="'.strtoupper($section).'" '.$sql.' ORDER BY i.published DESC';
			$count = $this->d->query($sql);
			if (isset($count->num_rows)) {
				$count = $count->num_rows;
			} else {
				$count = 0;
			}
?>
			<div class="plxInterface plxEditorDialog plexusGUI">
			<?=$this->tools->pagination('plxImageSelector', $count, $page, $limit, 7);?>
			<div style="float: right; padding: 0 0 10px 0">
				<input type="text" style="font-size: 13px; width: 150px; height: 14px; vertical-align: top;" id="plxSelectorSearchField" value="<?=htmlspecialchars($term)?>" />
				<div id="plxSelectorSearchButton" class="button" style="vertical-align: top; height: 14px; line-height: 12px;"><?=§('Search')?></div>
			</div>
			<div class="clear"></div>
			<div style="width: 790px; height: 430px; overflow: hidden;">
				<div style="width: 800px; height: 440px;">
<?php
			if ($count) {
				$r = $this->d->query($sql.' LIMIT '.(($page*$limit)-$limit).','.$limit);
				if ($r->num_rows) {
					switch ($section) {
						case 'image':
							while ($i = $r->fetch_object('Image')) {
?>
						<img src="<?=$this->imageScaleLink($i->getOriginalLink(), 150, 100, $_GET['ajax'])?>" title="<?= empty($i->title) ? $i->file : $i->title ?>" onclick="tinyMCE.execCommand('mceInsertContent', false, '&lt;a class=&quot;lightThumb&quot; href=&quot;<?=$i->fullsize?>&quot; rel=&quot;lightboxPageContent&quot;&gt;&lt;img class=&quot;lightThumb&quot; src=&quot;<?=$i->resized?>&quot; alt=&quot;&quot; /&gt;&lt;/a&gt;'); jQuery.fancybox.close();" width="150" height="100" alt="" />
<?php
							}
						break;
						case 'gallery':
							while ($g = $r->fetch_object('Gallery')) {
								foreach ($g->images as $img) {
									$image = $this->type('IMAGE', $img);
									if (!empty($image->id)) {
										$g->thumb = $image->imageScaleLink($image->src, '150', '100', $_GET['ajax']);
										break;
									}
								}
?>
						<img src="<?=$g->thumb?>" onclick="tinyMCE.execCommand('mceInsertContent', false, '<div class=&quot;gallery&quot;><?=$g->id?></div>'); jQuery.fancybox.close(); tinyMCE.execCommand('mceRemoveControl', false, '<?=$_GET['editorId']?>');	tinyMCE.execCommand('mceAddControl', false, '<?=$_GET['editorId']?>');" width="150" height="100" alt="" />
<?php
							}
						break;
						case 'video':
							while ($v = $r->fetch_object('Video')) {
								$v->detect();
?>
						<img src="<?=$this->imageScaleLink($v->src, 100, 75)?>" onclick="tinyMCE.execCommand('mceInsertContent', false, '<div class=&quot;video&quot;><?=$v->id?></div>'); jQuery.fancybox.close(); tinyMCE.execCommand('mceRemoveControl', false, '<?=$_GET['editorId']?>');	tinyMCE.execCommand('mceAddControl', false, '<?=$_GET['editorId']?>');" width="150" height="100" alt="" />
<?php
							}
						break;
						case 'file':
							echo '<div id="wysiwygContentFiles"><ul>';
							while ($f = $r->fetch_object('File')) {
								if (empty($f->title)) {
									$f->title = $f->file;
								}
?>
						<li title="<?=$f->file?>" onclick="tinyMCE.execCommand('mceInsertContent', false, '<a class=&quot;download&quot; href=&quot;plx-file://<?=$f->target?>/<?=$f->file?>&quot;><?=htmlspecialchars($f->title)?></a>'); jQuery.fancybox.close();"><span class="plxWysiwygFileIcon"></span><?=$f->title?></li>
<?php
							}
							echo '</ul></div>';
						break;
					}
				}
			} else {
?>
			<p style="text-align: center; line-height: 300px; font-size: 4em; color: #D9D9D9;"><?=§('No matches')?></p>
<?php			
			}
?>
				</div>
			</div></div>
			<script type="text/javascript">
				jQuery('.plxInterface .plxPagination.plxImageSelector a').click(function() {
					var page = jQuery(this).attr('href').split('/');
					page = page[page.length-1];
					if (page.indexOf('?') != -1) {
						page = page.split('?');
						page = page[0];
					}
					if (isNaN(page)) {
						plxSelectorLoad(plxRoot + 'plx-api/editor/<?=$section?>?ajax=<?=urlencode($_GET['ajax'])?><?=$search?>');
					} else {
						plxSelectorLoad(plxRoot + 'plx-api/editor/<?=$section?>/' + page + '?ajax=<?=urlencode($_GET['ajax'])?><?=$search?>');
					}
					return false;
				});
				jQuery('#plxSelectorSearchField').keyup(function(event) {
					if (event.which == 13) {
						plxSelectorLoad(plxRoot + 'plx-api/editor/<?=$section?>' + '?ajax=<?=urlencode($_GET['ajax'])?>&search=' + jQuery(this).val());
					}  
				});
				jQuery('#plxSelectorSearchButton').click(function() {
					plxSelectorLoad(plxRoot + 'plx-api/editor/<?=$section?>' + '?ajax=<?=urlencode($_GET['ajax'])?>&search=' +jQuery('#plxSelectorSearchField').val());
				});
			</script>
<?php
			$data = ob_get_clean();

			return array(
				'status' => 1,
				'data' => $data
			);
		}

		function editorEmbeddings()
		{
			$dock = new Dock('plx.embedded', $_GET['contentId']);
			return array(
				'status' => 1,
				'data' => $dock->chooseWidget(TRUE, $_GET['editorId'])
			);
		}

		function editorUpload()
		{
			if ($this->access->granted('system.new')
				|| $this->access->granted('system.data.image')
				|| $this->access->granted('system.data.gallery')
				|| $this->access->granted('system.data.video')
				|| $this->access->granted('system.data.file')
			) {
				ob_start();
?>
				<ul class="editorChooseType">
<? if ($this->access->granted('system.new') || $this->access->granted('system.data.image')) : ?>
					<li class="image"><a href="<?=$this->a->assigned('system.new')?>/<?=§('Image')?>?ajax=<?=urlencode($this->a->getRoot())?>&lite2"><span class="icon"></span><?=§('Image')?></a></li>
<? endif; ?>
<? if ($this->access->granted('system.new') || $this->access->granted('system.data.gallery')) : ?>
					<li class="gallery"><a href="<?=$this->a->assigned('system.new')?>/<?=§('Gallery')?>?ajax=<?=urlencode($this->a->getRoot())?>&lite2"><span class="icon"></span><?=§('Gallery')?></a></li>
<? endif; ?>
<? if ($this->access->granted('system.new') || $this->access->granted('system.data.video')) : ?>
					<li class="video"><a href="<?=$this->a->assigned('system.new')?>/<?=§('Video')?>?ajax=<?=urlencode($this->a->getRoot())?>&lite2"><span class="icon"></span><?=§('Video')?></a></li>
<? endif; ?>
<? if ($this->access->granted('system.new') || $this->access->granted('system.data.file')) : ?>
					<li class="file"><a href="<?=$this->a->assigned('system.new')?>/<?=§('File')?>?ajax=<?=urlencode($this->a->getRoot())?>&lite2"><span class="icon"></span><?=§('File')?></a></li>
<? endif; ?>
				</ul>
				<script type="text/javascript">
					jQuery('ul.editorChooseType li a').fancybox({
						centerOnScroll: true,
						overlayOpacity: 0.5,
						overlayColor: '#000',
						transitionIn: 'elastic',
						transitionOut: 'elastic',
						onComplete: function(link) {
							jQuery('form.plexusFormAjax').ajaxForm({
								success: function(data) {
									jQuery.fancybox.close();
									tinyMCE.execInstanceCommand('<?=$_GET['editorId']?>', 'mceInsertContent', false, data);
									tinyMCE.execCommand('mceRemoveControl', false, '<?=$_GET['editorId']?>');
									tinyMCE.execCommand('mceAddControl', false, '<?=$_GET['editorId']?>');
									
								}
							});
						}
					});
				</script>
<?php
				$data = ob_get_clean();
				return array(
					'status' => 1,
					'data' => $data
				);	
			}
			return array(
				'status' => 0,
				'message' => 'You do not have the necessary rights to perform this action.'
			);		
		}
	}
?>
