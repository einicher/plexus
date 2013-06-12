<?php
	class Database2 extends mysqli
	{
		static $instance;

		static function instance()
		{
			$db = (object) Core::$conf->database;
			if (empty(self::$instance)) {
				self::$instance = new self($db->host, $db->user, $db->password, $db->name);
				self::$instance->set_charset('utf8');
				self::$instance->prefix = $db->prefix;
			}
			return self::$instance;
		}

		function table($name)
		{
			return $this->prefix.$name;
		}

		function escape($s)
		{
			return $this->real_escape_string($s);
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
