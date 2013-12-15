<?php
	class Setup extends Core
	{
		function __construct($host = '')
		{
			$this->host = $host;
		}

		function __toString()
		{
			return $this->get();
		}

		function get()
		{
			$this->content = $this->runSetup();
			return $this->t->get('setup.php', array('setup' => $this));
		}

		function runSetup()
		{
			$dependencies = $this->checkDependencies();
			if (strpos($dependencies, 'class="red"') === FALSE) {
				if ($this->checkDatabase()) {
					if ($this->checkAdmin()) {
						if ($this->checkSite()) {
							return TRUE;
						} else {
							return $this->setupSite();
						}
					} else {
						return $this->setupAdmin();
					}
				} else {
					return $this->setupDatabase();
				}
			} else {
				return $dependencies;
			}
		}

		function checkDependencies()
		{
			$this->title = §('Dependency Checks');
			$checks = '<p>'.§('Please correct all problems (the red ones are problems) and {{<a href="javascript:location.reload()">refresh</a>}}.').'</p><ul>';
			$checks .= version_compare(PHP_VERSION, '5.0.0', '>=') ? '<li class="green">'.§('Your PHP version is '.PHP_VERSION.'.').'</li>' : '<li class="red">'.§('At least PHP version 5 needed, version '.PHP_VERSION.' found.').'</li>';
			@chmod(PLX_STORAGE, 0777);
			$checks .= is_writable(PLX_STORAGE) ? '<li class="green">'.§('Storage directory is writable.').'</li>' : '<li class="red">'.§('Storage directory '.PLX_STORAGE.' ist not writable').'</li>';
			if (file_exists(Core::getStorage('config.php'))) {
				$checks .= is_writable(Core::getStorage('config.php')) ? '<li class="green">'.§('Configuration file is writable.').'</li>' : '<li class="red">'.§('Configuration file '.PLX_STORAGE.'config.php ist not writable').'</li>';
			}
			$checks .= @file_get_contents($this->a->getHome('plxCheckForRewrittenUrls')) == 'TRUE' ? '<li class="green">'.§('Url rewriting is possible.').'</li>' : '<li class="red">'.§('Rwritten URLs are not possible! Maybe mod_rewrite is not enabled?').'</li>';
			#$checks .=  ? '<li class="green">'.§('').'</div>' : '<li class="red">'.§('').'</div>';
			return $checks.'</ul>';
		}

		function checkDatabase()
		{
			if (   isset($this->conf->database)
				&& isset($this->conf->database->host)
				&& isset($this->conf->database->user)
				&& isset($this->conf->database->password)
				&& isset($this->conf->database->name)
				&& @mysql_connect(
					   $this->conf->database->host,
					   $this->conf->database->user,
					   $this->conf->database->password
				   ) !== FALSE
				&& @mysql_select_db($this->conf->database->name) !== FALSE
			) {
				return TRUE;
			}
			return FALSE;
		}

		function checkAdmin()
		{
			return $this->d->get('SELECT i.id FROM `#_index` i, `#_properties` p WHERE i.type="USER" && i.id=p.parent && p.name="groups" && (
				FIND_IN_SET("-1", p.value) OR FIND_IN_SET(" -1", p.value)
			)');
		}

		function checkSite()
		{
			return false;
		}

		function setupDatabase()
		{
			$this->title = §('Database Connection');
			$database = @$this->conf->database;
			@$database->plxSetupDatabase = true;
			if (!empty($_POST['plexusForm']) && isset($_POST['plxSetupDatabase'])) {
				foreach ($_POST as $name => $value) {
					$database->$name = $value;
				}
				$con = @mysql_connect($database->host, $database->user, $database->password);
				if (!$con) {
					$this->error(§('Database connection failed with your host/user/password data.'));
				} else {
					$db = mysql_select_db($database->name, $con);
					if (!$db) {
						$this->error(§('Could not select database “'.$database->name.'”.'));
					} else {
						if (!file_exists($this->host)) {
							@mkdir($this->host);
							@chmod($this->host, 0777);
						}

						$c = '<?php
	$conf->database->host = \''.$database->host.'\';
	$conf->database->user = \''.$database->user.'\';
	$conf->database->password = \''.$database->password.'\';
	$conf->database->name = \''.$database->name.'\';
	$conf->database->prefix = \''.$database->prefix.'\';
?>';
						$config = $this->host.'config.php';

						$fs = @fopen($config, 'w');
						@fwrite($fs, $c);
						@fclose($fs);
						@chmod($config, 0777);
						
						$this->conf->database = $database;
						if (!$this->checkAdmin()) {
							return $this->setupAdmin();
						} else {
							header('Location:'.$this->a->getHome());
							exit;
						}
					}
				}
			}

			return new Form(
				array('advancedOff' => TRUE,
					array(
						'type' => 'string',
						'name' => 'host',
						'required' => TRUE,
						'options' => array(
							'label' => §('Host')
						)
					),
					array(
						'type' => 'string',
						'name' => 'user',
						'required' => TRUE,
						'options' => array(
							'label' => §('User')
						)
					),
					array(
						'type' => 'string',
						'name' => 'password',
						'required' => FALSE,
						'options' => array(
							'label' => §('Password')
						)
					),
					array(
						'type' => 'string',
						'name' => 'name',
						'required' => TRUE,
						'options' => array(
							'label' => §('Database')
						)
					),
					array(
						'type' => 'string',
						'name' => 'prefix',
						'required' => FALSE,
						'options' => array(
							'label' => §('Prefix')
						)
					),
					array(
						'type' => 'hidden',
						'name' => 'plxSetupDatabase',
						'required' => TRUE
					)
				),
				$database
			);
		}

		function setupAdmin()
		{
			$this->title = §('Administration User');

			$admin = (object) array(
				'name' => 'admin',
				'email' => '',
				'password1' => '',
				'password2' => '',
				'plxSetupAdmin' => 'TRUE'
			);

			if (!empty($_POST['plexusForm']) && !empty($_POST['plxSetupAdmin'])) {
				$empty = false;
				foreach ($_POST as $name => $value) {
					$admin->$name = $value;
					if (empty($value)) {
						$empty = true;
					}
				}
				if ($empty) {
					$this->error(§('Please fill out all fields.'));
				} elseif ($admin->password1 != $admin->password2) {
					$this->error(§('Passwords did not match.'));
					$admin->password1 = '';
					$admin->password2 = '';
				} else {
					$u = $this->getData('USER');
					$u->name = $admin->name;
					$u->email = $admin->email;
					$u->password = $admin->password1;
					$u->doRedirect = false;
					$u->autoFormatAddress = true;
					$u->groups = -1;
					$u->rights = '';
					$u->plexusImport = 1;
					$u->author = 0;
					$id = $u->save();
					header('Location:'.$this->a->getHome());
					exit;
				}
			}

			return new Form(
				array('advancedOff' => TRUE,
					array(
						'type' => 'string',
						'name' => 'name',
						'required' => TRUE,
						'options' => array(
							'label' => §('Name')
						)
					),
					array(
						'type' => 'string',
						'name' => 'email',
						'required' => TRUE,
						'options' => array(
							'label' => §('Email')
						)
					),
					array(
						'type' => 'password',
						'name' => 'password1',
						'required' => TRUE,
						'options' => array(
							'label' => §('Password')
						)
					),
					array(
						'type' => 'password',
						'name' => 'password2',
						'required' => TRUE,
						'options' => array(
							'label' => §('Confirm password')
						)
					),
					array(
						'type' => 'hidden',
						'name' => 'plxSetupAdmin',
						'required' => TRUE
					)
				),
				$admin
			);
		}

		function setupSite()
		{
			$this->title = §('Site Information');
			return new Form(
				array(
					array(
						'type' => 'string',
						'name' => 'name',
						'required' => TRUE,
						'options' => array(
							'label' => §('Sitename')
						)
					)
				)
			);
		}
	}
?>
