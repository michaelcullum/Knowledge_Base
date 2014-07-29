<?php
/**
*
* Knowledge Base extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tmbackoff\knowledgebase\acp;

class knowledgebase_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container, $request, $user;

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('tmbackoff.knowledgebase.admin.controller');

		// Requests
		$action = $request->variable('action', '');
		$category_id = $request->variable('c', 0);
		//$category_name = $request->variable('category_name', '', true);

		$category_data = $errors = array();

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		// Load the "manage" module mode
		switch ($mode)
		{
			case 'manage':
				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'knowledgebase_manage';

				// Set the page title for our ACP page
				$this->page_title = $user->lang('KNOWLEDGE_BASE_MANAGE');

				// Perform any actions submitted by the user
				switch ($action)
				{
					case 'add':
						// Load the add category handle in the admin controller
						$admin_controller->add_category();

						// Return to stop execution of this script
						return;
					break;

					case 'delete':
						// Delete a category
						$admin_controller->delete_category($category_id);
					break;

					case 'edit':
						// Load the edit category handle in the admin controller
						$admin_controller->edit_category($category_id);

						// Return to stop execution of this script
						return;
					break;

					case 'move_down':
						// Move a category down one position
						$admin_controller->move_category($category_id, 'down');
					break;

					case 'move_up':
						// Move a category up one position
						$admin_controller->move_category($category_id, 'up');
					break;
				}

				$admin_controller->display_categories();
			break;
		}
	}
}