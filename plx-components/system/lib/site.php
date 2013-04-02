<?php
	class Site extends Core
	{
		public $content;
		static $components = array();
		static $cache = array();

		function __construct(&$content)
		{
			$this->content =& $content;
		}

		function bodyClass()
		{
			$class = strtolower($this->content->getType());
			if ($this->content->disableSidebar) $class .= ' sidebarDisabled';
			$class .= ' '.$this->content->classes;
			$class = $this->observer->notify('system.bodyClass', $class, $this);
			return $class;
		}

		function getHome($append = '')
		{
			return $this->addr->getHome($append);
		}

		function getRoot($append = '')
		{
			return $this->addr->getRoot($append);
		}

		function getThemeRoot($file)
		{
			return $this->addr->getRoot($this->tpl->locateFile($file));
		}

		function getName()
		{
			return $this->getOption('site.name');
		}

		function getTitle($separator = ' • ')
		{
			if (ContentControls::$editMode) {
				if ($this->content->getTitle()) {
					return $this->observer->notify('siteTitle', strip_tags($this->content->getTitle(TRUE)).$separator.strip_tags($this->getName()), $separator);
				} else {
					return $this->observer->notify('siteTitle', $this->lang->get('Create').$separator.strip_tags($this->getName()), $separator);
				}
			} else {
				if ($this->content->getTitle()) {
					return $this->observer->notify('siteTitle', strip_tags($this->content->getTitle(TRUE)).$separator.strip_tags($this->getName()), $separator);
				} else {
					return $this->observer->notify('siteTitle', strip_tags($this->getName()), $separator);
				}
			}
		}

		function getGenerator()
		{
			return $this->system->name.' '.$this->system->version;
		}

		function getGeneratorHomepage()
		{
			return $this->system->home;
		}

		function getHeader($siteHeader = '')
		{
			return $this->observer->notify('site.getHeader', $siteHeader, $this->content);
		}

		function getDefaultHead($args = array())
		{
			return $this->tpl->get('head.php', array('args' => $args));
		}

		function getDock($name = 'default', $options = '')
		{
			if ($this->content->disableSidebar && ($name == '' || $name == 'default' || $name == 'sidebar')) {
				return;
			}

			$dock = $this->observer->notify('system.occupySidebar.beforeLoad', FALSE, $name, $options, $this->content);
			if (empty($dock)) {
				$dock = new Dock($name, $this->content->id);
				if (is_array($options)) {
					$dock->options = (object) $options;
					foreach ($dock->options as $n => $value) {
						$dock->$n = '';
						$dock->$n =& $dock->options->$n;
						if ($n == 'exclude' && in_array($this->content->id, explode(',', $value))) {
							return;
						}
					}
				}
				$dock = $dock->view();
				$dock = $this->observer->notify('system.occupySidebar.afterLoad', $dock, $name, $options, $this->content);
			}
			return $dock;
		}

		function showEditPanel()
		{
			if (!isset($_GET['crawl']) && $this->content->showEditPanel && (
				$this->access->granted('system.edit')
				|| ($this->access->granted('system.editOwnData') && $this->content->author == Access::$user->id)
				|| ($this->access->granted('system.editOwnData') && $this->content->id == Access::$user->id)
			)) {
				return true;
			}
		}

		function showEditLink()
		{
			if (!ContentControls::$editMode
			 && $this->content->showEditPanel
			 && $this->addr->getLevel(-1) != $this->addr->getAddress('system.translate')
			 && $this->addr->getLevel(-2) != $this->addr->getAddress('system.translate')
			 && $this->addr->getLevel(-1) != $this->addr->getAddress('system.copy')
			 && $this->addr->getLevel(-2) != $this->addr->getAddress('system.copy')
			 && ($this->access->granted('system.edit')
			 	|| ($this->access->granted('system.editOwnData') && $this->content->author == Access::$user->id)
			 	|| ($this->access->granted('system.editOwnData') && $this->content->id == Access::$user->id)
			 )
			) {
				return true;
			}
		}

		function showCancelLink()
		{
			if (!isset($_GET['ajax'])
			 && !isset($_GET['popup'])
			 && $this->content->showEditPanel
			 && (
			 	$this->access->granted('system.edit')
			 	|| ($this->access->granted('system.editOwnData') && $this->content->author == Access::$user->id)
			 	|| ($this->access->granted('system.editOwnData') && $this->content->id == Access::$user->id)
			 )
			 && (ContentControls::$editMode
			 	|| $this->addr->getLevel(-1) == $this->addr->getAddress('system.translate')
			 	|| $this->addr->getLevel(-2) == $this->addr->getAddress('system.translate')
			 	|| $this->addr->getLevel(-1) == $this->addr->getAddress('system.copy')
			 	|| $this->addr->getLevel(-2) == $this->addr->getAddress('system.copy')
			 )
			) {
				return true;
			}
		}

		function getContentView()
		{
			if ($this->content->status == 0 && !empty($this->content->id)) {
				$this->info($this->lang->get('Status of this {{'.$this->content->type.'}} is set to Draft, only you can see it.'));
			}
			if ($this->content->published > time() && !empty($this->content->id)) {
				$this->info($this->lang->get('Publish date of this {{'.$this->content->type.'}} is in the future ({{<strong>'.$this->tools->detectTime($this->content->published).'</strong>}}), it will not appear to the public until then.'));
			}

			$custom = Template::locateFile('custom-'.$this->content->id.'.php');
			if (file_exists($custom)) {
				$content = $this->tools->detectSpecialSyntax($this->tpl->get($custom));
			} else {
				$content = $this->observer->notify('system.content.display', $this->content->view(), $this->content);
			}

			return Template::get2('content.php', array('main' => $content));
		}

		function getContentEdit()
		{
			$this->observer->notify('site.beforeContentEdit', &$this);
			
			if (isset($_GET['ajax'])) {
				$this->content->doRedirect = FALSE;
			}

			if (!empty($_POST['plexusForm'])) {
				if ($this->addr->getLevel(-2) == $this->addr->assigned('system.new')) {
					$this->content->autoFormatAddress = TRUE;
				}
				$data = (object) $_POST;
				$data = $this->observer->notify('site.data.beforeSave', $data, &$this->content);
				$save = $this->content->save($data);
				if (is_numeric($save) && $save !== FALSE && isset($_GET['ajax'])) {
					echo $save;
					exit;
				}
			}

			return Template::get2('content.php', array('main' => $this->content->form()));
		}

		function getContent()
		{
			$this->observer->notify('site.beforeGetContent', &$this);
			
			if (ContentControls::$editMode && $this->content->type != 'ERROR404' && $this->content->type != 'ERROR403') {
				$this->content->showTitle = FALSE;
				$content = $this->getContentEdit();
			} else {
				$content = $this->getContentView();
			}

			return $this->observer->notify('site.getContent', $content);
		}

		function getFooter()
		{
			if (!empty(self::$cache['footer'])) {
				return self::$cache['footer'];
			}
			ob_start();
?>
		<script type="text/javascript" src="<?=$this->getRoot(PLX_SYSTEM.'theme/footer.js')?>"></script>
<?php
			$script = ob_get_clean();
			return $this->observer->notify('site.footer', $this->panel().$script);
		}

		function panel()
		{
			if (!$this->access->granted('system.showPanel') || isset($_GET['crawl'])) {
				return;
			}

			$panel = new Panel;

			$panel->addItem('left', 'home', §('Home'), array(
				'link' => $this->addr->getHome()
			));

			$panel->addItem('left', 'new', §('New'), array(
				'link' => $this->addr->assigned('system.new')
			));
			$types = array();
			foreach (Core::$types as $name => $type) {
				$panel->addItem('new', $name, $type['label'], array(
					'link' => $this->addr->assigned('system.new').'/'.$this->addr->transform($type['label'])
				));
			}

			$panel->addItem('left', 'preferences', §('Preferences'), array(
				'link' => $this->addr->assigned('system.preferences')
			));

			$panel->addItem('right', 'database', §('Database'), array(
				'link' => $this->addr->assigned('system.database')
			));
			$panel->addItem('right', 'logout', §('Logout'), array(
				'link' => $this->addr->assigned('system.logout')
			));

			$panel = $this->observer->notify('system.panel', $panel);

			return $panel->view();
		}

		function hasInfoMessages()
		{
			return !empty(Core::$infos);
		}

		function getInfoMessages()
		{
			return Core::$infos;
		}

		function hasErrorMessages()
		{
			return !empty(Core::$errors);
		}

		function getErrorMessages()
		{
			return Core::$errors;
		}

		function isHome()
		{
			if (count($this->addr->levels) == 1 || (count($this->addr->levels) == 2 && $this->control->paginationActive) || (count(Control::$languages) > 1 && count($this->addr->levels) == 2)) {
				return TRUE;
			}
			return FALSE;
		}
	}
?>
