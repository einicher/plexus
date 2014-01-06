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
						'label' => §('Title')
					)
				),
				array(
					'type' => 'number',
					'name' => 'limit',
					'required' => FALSE,
					'options' => array(
						'label' => §('Limit')
					)
				),
				array(
					'type' => 'custom',
					'name' => 'include',
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
						'label' => §('Title length (in chars, -1 = unlimited)')
					)
				),
				array(
					'type' => 'number',
					'name' => 'length',
					'required' => FALSE,
					'options' => array(
						'label' => §('Excerpt length (in words)')
					)
				),
				array(
					'type' => 'number',
					'name' => 'thumb',
					'required' => FALSE,
					'options' => array(
						'label' => §('Thumb width (in Pixel)')
					)
				),
				array(
					'type' => 'number',
					'name' => 'width',
					'required' => FALSE,
					'options' => array(
						'label' => §('Image width (in Pixel)')
					)
				),
				array(
					'type' => 'checkbox',
					'name' => 'typeSelector',
					'required' => FALSE,
					'options' => array(
						'label' => §('Show Type Selector')
					)
				),
				array(
					'type' => 'checkbox',
					'name' => 'pagination',
					'required' => FALSE,
					'options' => array(
						'label' => §('Disable Pagination')
					)
				)
			);
		}

		function init()
		{
			if (empty($this->data->include)) {
				$this->data->include = array('POST', 'IMAGE', 'LINK', 'GALLERY', 'MICRO', 'VIDEO');
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

		function view($type = '')
		{
			$f = new Feed;

			if (!empty($this->data->thumb)) {
				$f->set('thumbWidth', $this->data->thumb);
			}
			if (!empty($this->data->titleLength)) {
				$f->set('titleLength', $this->data->titleLength == -1 ? 99999 : $this->data->titleLength);
			}
			if (!empty($this->data->length)) {
				$f->set('excerptLength', $this->data->length == -1 ? 99999 : $this->data->length);
			}
			if (!empty($this->data->limit)) {
				$f->set('limit', $this->data->limit);
			}
			if (empty($this->data->pagination)) {
				$f->set('showPagination', true);
			}
			if (!empty($this->data->typeSelector)) {
				$f->set('showTypeSelector', true);
			}
			if (!empty($this->data->include)) {
				$f->set('include', $this->data->include);
			}

			return $f->view();
		}

		function types()
		{
			ob_start();
?>
<div class="formField clearfix">
	<label class="formFieldIncludeLabel"><?=§('Included data types')?></label>
	<? foreach (Core::$types as $type => $data) : ?>
		<label style="width: 33%; float: left;">
			<input type="checkbox" name="include[]" value="<?=$type?>"<?= in_array($type, $this->data->include) ? ' checked="checked"' : '' ?> />
			<?=$type?>
		</label>
	<? endforeach; ?>
</div>
<?php
			return ob_get_clean();
		}
	}
?>
