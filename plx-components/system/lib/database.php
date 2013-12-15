<?php
	class Database extends mysqli
	{
		static $instance;
		static $tables;

		public $prefix;

		static public function &instance($db = '')
		{
			if (empty(self::$instance)) {
				self::$instance = new self($db->host, $db->user, $db->password, $db->name);
				self::$instance->set_charset('utf8');
				self::$instance->prefix = $db->prefix;

				if (!self::$instance->checkForTable(self::$instance->table('index'))) {
					self::$instance->query('
						CREATE TABLE `#_index` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `parent` int(11) NOT NULL,
						  `address` varchar(255) NOT NULL,
						  `type` varchar(255) NOT NULL,
						  `status` int(11) NOT NULL,
						  `author` int(11) NOT NULL,
						  `published` int(11) NOT NULL,
						  `language` varchar(255) NOT NULL,
						  PRIMARY KEY (`id`),
						  INDEX (`parent`,`address`,`status`,`published`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;
					');
				}

				if (!self::$instance->checkForTable(self::$instance->table('properties'))) {
					self::$instance->query('
						CREATE TABLE `#_properties` (
							`parent` int(11) NOT NULL,
							`name` varchar(255) NOT NULL,
							`value` text NOT NULL,
							FULLTEXT KEY `value` (`value`),
							INDEX (`parent`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;
					');
					Core::setOption('gallery.thumbSize', '96');
				}

				$check = Core::getOption('plexus.Upgrade-0.6-numeric');
				if (self::$instance->checkForTable(self::$instance->table('numeric')) && empty($check)) {
					self::$instance->query('INSERT INTO `#_properties` SELECT * FROM `#_numeric`');
					Core::setOption('plexus.Upgrade-0.6-numeric', 1);
					Core::info('Your '.self::$instance->table('numeric').' table was merged into '.self::$instance->table('properties').', its no longer required and should be deleted.');
				}

				$check = Core::getOption('plexus.Upgrade-0.6-textual');
				if (self::$instance->checkForTable(self::$instance->table('textual')) && empty($check)) {
					self::$instance->query('INSERT INTO '.self::$instance->table('properties').' SELECT * FROM '.self::$instance->table('textual').'');
					self::$instance->query('ALTER TABLE '.self::$instance->table('properties').' ORDER BY `parent` ');
					Core::setOption('plexus.Upgrade-0.6-textual', 1);
					Core::info('Your '.self::$instance->table('textual').' table was merged into '.self::$instance->table('properties').', its no longer required and should be deleted.');
				}

				if (!self::$instance->checkForTable(self::$instance->table('options'))) {
					self::$instance->query('
						CREATE TABLE `#_options` (
							`id` int(11) NOT NULL AUTO_INCREMENT,
							`association` varchar(255) NOT NULL,
							`name` varchar(255) NOT NULL,
							`value` text NOT NULL,
						  	PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;
					');
					Core::setOption('site.name', 'Name of this Site');
					Core::setOption('site.mail', 'info@example.com');
					Core::setOption('site.language', 'en');
					Core::setOption('site.theme', 'default');
					Core::setOption('content.width', '500');
					Core::setOption('content.fullsize', '900');
					Core::setOption('gallery.thumbSize', '96');
				}
			}
			return self::$instance;
		}

        function checkForTable($needle, $force = FALSE)
        {
            if (empty(self::$tables) || $force) {
                self::$tables = array();
                $q = self::$instance->query('SHOW TABLES');
                while($tables = $q->fetch_array()) {
                    self::$tables[] = $tables[0];
                }
            }

            return in_array($needle, self::$tables);
        }

		static public function table($name)
		{
			return self::$instance->prefix.$name;
		}

		static public function escape($s)
		{
			return self::$instance->real_escape_string($s);
		}

		function prepare($sql)
		{
			$sql = str_replace('#_', $this->prefix, $sql);
			return parent::prepare($sql);
		}

		function query($sql)
		{
			$sql = str_replace('#_', $this->prefix, $sql);
			return parent::query($sql);
		}

		function count($sql = '')
		{
			if (!empty($sql)) {
				$this->query($sql);
			}
			return $this->affected_rows;
		}

		function getPrepared()
		{
			$args = func_get_args();
			return call_user_func_array(array(&$this, 'preparedStatement'), $args);
		}

		/**
		 * $sql, $types, [$args ..]
		 */
		function preparedStatement()
		{
			$param = array();
			$args = func_get_args();
			$sql = array_shift($args);
			$stmt = $this->prepare($sql) OR exit($this->error.'<br />'.$sql);
			$param[] = array_shift($args);
			foreach ($args as $key => $arg) {
				$param[] =& $args[$key]; //bind_param needs references
			}
			call_user_func_array(array($stmt, 'bind_param'), $param) OR exit($this->error);
			$stmt->execute() OR exit($this->error);
			$stmt->store_result();

			$customLimit = 0;
			if (isset($this->execute_callback)) {
				$return = false;
				$callback = $this->execute_callback;
				unset($this->execute_callback);
				$stmt = $callback($stmt, $this, $return, $customLimit);
				if ($return) {
					return $stmt;
				}
			}

			if ($stmt->num_rows > 0) {
				if (method_exists($stmt, 'get_result')) { // works only with mysqlnd driver installed
					$result = $stmt->get_result();
					$results = array();
					$i = 0;
					while ($f = $result->fetch_object()) {
						$i++;
						$results[] = $f;
						if ($customLimit && $i == $customLimit) {
							break;
						}
					}
				} else {
					$result = array();
					$f = new stdClass;

					$meta = $stmt->result_metadata();
					while ($field = $meta->fetch_field()) {
						$result[] =& $f->{$field->name};
					}
					call_user_func_array(array($stmt, 'bind_result'), $result);

					$i = 0;
					$results = array();
					while ($stmt->fetch()) {
						$i++;
						// php sucks!!
						// $results[] = $f; is suddenly taking over all references within $f so $results values are all referenced to $f
						// workaround is to create a new object an reassign $f values
						$noRefs = new stdClass;
						foreach ($f as $k => $v) {
							$noRefs->$k = $v;
						}
						$results[] = $noRefs;
						unset($noRefs);
						if ($customLimit && $i == $customLimit) {
							break;
						}
					}
				}

				$stmt->close();

				if (count($results) == 1) {
					return array_pop($results);
				} else {
					return $results;
				}
			}
		}

		/**
		 * $args['force_array']: always returns array, also if there is only one result, or none
		 * $args['class']: fetches the data into that class
		 */
		function get($sql, $args = array())
		{
			$r = $this->query($sql);
			if ($r && $r->num_rows) {
				if ($r->num_rows == 1) {
					if (empty($args['class'])) {
						$f = $r->fetch_object();
					} else {
						$f = $r->fetch_object($args['class'], array('PLEXUS_DATABASE2_LOOP'));
					}

					if (isset($args['force_array'])) {
						return array($f);
					} else {
						return $f;
					}
				} else {
					$results = array();
					if (empty($args['class'])) {
						while ($f = $r->fetch_object()) {
							$results[] = $f;
						}
					} else {
						while ($f = $r->fetch_object($args['class'], array('PLEXUS_DATABASE2_LOOP'))) {
							$results[] = $f;
						}
					}
					return $results;
				}
			}
			if (isset($args['force_array'])) {
				return array();
			}
		}
	}
?>
