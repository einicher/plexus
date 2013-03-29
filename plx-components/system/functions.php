<?php
	function __autoload($class)
	{
		$filename = classNameToFileName($class);
		if (!on_exist_require(PLX_SYSTEM.'lib/'.strtolower($filename).'.php')) {
			$dbg = debug_backtrace();
			if (isset($dbg[0]['file'])) {
				preg_match('='.PLX_COMPONENTS.'([^/]*)/=', $dbg[0]['file'], $results);
				if (!empty($results[1])) {
					on_exist_require(PLX_COMPONENTS.$results[1].'/lib/'.strtolower($class).'.php');
				}
			}
		}
	}

	function classNameToFileName($class)
	{
		$strSplit = str_split($class);
		$filename = '';
		foreach ($strSplit as $key => $char) {
			if (!is_numeric($char) && $char == strtoupper($char) && $key > 0) {
				$filename .= '-'.$char;
			} else {
				$filename .= $char;
			}
		}
		return strtolower($filename);
	}

	function fileNameToClassName($file)
	{
		return str_repalce(' ', '', ucwords(str_replace('-', ' ', $file)));
	}

	function on_exist_require($file)
	{
		if (file_exists($file)) {
			require_once $file;
			return TRUE;
		}
		return FALSE;
	}

	function whatIsInsideOf($var)
	{
		ob_start();
		if (is_bool($var)) {
			echo '<pre>:';
			var_dump($var);
			echo ':</pre>';
		} else {
			echo '<pre>:';
			print_r($var);
			echo ':</pre>';
		}
		echo "\n";
		return ob_get_clean();
	}

	function whatsIn($var)
	{
		return whatIsInsideOf($var);
	}

	function µ($var)
	{
		return whatIsInsideOf($var);
	}

	function §($text)
	{
		global $file;
		return Language::getInstance()->get($text);
	}

	function implodeKeys($glue, $array)
	{
		$i = 0;
		$string = '';
		$count = count($array);
		foreach ($array as $key => $value) {
			$i++;
			$string .= $key;
			if ($i < $count) {
				$string .= $glue;
			}
		}
		return $string;
	}

	function π()
	{
		return pi();
	}
?>