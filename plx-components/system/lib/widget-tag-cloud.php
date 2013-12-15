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
						'label' => §('Title')
					) 
				),
				array(
					'type' => 'string',
					'name' => 'limit',
					'required' => FALSE,
					'options' => array(
						'label' => §('Limit'),
						'caption' => §('With >30 you can limit to tags bigger than something, = and < work too.'),
					) 
				),
				array(
					'type' => 'radio',
					'name' => 'sort',
					'required' => FALSE,
					'options' => array(
						'label' => §('Sort by'),
						'values' => array(
							1 => §('Counter'),
							2 => §('Alphabet')
						)
					) 
				),
				array(
					'type' => 'radio',
					'name' => 'showCounts',
					'required' => FALSE,
					'options' => array(
						'label' => §('Show counts'),
						'values' => array(
							1 => §('Yes'),
							2 => §('No')
						)
					) 
				),
				array(
					'type' => 'radio',
					'name' => 'display',
					'required' => FALSE,
					'options' => array(
						'label' => §('Display as'),
						'values' => array(
							1 => §('Tag Cloud'),
							2 => §('List')
						)
					) 
				),
				array(
					'type' => 'string',
					'name' => 'disabled',
					'required' => FALSE,
					'options' => array(
						'label' => §('Disable Tags'),
						'caption' => §('Separate with commas'),
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
			if ($this->a->getLevel(1) == $this->a->assigned('system.tags') && empty($this->a->levels[2])) {
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
					'href' => $this->a->assigned('system.tags').'/'.$tag,
					'class' => 'tag'.$fontSize,
					'count' => $count
				);
			}

			if ($this->data->display == 1) {
				return $this->t->get('widget-tag-cloud.php', array('tags' => $tags, 'showCounts' => $this->data->showCounts == 2 ? 0 : 1));
			} else {
				return $this->t->get('widget-tag-cloud-list.php', array('tags' => $tags, 'showCounts' => $this->data->showCounts == 2 ? 0 : 1));
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
