<?php
	class PlexusConnect extends Component
	{
		static $instance;

		public $name = 'Plexus Connect';
		public $description = 'Connect Plexus driven websites together. Get and send push notifications when content is published.';
		public $version = 0.6;

		static function instance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function construct()
		{
			if (!empty($_POST['plexusConnectionRequest']) && !empty($_POST['plexusConnectionToken']) && !empty($_POST['plexusConnectionName'])) {
				require_once PLX_COMPONENTS.'plexus-connect/lib/api.php';
				echo PlexusApi::instance()->connectionReceive((object) $_POST);
				exit;
			}
			$this->a->assign('system.plexus', 'plx-plexus', array(&$this, 'control'));
			$this->a->assign('system.plexus.requests', 'requests', array(&$this, 'control'), 'system.plexus');
			$this->a->assign('system.plexus.connections', 'connections', array(&$this, 'control'), 'system.plexus');
			$this->o->connect('system.panel', 'addPanelMenuItem', $this);
			$this->extendPlexusAPI('connect', PLX_COMPONENTS.'plexus-connect/lib/api.php', array('PlexusAPI::instance()', 'control'));
			$this->o->connect('data.onSaveReady', 'onSaveReady', $this);
		}

		function control($level, $levels, $cache)
		{
			Control::$standalone = true;
			require_once PLX_COMPONENTS.'plexus-connect/lib/backend.php';
			$backend = new PlexusBackend;
			$backend->levels = $levels;
			return $backend;
		}

		function onSaveReady($data)
		{
			if ($data->justCreated) {
				if ($data->type == 'IMAGE' && $data->status == 2 || isset($data->doNotPush)) {
					// don't push that
				} else {
					require_once PLX_COMPONENTS.'plexus-connect/lib/api.php';
					$connections = PlexusApi::instance()->getConnections(2);
					foreach ($connections as $connection) {
						PlexusApi::instance()->setPush($connection, $data);
					}
				}
			}
		}

		function addPanelMenuItem($panel)
		{
			$panel->addItem('left', 'plexus', 'Plexus', array(
				'link' => $this->a->assigned('system.plexus'),
				'indicator' => self::instance()->getIndicators()
			));
			return $panel;
		}

		static function getIndicators()
		{
			$indicator = 0;
			$indicator += self::getUnreadCount();
			$indicator += self::getOpenRequests();
			return $indicator;
		}

		function getUnreadCount()
		{
			$r = Database2::instance()->query('SELECT * FROM '.Database2::instance()->table('pushes').' WHERE status=0');
			if (isset($r->num_rows)) {
				return $r->num_rows;
			}
			return 0;
		}

		function getOpenRequests()
		{
			$connections = Core::getOption('plexus.connect.connection');
			if (empty($connections)) {
				return 0;
			}
			if (!is_array($connections)) {
				$connections = array($connections);
			}
			$indicator = 0;
			foreach ($connections as $connection) {
				$connection = json_decode($connection->value);
				if ($connection->status == 1 && !isset($connection->validated)) {
					$indicator++;
				}
			}
			return $indicator;
		}
	}
?>
