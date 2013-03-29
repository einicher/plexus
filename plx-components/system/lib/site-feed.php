<?php
	// this will replace all feed like listings within plexus as of version 0.6
	// search, witefeeds and so on ...

	class SiteFeed extends Core
	{
		function __construct($args)
		{
			foreach ($args as $n => $v) {
				$this->n = $v;
			}
		}

		function loop()
		{
		
		}
	}
?>