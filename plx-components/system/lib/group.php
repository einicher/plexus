<?php
	class Group extends PlexusDataModel
	{
		public $type = 'GROUP';
		public $status = 0;
		
		function construct()
		{
			$this->add('string', 'name', TRUE, array(
				'label' => §('Name'),
				'transformToAddress' => 1
			));
			$this->add('wysiwyg', 'description', FALSE, array(
				'label' => §('Description')
			));
			$this->add('custom', 'rights', FALSE, array(
				'actor' => Access::instance(),
				'call' => 'rightsDialog'
			));
		}

		function init()
		{
			if (!empty($this->rights)) {
				$this->rights = explode(',', $this->rights);
			} else {
				$this->rights = array();
			}
		}

		function beforeSave($data)
		{
			if (!empty($this->rights)) {
				$this->change('rights', 'text');
				$this->rights = implode(',', $this->rights);
			}
		}

		function getTitle()
		{
			return $this->name;
		}

		function getContent()
		{
			return $this->description;
		}

		function result()
		{
			return;
		}

		static function getGroups($completeGroupObject = false)
		{
			$groups = array();
			$q = Database::instance()->query('SELECT * FROM `#_index` WHERE `type`="GROUP"');
			while ($group = $q->fetch_object('Group')) {
				if ($completeGroupObject) {
					$groups[$group->id] = $group;
				} else {
					$groups[$group->id] = $group->name;
				}
			}
			return $groups;
		}

		static function getUsers($groupID, $completeUserObject = false)
		{
			$users = array();
			$q = Database::instance()->query('SELECT i.* FROM `#_properties`p, `#_index` i WHERE i.type="USER" AND i.id=p.parent AND p.name="groups" AND FIND_IN_SET('.$groupID.', p.value)');
			while ($user = $q->fetch_object('User')) {
				if ($completeUserObject) {
					$users[$user->id] = $user;
				} else {
					$users[$user->id] = $user->getName();
				}
			}
			return $users;
		}
	}
?>
