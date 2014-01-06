<?php
	class PlexusCrud extends Core
	{
		static $bluePrints;
		public $_cache;

		public $id = 0;
		public $type = '';
		public $address = '';
		public $parent = 0;
		public $status = 1;
		public $author = 0;
		public $published = 0;
		public $language = '';
		public $translation = 0;

		public $excerptLength = 28;
		public $classes = '';

		public $showTitle = true;
		public $showEditPanel = true;
		public $doRedirect = true; // redirect after successfull saving
		public $autoFormatAddress = false;
		public $noRealAddress = false; // if tur this data type will not be available trough an url
		public $disableSidebar = false;
		public $takeOverMainLoop = false; // used to tell main loop in $control->run that this objects assigned method will process further path levels on their own
		public $justCreated = false;

		protected $inDatabase2Mode = false;

		function __construct($mixed = '', $assign = FALSE)
		{
			if ($mixed == 'PLEXUS_DATABASE2_LOOP') {
				$this->clearBlueprint();
				$this->inDatabase2Mode = true;
				$this->construct();
				PlexusDataControl::fetchDataSet($this, $this);
				return;
			}

			$this->construct();

			$this->o->notify('system.model.construct', $this);
			$this->o->notify('system.model.construct.'.strtolower($this->type), $this);

			if (empty($mixed)) {
				$this->published = time();
				$this->author = empty(Access::$user->id) ? 0 : Access::$user->id;
				$this->language = Control::$language;
				if (!empty($this->id)) {
					PlexusDataControl::fetchDataSet($this, $this);
				}
			} elseif (is_object($mixed) && $assign) {
				foreach ($mixed as $name => $value) {
					$this->$name = $value;
				}
			} elseif (is_array($mixed)) {
				foreach ($mixed as $name => $value) {
					$this->$name = $value;
				}
			} else {
				PlexusDataControl::fetchDataSet($mixed, $this);
			}

			$this->init();

			return $this;
		}

		function construct()
		{
		}

		function init()
		{
		}

		function showTitle()
		{
			return $this->showTitle;
		}

		function getType()
		{
			return $this->type;
		}

		function getTitle()
		{
			if (isset($this->title)) {
				return $this->title;
			}
			if (isset($this->name)) {
				return $this->name;
			}
		}

		function getKeywords()
		{
			if (isset($this->tags)) {
				return $this->tags;
			}
			if (isset($this->keywords)) {
				return $this->keywords;
			}
		}

		function getContent()
		{
			if (isset($this->content)) {
				return $this->content;
			}
		}

		function getAuthor()
		{
			if (ContentControls::$editMode) {
				return;
			}
			if (!empty($this->author)) {
				$u = new User($this->author);
				return $u->name;
			}
		}

		function getAuthorLink()
		{
			if (!empty($this->author)) {
				$u = new User($this->author);
				return $u->getLink();
			}
		}

		function getDate()
		{
			if (ContentControls::$editMode) {
				return;
			}
			return date('c', $this->published);
		}

		function getDescription($words = 37)
		{
			if (ContentControls::$editMode && $words != -1) {
				return;
			}
			return $this->tools->cutByWords($this->excerptStrip($this->view(), $words));
		}

		function excerptStrip($text)
		{
			return trim(
				preg_replace('=[ ]+=', ' ', 
					str_replace("\t", ' ', 
						str_replace("\n", ' ', 
							str_replace("\r", ' ', 
								strip_tags(
									$text
								)
							)
						)
					)
				)
			);
		}

		function getMeta()
		{
			if (!empty($this->id)) {
				preg_match_all('/<img[^\>]*src="([^"]*)"/iU', $this->view(), $results);
				if (!empty($results[1][0])) {
					return '<meta property="og:image" content="'.$this->a->getHome(str_replace('../', '', $results[1][0])).'" />';
				}
			}
		}

		function add($type, $name, $required = FALSE, $options = '')
		{
			$add = TRUE;
			if (isset(self::$bluePrints[$this->type])) {
				foreach (self::$bluePrints[$this->type] as $key => $field) {
					if ($field['name'] == $name) {
						$add = FALSE;
						$return = $key;
					}
				}
			}

			if ($add) {
				if (isset($options['after'])) {
					foreach (self::$bluePrints[$this->type] as $key => $field) {
						if ($field['name'] == $options['after']) {
							$count = $key+10;
						}
					}
				} else {
					$count = 100;
					if (!empty(self::$bluePrints[$this->type])) {
						$keys = array_keys(self::$bluePrints[$this->type]);
						$count = array_pop($keys)+100;
					}
				}

				#$options['label'] .= ' '.$count;

				self::$bluePrints[$this->type][$count] = array(
					'type' => $type,
					'name' => $name,
					'required' => $required,
					'options' => $options
				);
	
				ksort(self::$bluePrints[$this->type]);
			}

			if ($this->inDatabase2Mode) {
				return;
			}

			switch ($type) {
				case 'number':
					if (empty($this->$name)) {
						$this->$name = 0;
					}
				break;

				case 'date':
				case 'time':
				case 'datetime':
					if (empty($this->$name)) {
						$this->$name = time();
					}
				break;

				case 'wysiwyg':
					if (ContentControls::$editMode) {
						Core::resource('jqueryui');
						Core::resource('tinymce');
					}
					if (!isset($this->$name)) {
						$this->$name = '';
					}
				break;

				case 'file':
					if (ContentControls::$editMode) {
						Core::resource('jqueryui');
					}
					if (!isset($this->$name)) {
						$this->$name = '';
					}
				break;

				default:
					if (!isset($this->$name)) {
						$this->$name = '';
					}
			}
		}

		function remove($name)
		{
			foreach (self::$bluePrints[$this->type] as $key => $field) {
				if ($field['name'] == $name) {
					unset(self::$bluePrints[$this->type][$key]);
					unset($this->$name);
				}
			}
		}

		function hide($name)
		{
			foreach (self::$bluePrints[$this->type] as $key => $field) {
				if ($field['name'] == $name) {
					self::$bluePrints[$this->type][$key]['hide'] = true;
				}
			}
		}

		function change($name, $type, $required = FALSE, $options = '')
		{
			foreach (self::$bluePrints[$this->type] as $key => $field) {
				if ($field['name'] == $name) {
					self::$bluePrints[$this->type][$key] = array(
						'type' => $type,
						'name' => $name,
						'required' => $required,
						'options' => $options
					);
				}
			}
		}

		function create()
		{
			$this->beforeCreate(TRUE);
			return edit();
		}

		function view()
		{
			if (empty($this->_cache->content)) {
				@$this->_cache->content = $this->o->notify('system.crud.view', $this->tools->detectSpecialSyntax($this->getContent()));
			}
			return $this->_cache->content;
		}

		function edit($action = '')
		{
			$this->beforeEdit(TRUE);
			$form = new Form(self::$bluePrints[$this->type], $this);
			if (!empty($action)) {
				$form->action = $action;
			}
			return $form->get();
		}

		// deprecated as of plexus 0.4.1
		function form($action = '')
		{
			return $this->edit($action);
		}

		/**
		 *	@param array|object $data overwrite $this properties with those in $data
		 */
		function save($data = '')
		{

			if (is_array($data)) {
				$data = (object) $data;
			}

			if (isset($data->plexusRemove)) {
				$this->delete();
				if ($this->doRedirect) {
					header('Location:./');
				} else {
					echo 'DELETED';
				}
				exit;
			}

			$this->beforeEdit();

			if (!empty($data)) {
				foreach ($data as $key => $value) {
					$this->$key = $value;
				}
			}

			foreach (self::$bluePrints[$this->type] as $field) {
				if (($field['type'] == 'date' || $field['type'] == 'time' || $field['type'] == 'datetime') && !is_numeric($this->$field['name'])) {
					$this->$field['name'] = strtotime($this->$field['name']);
				}
				if (!empty($data) && isset($this->{$field['name']}) && !isset($data->{$field['name']}) && empty($field['hide'])) {
					unset($this->{$field['name']});
				}
			}

			$this->o->notify(strtolower($this->type).'.beforeSaving', $this);
			$this->o->notify('data.beforeSaving', $this);

			$errors = array();

			foreach (self::$bluePrints[$this->type] as $field) {
				if ($field['required'] > 0 && empty($this->$field['name']) && @$this->$field['name'] !== 0 && $field['type'] != 'file') { // darf nicht leer sein, aber Null
					$fields[] = @$field['options']['label'];
					$errors[] = $field;
				} elseif ($field['type'] == 'captcha') {
					if (strtolower($this->$field['name']) == strtolower(strrev($_SESSION['captcha'][$field['name']]->string))) {
						$_SESSION['captcha'][$field['name']]->hold = TRUE;
					} else {
						$field['error'] = §('Your botcheck string is wrong.');
						$errors[] = $field;
						$this->$field['name'] = '';
					}
				}
			}

			if (empty($errors)) {
				if ($this->beforeSave($data) !== FALSE) {
					$this->_cache = '';
					if (empty($this->id)) {
						$this->justCreated = true;
					}
					$id = PlexusDataControl::save(self::$bluePrints[$this->type], $this, $this->autoFormatAddress);
					unset(self::$bluePrints[$this->type]);
					if (!empty($this->translation)) {
						$this->setOption('translation', $this->id, $this->translation);
					}
					$this->onSaveReady($data);
					$this->control->clearCache();
					unset($_SESSION['captcha']);
					$this->o->notify(strtolower($this->type).'.onSaveReady', $this);
					$this->o->notify('data.onSaveReady', $this);
					if ($this->doRedirect && $id !== FALSE) {
						header('Location: '.$this->a->httpGetVars($this->a->getRootLink($id)));
						exit;
					} else {
						return $id;
					}
				} else {
					return FALSE;
				}
			} else {
				$f = '';
				$messages = '';
				foreach ($errors as $key => $field) {
					if (empty($field['error'])) {
						$f .= ', '.$fields[$key];
					} else {
						$messages .= $field['error'].'<br />';
					}
				}
				if (!empty($f)) {
					$f = substr($f, 2);
					$messages .= §('The following required fields were left empty: {{'.$f.'}}').'<br />';
				}
				$this->error(substr($messages, 0, -6));
				return $errors;
			}
		}

		function beforeEdit()
		{
		}

		function beforeCreate()
		{
		}

		function beforeSave($data)
		{
		}

		function onSaveReady($data)
		{
		}

		function delete()
		{
			Database2::instance()->query('DELETE FROM `#_options` WHERE name="translations" && association='.$this->id);
			Database2::instance()->query('DELETE FROM `#_options` WHERE name="translations" && value='.$this->id);
			return PlexusDataControl::remove($this->id);
		}

		function set($name, $value) // fluid
		{
			$this->$name = $value; 
			return $this;
		}

		function get($name)
		{
			return $this->$name;
		}

		function getEditLink()
		{
			return $this->a->assigned('system.edit');
		}

		function getCancelLink()
		{
			return $this->a->current(1);
		}

		function getTranslateLink()
		{
			return $this->a->assigned('system.translate');
		}

		function getCopyLink()
		{
			return $this->a->assigned('system.copy');
		}

		function getBlueprint()
		{
			return self::$bluePrints[$this->type];
		}

		function clearBlueprint()
		{
			unset(self::$bluePrints[$this->type]);
		}

		function result()
		{
			return '<p><a href="'.$this->link().'">DATA_TYPE “'.$this->type.'” has not defined a method result() :\'(.</a></p>';
		}

		function getLink($absolute = FALSE)
		{
			if ($absolute) {
				return $this->a->getHomeLink($this->id);
			} else {
				return $this->a->getRootLink($this->id);
			}
		}

		// deprecated, use getLink()
		function link($absolute = FALSE)
		{
			return $this->getLink($absolute);
		}

		function api()
		{
			return (object) array(
				'id' => $this->id,
				'type' => $this->type,
				'published' => $this->published,
				'link' => $this->getLink(1)
			);
		}

		function previous()
		{
			$fetch = $this->d->get('SELECT * FROM `#_index` WHERE status=1 AND published<'.$this->published.' ORDER BY published DESC LIMIT 1');
			if (!empty($fetch)) {
				return $this->getData($fetch);
			}
		}

		function next()
		{
			$fetch = $this->d->get('SELECT * FROM `#_index` WHERE status=1 AND published>'.$this->published.' ORDER BY published ASC LIMIT 1');
			if (!empty($fetch)) {
				return $this->getData($fetch);
			}
		}

		function getTrackbackUrl()
		{
			return $this->a->assigned('system.trackback');
		}

		function getTrackbacksCount()
		{
			$t = $this->getOption('trackback', $this->id);
			if (empty($t)) {
				return 0;
			} elseif (is_array($t)) {
				return count($t);
			} else {
				return 1;
			}
		}

		function getTrackbacks()
		{
			$trackbacks = array();
			$t = $this->getOption('trackback', $this->id);

			if (!empty($t)) {
				if (!is_array($t)) {
					$t = array($t);
				}
				foreach ($t as $tb) {
					$tb = $tb->value;
					$trackbacks[] = json_decode($tb);
				}
			}
			return $trackbacks;
		}

		function saveProperty($property, $value = '')
		{
			if (!empty($value)) {
				$this->$property = $value;
			}
			return PlexusDataControl::saveProperty($this->id, $property, $this->$property);
		}
	}
?>
