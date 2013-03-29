<?php
	class Group extends PlexusDataModel
	{
		public $type = 'GROUP';
		public $status = 0;
		
		function construct()
		{
			$this->add('string', 'name', TRUE, array(
				'label' => $this->lang->get('Name'),
				'transformToAddress' => 1
			));
			$this->add('wysiwyg', 'description', FALSE, array(
				'label' => $this->lang->get('Description')
			));
			$this->add('custom', 'rights', FALSE, array(
				'actor' => Access::getInstance(),
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

		function beforeSave()
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
			while ($fetch = Database::fetch('SELECT * FROM '.Database::table('index').' WHERE `type`="GROUP"')) {
				$group = new Group($fetch);
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
			while ($fetch = Database::fetch('SELECT i.* FROM '.Database::table('textual').' t, '.Database::table('index').' i WHERE i.type="USER" AND i.id=t.parent AND t.name="groups" AND FIND_IN_SET('.$groupID.', t.value)')) {
				$user = new User($fetch);
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