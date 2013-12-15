<?php
	class Component extends Core
	{
		public $name = 'COMPONENT';
		public $description = 'COMPONENT_DESCRIPTION';
		public $version = 0.1;
		public $link = 'COMPONENT_LINK';

		public $author = 'AUTHOR_NAME';
		public $authorMail = 'AUTHOR_EMAIL';
		public $authorLink = 'AUTHOR_LINK';

		public $dependencies = array();
		public $minVersion = 0;

		final function __construct($quiet = FALSE)
		{
			if (!$quiet) {
				$this->construct();
			}
		}

		function construct()
		{
		}

		function addMenu($label, $call, &$actor = '', $indicator = 0)
		{
			$address = strtolower($this->a->transform($label));
			$this->a->assign('system.preferences.'.$address, $address, array(&$actor, $call), 'system.preferences');
			Site::$components[] = (object) array(
				'label' => $label,
				'link' => $this->a->assigned('system.preferences').'/'.$this->a->transform($label),
				'popup' => true,
				'actor' => &$actor,
				'call' => $call,
				'address' => $address,
				'indicator' => $indicator
			);
		}
	}
?>
