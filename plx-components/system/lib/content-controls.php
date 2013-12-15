<?php
	class ContentControls extends Core
	{
		static $instance;
		static $editMode = false;

		static public function &instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function plxNew($level, $levels, $cache)
		{
			if ($this->access->granted()) {
				if ($this->access->granted('system.new')
				|| $this->access->granted('system.data.image')
				|| $this->access->granted('system.data.gallery')
				|| $this->access->granted('system.data.video')
				|| $this->access->granted('system.data.file')) {
					if (count($levels) == 2) {
						$current = new Page(§('Choose data type'), $this->choose());
						$current->showEditPanel = FALSE;
						return $current;
					} else {
						self::$editMode = TRUE;
						return $this->detectTypeByAddress($levels[2]);
					}
				} else {
					$page = new Page(§('Access rights needed'), §('You do not have the necessary permissions to add new contents.'));
					$page->type = 'ERROR403';
					return $page;
				}
			} else {
				return System::instance()->login($level, $levels, $cache);
			}
		}

		function plxCreate($level, $levels, $cache)
		{
			if ($this->access->granted()) {
				if ($this->access->granted('system.create')) {
					if ($this->a->getLevel(-2, $levels) == $this->a->getAddress('system.create')) {
						self::$editMode = TRUE;
						$current = $this->detectTypeByAddress($this->a->getLevel(-1, $levels));
						if (isset($current->title)) {
							$current->title = urldecode($this->a->getLevel(-3, $levels));
						}
						$current->address = $this->a->transform($this->a->getLevel(-3, $levels));
						$key = count($cache)-1;
						if ($key < 0) $key = 0;
						if (!empty($cache)) {
							$homepage = $this->d->get('SELECT id FROM `#_index` WHERE `address`="" && `parent`=0 && `language`="'.Control::$language.'"');
							if (isset($cache[count($cache)-2])) {
								$current->parent = $cache[count($cache)-2]->id;
							}
							if (!empty($homepage) && $current->parent == $homepage->id) {
								$current->parent = 0;
							}
						}
						return $current;
					} else {
						$current = new Page(§('Choose data type'), $this->choose());
						$current->showEditPanel = FALSE;
						return $current;
					}
				} else {
					$page = new Page(§('Access rights needed'), §('You do not have the necessary permissions to create this content.'));
					$page->type = 'ERROR403';
					return $page;
				}
			} else {
				return System::login($level, $levels, $cache);
			}
		}

		function plxTranslate($level, $levels, $cache)
		{
			if (empty($levels[$level+1])) {
				$translate = $this->getData(array_pop($cache));
				$translations = Language::getTranslations($translate->id);
				$t = array();
				foreach ($translations as $translation) {
					if (empty($translation)) {
						continue;
					}
					$fetch = $this->getData($translation);
					$t[$fetch->language] = $fetch;
				}
				return new Page(§('Choose language'), $this->t->get('system', 'translate.php', array(
					'languages' => self::$languages,
					'current' => $translate,
					'translations' => $t
				)));
			} else {
				self::$editMode = true;
				$translate = $this->getData(array_pop($cache));
				$type = $this->getType($translate->type);
				require_once $type->file;
				$current = new $type->class;
				$current->language = $levels[$level+1];
				$current->title = $translate->title;
				$current->translation = $translate->id;
				if (!empty($translate->content)) {
					$current->content = $translate->content;
				}
				return $current;
			}
		}

		function plxCopy($level, $levels, $cache)
		{
			if ($this->access->granted()) {
				$current = $cache[count($cache)-2];
				if ($this->access->granted('system.edit') || ($this->access->granted('system.editOwnData') && $current->author == Access::$user->id) || ($this->access->granted('system.editOwnData') && $current->id == Access::$user->id)) {
					self::$editMode = TRUE;
					$type = $this->getDataType($current->type);
					$type->id = 0;
					require_once $type->file;
					$type = new $type->class($current->id);
					$type->id = 0;
					$type->address = '';
					$type->autoFormatAddress = 1;
					$type->doRedirect = 1;
					return $type;
				} else {
					$page = new Page(§('Access rights needed'), §('You do not have the necessary permissions to edit this content.'));
					$page->type = 'ERROR403';
					return $page;
				}
			} else {
				return $this->login($level, $levels, $cache);
			}
		}

		function plxEdit($level, $levels, $cache)
		{
			if ($this->access->granted()) {
				$current = $cache[count($cache)-2];
				if ($this->access->granted('system.edit')
				|| ($this->access->granted('system.editOwnData') && $current->author == Access::$user->id)
				|| ($this->access->granted('system.editOwnData') && $current->id == Access::$user->id)
				) {
					self::$editMode = TRUE;
					$type = $this->getDataType($current->type);
					require_once $type->file;
					return new $type->class($current->id);
				} else {
					$page = new Page(§('Access rights needed'), §('You do not have the necessary permissions to edit this content.'));
					$page->type = 'ERROR403';
					return $page;
				}
			} else {
				return System::login($level, $levels, $cache);
			}
		}

		public function choose()
		{
			$types = array();
			foreach (Core::$types as $name => $type)
			{
				if ($this->access->granted('system.data.'.strtolower($name))) {
					$type = (object) $type;
					$type->label = §($type->label);
					$type->address = $this->a->current($this->a->transform($type->label));
					$types[] = $type;
				}
			}
			return $this->t->get('choose.php', array(
				'types' => $types
			));
		}

		public function detectTypeByAddress($label)
		{
			foreach (Core::$types as $name => $type)
			{
				$type = (object) $type;
				$address = $this->a->transform($type->label);
				if ($address == $label) {
					if ($this->access->granted('system.data.'.strtolower($name))) {
						require_once $type->file;
						return new $type->class;
					} else {
						$page = new Page(§('Access rights needed'), §('You do not have the necessary permissions to create this content type.'));
						$page->type = 'ERROR403';
						return $page;					
					}
				}
			}
		}
	}
?>
