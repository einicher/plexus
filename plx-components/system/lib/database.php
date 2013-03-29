<?php
	class Database
	{
		static $instance;

		static $queries = array();
		static $prefix;
		static $tables;

		static function getInstance($conf = '')
		{
			if (empty(self::$instance) && !empty($conf)) {
				self::$instance = new self(
					$conf->host,
					$conf->user,
					$conf->password,
					$conf->name,
					$conf->prefix
				);
			}
			return self::$instance;
		}

		function __construct($host, $user, $pass, $db, $prefix = '')
		{
			$connection = @mysql_connect($host, $user, $pass);
			$selection = @mysql_select_db($db, $connection);

            if ($connection == FALSE || $selection == FALSE) {
                exit('Core Error: Failed to connect the MySQL database.<br/><br/>If you run a single core system check <b>Storage/config.php</b>,<br/> if you run a multi core system check <b>Storage/multi/'.$_SERVER['SERVER_NAME'].'/config.php</b> <br/>for the right database configuration.');
            } else {
                mysql_query('SET NAMES \'utf8\'');
                mysql_query('SET CHARACTER SET utf8');
            }

			self::$prefix = $prefix;

			if (!$this->checkForTable($this->table('index', FALSE))) {
				mysql_query('
					CREATE TABLE '.$this->table('index').' (
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
				') OR exit(mysql_error());
			}

			if (!$this->checkForTable($this->table('properties', FALSE))) {
				mysql_query('
					CREATE TABLE '.$this->table('properties').' (
						`parent` int(11) NOT NULL,
						`name` varchar(255) NOT NULL,
						`value` text NOT NULL,
						FULLTEXT KEY `value` (`value`),
						INDEX (`parent`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				') OR exit(mysql_error());
			}

			if (!$this->checkForTable($this->table('options', FALSE))) {
				mysql_query('
					CREATE TABLE '.$this->table('options').' (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`association` varchar(255) NOT NULL,
						`name` varchar(255) NOT NULL,
						`value` text NOT NULL,
					  	PRIMARY KEY (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				') OR exit(mysql_error());
				Core::setOption('site.name', 'Name of this Site');
				Core::setOption('site.mail', 'info@example.com');
				Core::setOption('site.language', 'en');
				Core::setOption('site.theme', 'default');
				Core::setOption('content.width', '500');
				Core::setOption('content.fullsize', '900');
				Core::setOption('gallery.thumbSize', '96');
			}
		}

		function clear($sql)
		{
			unset(self::$queries[$sql]);
		}

		function query($sql, $bypass = FALSE)
		{
			if ($bypass || !isset(self::$queries[$sql])) {
				self::$queries[$sql] =& mysql_query($sql) OR trigger_error('MYSQL ERROR<br />'.mysql_error().'<br />'.$sql, E_USER_WARNING);
			}
			return self::$queries[$sql];
		}

		function fetch($sql = '', $loop = FALSE, $bypass = FALSE)
		{
			if (empty($sql)) {
				end(self::$queries);
				$sql = key(self::$queries);
				$query = self::$queries[$sql];
			} else {
				$query = self::query($sql, $bypass);
			}
			if (!$loop) { // by default we assume that the developer wants no loop and so we clean our query resource
				unset(self::$queries[$sql]);
			}
			$fetch = mysql_fetch_object($query);
			if (mysql_error()) {
				echo $sql;
			}
			return $fetch;
		}

		function fetchArray($sql = '', $loop = FALSE, $bypass = FALSE)
		{
			if (empty($sql)) {
				end(self::$queries);
				$sql = key(self::$queries);
				$query = self::$queries[$sql];
			} else {
				$query = self::query($sql, $bypass);
			}
			if (!$loop) { // by default we assume that the developer wants no loop and so we clean our query resource
				unset(self::$queries[$sql]);
			}
			return mysql_fetch_array($query);
		}

		function count($sql = '')
		{
			if (empty($sql)) {
				end(self::$queries);
				$sql = key(self::$queries);
				$query = self::$queries[$sql];
			} else {
				$query = self::query($sql);
			}
			return mysql_num_rows($query);
		}

		function lastId()
		{
			return mysql_insert_id();
		}

		function table($table, $quotes = TRUE)
		{
			if ($quotes) {
				return '`'.self::$prefix.$table.'`';
			} else {
				return self::$prefix.$table;
			}
		}

        function checkForTable($needle, $force = FALSE)
        {
            if (empty(self::$tables) || $force) {
                self::$tables = array();
                self::clear('SHOW TABLES');
                while($tables = self::fetchArray('SHOW TABLES', 1)) {
                    self::$tables[] = $tables[0];
                }
            }

            return in_array($needle, self::$tables);
        }

        function escape($text)
        {
            if (get_magic_quotes_gpc()) {
                if (ini_get('magic_quotes_sybase')) {
                    $text = str_replace("''", "'", $text);
                } else {
                	$text = str_replace('\"', '"', $text);
                	$text = str_replace("\'", "'", $text);
                }
            }
            $text = mysql_real_escape_string($text);
            return $text;
        }
	}
?>
