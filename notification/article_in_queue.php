<?php
/**
*
* Knowledge Base extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tmbackoff\knowledgebase\notification;

/**
* Article in queue notifications class
* This class handles notifications for articles when they are put in the moderation queue (for moderators)
*/

class article_in_queue extends \phpbb\notification\type\base
{
	/** @var \phpbb\controller\helper */
	protected $helper;

	/**
	* Notification Type Knowledgebase Constructor
	*
	* @param \phpbb\auth\auth                     $auth             Auth object
	* @param \phpbb\db\driver\driver_interface    $db               Database object
	* @param \phpbb\controller\helper             $helper           Helper object
	* @param \phpbb\user                          $user             User object
	* @param string                               $root_path        phpBB root path
	* @param string                               $php_ext          phpEx
	* @param string $notification_types_table
	* @param string $notifications_table
	* @param string $user_notifications_table
	*
	* @return \phpbb\notification\type\base
	*/
	public function __construct(\phpbb\user_loader $user_loader, \phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, $user, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\controller\helper $helper, $phpbb_root_path, $php_ext, $notification_types_table, $notifications_table, $user_notifications_table)
	{
		$this->user_loader = $user_loader;
		$this->db = $db;
		$this->cache = $cache;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->notification_types_table = $notification_types_table;
		$this->notifications_table = $notifications_table;
		$this->user_notifications_table = $user_notifications_table;
	}

	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'article_in_queue';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_ARTICLE_IN_QUEUE';

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'id'	=> 'needs_approval',
		'lang'	=> 'NOTIFICATION_TYPE_IN_MODERATION_QUEUE',
		'group'	=> 'NOTIFICATION_GROUP_MODERATION',
	);

	/**
	* Get the id of the article
	*
	* @param array $notification_data The data for the updated articles
	*/
	public static function get_item_id($notification_data)
	{
		return (int) $notification_data['article_id'];
	}

	/**
	* Get the id of the parent
	*
	* @param array $notification_data The data for the updated articles
	*/
	public static function get_item_parent_id($notification_data)
	{
		// No parent
		return 0;
	}

	/**
	* Find the users who will receive notifications
	*
	* @param array $notification_data The type specific data for the updated articles
	* @param array $options Options for finding users for notification
	*
	* @return array
	*/
	public function find_users_for_notification($notification_data, $options = array())
	{
		// Grab all registered users (excluding bots and guests)
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE user_type <> ' . USER_IGNORE;
		$result = $this->db->sql_query($sql);

		$allowed = $this->auth->acl_get_list(false, array('m_kb_approve', 'm_kb_deny'), false);
		$allowed = array_intersect($allowed[0]['m_kb_approve'], $allowed[0]['m_kb_deny']);

		$users = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (in_array($row['user_id'], $allowed))
			{
				$users[$row['user_id']] = array('');
			}
		}
		$this->db->sql_freeresult($result);

		return $users;
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array();
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		return $this->user->lang(
            $this->language_key,
            $this->get_data('article_title')
        );
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return $this->helper->route('knowledgebase_main_controller', array('name' => 'viewarticle', 'a' => $this->item_id));
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return false;
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		return array();
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $notification_data The data for the updated articles
	* @param array $pre_create_data Data from pre_create_insert_array()
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($notification_data, $pre_create_data = array())
	{
		$this->set_data('article_id', $notification_data['article_id']);
		$this->set_data('article_title', $notification_data['article_title']);

		$notification_data = parent::create_insert_array($notification_data, $pre_create_data);

		$this->notification_time = $notification_data['notification_time'] = time();

		return $notification_data;
	}
}
