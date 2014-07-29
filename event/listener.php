<?php
/**
*
* Knowledge Base extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace tmbackoff\knowledgebase\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/** @var string phpEx */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth                     $auth             Auth object
	* @param \phpbb\controller\helper             $helper           Helper object
	* @param \phpbb\template\template             $template         Template object
	* @param \phpbb\user                          $user             User object
	* @param string                               $php_ext          phpEx
	*
	* @return \tmbackoff\knowledgebase\event\listener
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $php_ext)
	{
		$this->auth = $auth;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->php_ext = $php_ext;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header'						=> 'add_page_header_link',
			'core.viewonline_overwrite_location'	=> 'add_viewonline_location',
		);
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'tmbackoff/knowledgebase',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link($event)
	{
		$this->template->assign_vars(array(
			'U_KNOWLEDGE_BASE' => ($this->auth->acl_get('u_kb_read')) ? $this->helper->route('knowledgebase_main_controller', array('name' => 'index')) : '',
		));
	}

	public function add_viewonline_location($event)
	{
		if ($event['on_page'][1] == 'app')
		{
			if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/kb/index') === 0 || strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/kb/viewarticle') === 0)
			{
				$event['location'] = $this->user->lang('VIEWONLINE');
				$event['location_url'] = $this->helper->route('knowledgebase_main_controller', array('name' => 'index'));
			}
		}
	}
}
