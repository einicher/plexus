<?php
	class SiteFeedWidget extends Widget
	{
		public $name = 'Site Feed';
		public $description = 'Show a list of your newest objects on your site.';

		function editFields()
		{
			return array(
				array(
					'type' => 'string',
					'name' => 'title',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Title')
					)
				),
				array(
					'type' => 'number',
					'name' => 'limit',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Limit')
					)
				),
				array(
					'type' => 'custom',
					'name' => 'except',
					'required' => FALSE,
					'options' => array(
						'actor' => &$this,
						'call' => 'types'
					)
				),
				array(
					'type' => 'number',
					'name' => 'titleLength',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Title length (in chars, -1 = unlimited)')
					)
				),
				array(
					'type' => 'number',
					'name' => 'length',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Excerpt length (in words)')
					)
				),
				array(
					'type' => 'number',
					'name' => 'thumb',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Thumb width (in Pixel)')
					)
				),
				array(
					'type' => 'number',
					'name' => 'width',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Image width (in Pixel)')
					)
				),
				array(
					'type' => 'checkbox',
					'name' => 'typeSelector',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Show Type Selector')
					)
				),
				array(
					'type' => 'checkbox',
					'name' => 'pagination',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Disable Pagination')
					)
				)
			);
		}

		function init()
		{
			if (empty($this->data->except)) {
				$this->data->except = array('POST', 'IMAGE', 'LINK', 'GALLERY', 'MICRO', 'VIDEO');
			} else {
				$this->data->except = preg_split('=,=', $this->data->except, -1, PREG_SPLIT_NO_EMPTY);
			}
			if (empty($this->data->limit)) $this->data->limit = 10;
			if (empty($this->data->length)) $this->data->length = 28;
			if (empty($this->data->titleLength)) $this->data->titleLength = -1;
			if (empty($this->data->thumb)) $this->data->thumb = 100;
		}

		function getTitle()
		{
			if (!empty($this->data->title)) {
				return $this->data->title;
			}
		}

		function view()
		{
			$c = 0;
			$results = array();
			$cache = array();
			$this->tpl->connect('siteFeedThumb', $this->data->thumb);

			$draft = '';
			if ($this->access->granted()) {
				$draft = ' || (status=0 && author='.$this->access->getUser('id').')';
			}

			$publish = ' && published <='.time();
			if ($this->access->granted()) {
				$publish = ' && (published <= '.time().' || (published > '.time().' && author='.$this->access->getUser('id').'))';
			}

			$types = '';
			if (isset($_GET['filter']) && isset($this->data->typeSelector)) {
				$types .= '    type="'.Database::escape($_GET['filter']).'"';
			} else {
				if (!empty($this->data->except)) {
					foreach ($this->data->except as $type) {
						if (!empty($type)) {
							$types .= ' || type = "'.$type.'"';
						}
					}
				}
			}
			$types = ' && ('.substr($types, 4).')';

			$sql = 'SELECT * FROM '.$this->db->table('index').' WHERE (status=1'.$draft.')'.$types.$publish.' ORDER BY published DESC';
			if (!empty($this->sql)) {
				$sql = $this->sql;
			}
			$count = $this->db->count($sql);
			$start = 0;
			$current = 1;
			$limit =& $this->data->limit;
			if (empty($this->data->pagination) && $this->control->paginationActive) {
				$current = $this->control->paginationPage;
				$start = ceil($current*$limit)-$limit;
			}
			$sql .= ' LIMIT '.$start.','.($limit*10);
			$i = 0;
			$r = array();
			$rid = array();
			$fetchedResults = $this->d->get($sql, array('force_array' => true));

			if (count($fetchedResults) > 0 && $this->control->paginationActive) {
				$this->control->paginationUsed = true;
			}

			foreach ($fetchedResults as $fetch) {
				$r[$fetch->id] = $fetch;
				$rid[] = $fetch->id;
			}

			// new start
			if (!empty($rid)) {
				$fetched = $this->d->get('SELECT parent,name,value FROM #_properties WHERE parent IN('.implode(',', $rid).')');
				foreach ($fetched as $fetch) {
					$r[$fetch->parent]->{$fetch->name} = $fetch->value;
				}
			}

			$i = 0;
			foreach ($r as $id => $result) {
				$i++;
				$type = Core::getType($result->type);
				if (empty($type)) {
					continue;
				}
				require_once $type->file;
				$type = new $type->class($result, true);

				$type->excerptLength = $this->data->length;
				$type->titleLength = $this->data->titleLength == -1 ? 99999 : $this->data->titleLength;
				if (is_object($this->dock)) {
					foreach ($this->dock as $name => $value) {
						$this->data->$name = $value;
					}
				}

				$type->count = $i;
				$result = $type->result($this->data, $cache, $i);
				if (empty($result)) {
					continue;
				}
				$results[] = $result;

				$cache[] = $type;
				if ($i == $this->data->limit) {
					break;
				}
			}
			// new end

			$results = '<div class="articleFeed">'.implode('', $results).'<div class="clear"></div></div>';

			if (empty($this->data->pagination)) {
				if ($count > $limit) {
					$results .= $this->tools->pagination('siteFeedWidget', $count, $current, $limit, 7).'<div class="clear"></div>';
				}
			}

			if (!empty($this->data->typeSelector)) {
				$current = $this->lang->get('-- Filter by Type --');
				foreach (Core::$types as $name => $type) {
					if (in_array($name, $this->data->except)) {
						$this->tpl->repeat('result.php', 'typeSelectorItem', array(
							'item' => (object) array(
								'label' => $type['label'],
								'href' => $this->addr->current('', FALSE, array('filter' => $name))
							)
						));
					}
					if (isset($_GET['filter']) && $name == $_GET['filter']) {
						$current = $this->lang->get('Filtered by type {{<strong>'.$type['label'].'</strong>}}');
					}
				}
				$results = $this->tpl->repeat('result.php', 'typeSelector', array('current' => $current)).$results;
			}		

			return $results;
		}

		function types()
		{
			ob_start();
?>
<div class="formField">
	<div style="width: 49%; float: left;">
		<strong><?=$this->lang->get('Include')?></strong>
		<ul id="sortable2" class="connectedSortable" style="padding: 5px; border: 1px solid #070; background: #CCFFCC; list-style-type: none; margin: 0;">
			<? foreach ($this->data->except as $type) : if (!empty($type)) : ?>
			<li style="cursor: pointer;"><?=Core::$types[$type]['label']?><input type="hidden" name="type" value="<?=$type?>"></li>
			<? endif; endforeach; ?>
		</ul>
	</div>
	<div style="width: 49%; float: right;">
		<strong><?=$this->lang->get('Exclude')?></strong>
		<ul id="sortable1" class="connectedSortable" style="padding: 5px; border: 1px solid #D00; background: #FFCCCC; list-style-type: none; margin: 0;">
			<? foreach (Core::$types as $type => $data) : if (!in_array($type, $this->data->except)) : ?>
			<li style="cursor: pointer;"><?=$data['label']?><input type="hidden" name="type" value="<?=$type?>"></li>
			<? endif; endforeach; ?>
		</ul>
	</div>
	<input id="plxAjaxFormExcludes" type="hidden" name="except" value="<?=implode(',', $this->data->except)?>" /> 
	<div class="clear"></div>
	<script type="text/javascript" >
		jQuery('#sortable1, #sortable2').sortable({
			connectWith: '.connectedSortable',
			update: function (event, ui) {
				var val = '';
				jQuery('#sortable2 input').each(function() {
					val += ','+jQuery(this).val();
				});
				jQuery('#plxAjaxFormExcludes').val(val);
			}
		}).disableSelection();
	</script> 
</div>
<?php
			return ob_get_clean();
		}
	}
?>
