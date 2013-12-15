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
		return Language::instance()->get($text);
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

	class FsockopenMultipartFormDataClass
	{
		protected $values = array();
		protected $files = array();

		function addValue($name, $value)
		{
			$this->values[$name] = $value;
		}

		function removeValue($name)
		{
			unset($this->values[$name]);
		}

		function addFile($name, $file)
		{
			$this->files[$name] = $file;
		}

		function removeFile($name)
		{
			unset($this->files[$name]);
		}

		function send($host, $path, $secure = false)
		{
			$boundary = sha1(1);
			$crlf = "\r\n";
			$data = '';

			if (!empty($this->values)) {
				foreach ($this->values as $name => $value) {
					$data .= '--'.$boundary.$crlf
							.'Content-Disposition: form-data; name="'.$name.'"'.$crlf
							.'Content-Length: '.strlen($value).$crlf.$crlf
							.$value.$crlf;
				}
			}

			if (!empty($this->files)) {
				foreach ($this->files as $name => $file) {
					$finfo = new \finfo(FILEINFO_MIME);
					$mimetype = $finfo->file($file);

					$file_contents = file_get_contents($file);

					$data .= '--'.$boundary.$crlf
							.'Content-Disposition: form-data; name="'.$name.'"; filename="'.basename($file).'"'.$crlf
							.'Content-Type: '.$mimetype.$crlf
							.'Content-Length: '.strlen($file_contents).$crlf
							.'Content-Type: application/octet-stream'.$crlf.$crlf
							.$file_contents.$crlf;
				}
			}

			$data .= '--'.$boundary.'--';
//echo µ($data);
			$response = '';
			if ($secure) {
				$fp = fsockopen('ssl://'.$host, 443, $errno, $errstr, 30);
			} else {
				$fp = fsockopen($host, 80, $errno, $errstr, 20);
			}
			if ($fp) {
				$write = 'POST '.$path.' HTTP/1.1'.$crlf
						.'Host: '.$host.$crlf
						.'Content-type: multipart/form-data; boundary='.$boundary.$crlf
						.'Content-Length: '.strlen($data).$crlf
						.'Connection: Close'.$crlf.$crlf
						.$data;

				fwrite($fp, $write);
				while ($line = fgets($fp)) {
					if ($line !== false) {
						$response .= $line;
					}
				}
				fclose($fp);
				$response = explode($crlf.$crlf, $response);
				unset($response[0]);
				return implode($crlf.$crlf, $response);
			} else {
				return $errstr;
			}
		}
	}

	if (!function_exists('get_called_class')) { // < 5.3.0
		function get_called_class()
		{
			$t = debug_backtrace();
			$t = $t[0];
			if (isset($t['object']) && $t['object'] instanceof $t['class']) {
				return get_class($t['object']);
			}
			return false;
		}
	}  
?>
