<?php
	class TagCloudWidget extends Widget
	{
		public $name = 'Tag Cloud / Tag List';
		public $description = 'Shows a cloud or list of your most popular Tags.';

		function editFields()
		{
			return array('type' => 'widget',
				array(
					'type' => 'string',
					'name' => 'title',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Title')
					) 
				),
				array(
					'type' => 'string',
					'name' => 'limit',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Limit'),
						'caption' => $this->lang->get('With >30 you can limit to tags bigger than something, = and < work too.'),
					) 
				),
				array(
					'type' => 'radio',
					'name' => 'sort',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Sort by'),
						'values' => array(
							1 => $this->lang->get('Counter'),
							2 => $this->lang->get('Alphabet')
						)
					) 
				),
				array(
					'type' => 'radio',
					'name' => 'showCounts',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Show counts'),
						'values' => array(
							1 => $this->lang->get('Yes'),
							2 => $this->lang->get('No')
						)
					) 
				),
				array(
					'type' => 'radio',
					'name' => 'display',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Display as'),
						'values' => array(
							1 => $this->lang->get('Tag Cloud'),
							2 => $this->lang->get('List')
						)
					) 
				),
				array(
					'type' => 'string',
					'name' => 'disabled',
					'required' => FALSE,
					'options' => array(
						'label' => $this->lang->get('Disable Tags'),
						'caption' => $this->lang->get('Separate with commas'),
					) 
				)
			);
		}

		function init()
		{
			if (empty($this->data->limit)) $this->data->limit = 30;
			if (empty($this->data->sort)) $this->data->sort = 2;
			if (empty($this->data->showCounts)) $this->data->showCounts = 1;
			if (empty($this->data->display)) $this->data->display = 1;
		}

		function getTitle()
		{
			return $this->data->title;
		}

		function view($force = FALSE)
		{
			if ($this->addr->getLevel(1) == $this->addr->assigned('system.tags') && empty($this->addr->levels[2])) {
				if ($force) {
					$this->data->limit = -1;
				} else {
					return;
				}
			}
			
			$tags = array();
			$results = $this->d->get('SELECT p.value FROM `#_properties` p, `#_index` i WHERE p.name="tags" && p.parent=i.id && (i.status=1 || i.status=2)');
			foreach ($results as $fetch) {
				if (!empty($fetch->value)) {
					$t = explode(',', $fetch->value);
					foreach ($t as $tag) {
						@$tags[trim($tag)]++;
					}
				}
			}

			$disabled = array();
			if (isset($this->data->disabled)) {
				$disabled = explode(',', $this->data->disabled);
				$disabled = array_map('trim', $disabled);
			}

			$i = 0;
            arsort($tags);
			$collect = array();
            foreach ($tags as $tag => $count) {
				if (in_array($tag, $disabled)) {
					continue;
				}
				if ($this->limitCondition($i, $count)) {
					if ($i == 0) $tagMax = $count; 
					$collect[$tag] = $count;
					$tagMin = $count;
					$i++;
				}
			}

			switch ($this->data->sort) {
				case 1: arsort($collect); break;
				case 2: ksort($collect); break;
			}

			$maxFontSize = 10;

			$tags = array();
			foreach ($collect as $tag => $count) {
                if ($count > $tagMin && $tagMax-$tagMin > 0) {
                    $fontSize = round(($maxFontSize*($count-$tagMin))/($tagMax-$tagMin));
                } else {
                    $fontSize = 0;
                }

				$tags[] = (object) array(
					'name' => $tag,
					'href' => $this->addr->assigned('system.tags').'/'.$tag,
					'class' => 'tag'.$fontSize,
					'count' => $count
				);
			}

			if ($this->data->display == 1) {
				return Template::get2('widget-tag-cloud.php', array('tags' => $tags, 'showCounts' => $this->data->showCounts == 2 ? 0 : 1));
			} else {
				return Template::get2('widget-tag-cloud-list.php', array('tags' => $tags, 'showCounts' => $this->data->showCounts == 2 ? 0 : 1));
			}
		}

		function limitCondition($i, $count)
		{
			if (substr($this->data->limit, 0, 1) == '<') {
				return $count < substr($this->data->limit, 1);
			} elseif (substr($this->data->limit, 0, 1) == '>') {
				return $count > substr($this->data->limit, 1);
			} elseif (substr($this->data->limit, 0, 1) == '=') {
				return $count == substr($this->data->limit, 1);
			} else {
				if ($this->data->limit == -1) return TRUE;
				return $i < $this->data->limit;
			}
		}
	}
?>
