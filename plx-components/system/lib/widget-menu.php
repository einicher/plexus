<?php
	class MenuWidget extends Widget
	{
		public $name = 'Menu Widget';
		public $description = 'Display a custom Menu.';

		function editFields()
		{
			return array('type' => 'widget',
				array('type' => 'string', 'name' => 'title', 'required' => FALSE, 'options' => array(
					'label' => §('Title')
				)),
				array('type' => 'custom', 'name' => 'items', 'required' => FALSE, 'options' => array(
					'actor' => &$this,
					'call' => 'getMenuEditor'
				))
			);
		}

		function getMenuEditor()
		{
			ob_start();
?>
<ul id="menuEditorBody" style="list-style-type: none; margin: 0; padding: 0;">
<?php
	if (!empty($this->data->label)) {
		foreach ($this->data->label as $key => $value) {
?>
	<li>
		<span class="handle" style="background: #070; cursor: move;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<label><?=§('Label')?></label>
		<input type="text" name="label[]" value="<?=$value?>" />
		<label for=""><?=§('Link')?></label>
		<input type="text" name="link[]" value="<?=$this->data->link[$key]?>" />
		<br />
	</li>
<?php

		}
	}
?>
	<li>
		<span class="handle" style="background: #070; cursor: move;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<label><?=§('Label')?></label>
		<input type="text" name="label[]" value="" />
		<label for=""><?=§('Link')?></label>
		<input type="text" name="link[]" value="" />
	</li>
</ul>
<div id="menuEditorDefault" style="display: none;">
	<li>
		<span class="handle" style="background: #070; cursor: move;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<label><?=§('Label')?></label>
		<input type="text" name="label[]" value="" />
		<label for=""><?=§('Link')?></label>
		<input type="text" name="link[]" value="" />
	</li>
</div>
<br />
<button id="menuEditorButton" type="button"><?=§('+ Add Item')?></button>
<p><?=§('Use the green handles to sort your menu.')?></p>
<p><?=§('If you leave a link field empty, the link will be made autmotaicly from the label text.')?></p>
<p><?=§('Link to homepage/frontpage is a single slash: “/”.')?></p>
<script type="text/javascript">
	jQuery('#menuEditorButton').click(function(e) {
		jQuery('#menuEditorBody').append(
			jQuery('#menuEditorDefault').html()
		);
	});
	jQuery('ul#menuEditorBody').sortable({
		handle: 'span.handle',
		cursor: 'crosshair'
	});
</script>
<?php
			return ob_get_clean();
		}

		function save($data, $id = '')
		{
			foreach ($data['link'] as $key => $value) {
				if (empty($value)) {
					if (empty($data['label'][$key])) {
						unset($data['label'][$key], $data['link'][$key]);
						continue;
					}
					$data['link'][$key] = Address::transform($data['label'][$key]);
				}
			}
			$data['label'] = array_merge($data['label']);
			$data['link'] = array_merge($data['link']);
			return parent::save($data, $id);
		}

		function getTitle()
		{
			if (!empty($this->data->title)) {
				return $this->data->title;
			}
		}

		function view($type = '')
		{
			if (empty($this->data->label)) {
				return '<br />';
			}

			$hasSub = false;
			foreach ($this->data->label as $key => $value) {
				if (substr($this->data->link[$key], 0, 1) != '/' || count(explode('/', $this->data->link[$key])) > 1) {
					$hasSub = true;
				}
			}

			$c = 0;
			$items = array();
			$count = count($this->data->label);
			foreach ($this->data->label as $key => $value) {
				$c++;
				$classes = 'item-'.$c;
				$external = FALSE;
				$link = $this->data->link[$key];
				$l = strlen($link);
				if (substr($link, 0, 7) == 'http://' || substr($link, 0, 8) == 'https://') {
					$href = $link;
					$external = TRUE;
					$classes .= ' external';
				} elseif ($link == '/') {
					$href = $this->a->getRoot();
				} elseif (substr($link, 0, 1) == '/') {
					$href = $this->a->getRoot(substr($link, 1));
				} else {
					if ($this->data->status == -77 && $this->dock->page == $this->data->includes[0]) {
						$href = $this->a->current($link);
					} else {
						$href = str_replace('//', '/', $this->a->current(-1).'/'.$link);
					}
				}

				if (($hasSub === false || '/'.$this->a->path == $link) && substr($link, 0, 1) == '/') {
					$active = ((substr('/'.$this->a->path, 0, strlen($link)) == $link && $link != '/') || ($link == '/' && empty($this->a->path))) ? TRUE : FALSE;
				} else {
					$active = $link == substr($this->a->path, 0-strlen($link));
				}

				if ($c == 1) {
					$classes .= ' first';
				} elseif ($c == $count) {
					$classes .= ' last';
				}
				if ($active) {
					$classes .= ' active';
				}
				$items[] = (object) array(
					'label' => $value,
					'href' =>  $href,
					'active' => $active,
					'classes' => $classes,
					'external' => $external
				);
			}

			$items = $this->o->notify('system.menuWidgetView.Items', $items, $this);

			return $this->t->get('widget-menu.php', array(
				'menu' => $this,
				'items' => $items
			));
		}
	}
?>
