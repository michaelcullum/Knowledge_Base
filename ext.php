<?php
/**
*
* Knowledge Base extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tmbackoff\knowledgebase;

/**
* Extension class for custom enable/disable/purge actions
*
* NOTE TO EXTENSION DEVELOPERS:
* Normally it is not necessary to define any functions inside the ext class below.
* The ext class may contain special (un)installation commands in the methods
* enable_step(), disable_step() and purge_step(). As it is, these methods are defined
* in phpbb_extension_base, which this class extends, but you can overwrite them to
* give special instructions for those cases. Knowledge Base must do this because it uses
* the notifications system, which requires the methods enable_notifications(),
* disable_notifications() and purge_notifications() be run to properly manage the
* notifications created by Knowledge Base when enabling, disabling or deleting this
* extension.
*/
class ext extends \phpbb\extension\base
{
	/**
	* Single enable step that installs any included migrations
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	*/
	function enable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				// Enable knowledge base notifications
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->enable_notifications('article_in_queue');
				return 'notifications';

			break;

			default:

				// Run parent enable step method
				return parent::enable_step($old_state);

			break;
		}
	}

	/**
	* Single disable step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	*/
	function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				// Disable knowledge base notifications
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->disable_notifications('article_in_queue');
				return 'notifications';

			break;

			default:

				// Run parent disable step method
				return parent::disable_step($old_state);

			break;
		}
	}

	/**
	* Single purge step that reverts any included and installed migrations
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	*/
	function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				// Purge board rules notifications
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->purge_notifications('article_in_queue');
				return 'notifications';

			break;

			default:

				// Run parent purge step method
				return parent::purge_step($old_state);

			break;
		}
	}
}
