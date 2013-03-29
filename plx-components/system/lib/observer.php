<?php
	class Observer
	{
		static $instance;
		static $observers = array();

		function getInstance()
		{
			if (empty(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		function connect($action, $call, &$actor = '', $directActor = TRUE)
		{
			self::$observers[$action][] = array(
				'actor' => &$actor,
				'method' => $call,
				'direct' => $directActor
			);
		}

		function notify($action)
		{
			$a = func_get_args();
			$action = array_shift($a);

			if (!isset(self::$observers[$action])) {
				return array_shift($a);
			}

			$args = '';
			if (!empty($a)) {
				foreach ($a as $key => $value) {
					$args .= '$a['.$key.'], ';
				}
				$args = substr($args, 0, -2);
			}

			$collect = '';
			foreach (self::$observers[$action] as $call) {
				if (is_object($call['method'])) {
					$e = $call['method'];
					$e = eval('return $e('.$args.');');
				} elseif (empty($call['actor'])) {
					$e = eval('return '.$call['method'].'('.$args.');');
				} else {
					if ($call['direct']) {
						$e = eval('return $call[\'actor\']->'.$call['method'].'('.$args.');');
					} else {
						$e = eval('return '.$call['method'].'($call[\'actor\'], '.$args.');');
					}
				}
				if (is_array($e)) {
					$collect = $e;
				} elseif (is_object($e)) {
					if (isset($e->overwrite)) {
						return $e->content;
					} elseif (isset($e->prepend)) {
						return $collect = $e->content.$collect;
					} elseif (isset($e->append)) {
						return $collect .= $e->content;
					} else {
						return $e;
					}
				} else {
					$collect .= $e;
				}
			}
			return $collect;
		}
	}
?>