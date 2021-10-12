<?php

/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2020 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbservices\smartfeed\controller;

use phpbbservices\smartfeed\constants\constants;

class feed
{
	protected $auth;
	protected $common;
	protected $config;
	protected $db;
	protected $ext_manager;
	protected $helper;
	protected $language;
	protected $template;
	protected $phpbb_log;
	protected $phpbb_notifications;
	protected $phpbb_root_path; // Only used in functions.
	protected $phpEx;
	protected $request;
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth						$auth
	* @param \phpbbservices\smartfeed\core\common	$common
	* @param \phpbb\config\config					$config
	* @param \phpbb\db\driver\factory				$db
	* @param \phpbb\extension\manager				$ext_manager
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\language\language 				$language
	* @param \phpbb\log\log							$phpbb_log
	* @param string									$phpbb_root_path
	* @param string									$php_ext
	* @param \phpbb\request\request 				$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\notification\manager 			$notification_manager
	*/
	
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user,
		$php_ext, \phpbb\db\driver\factory $db, \phpbb\auth\auth $auth, $phpbb_root_path, \phpbb\request\request $request, \phpbb\log\log $phpbb_log,
		\phpbbservices\smartfeed\core\common $common, \phpbb\language\language $language, \phpbb\notification\manager $notification_manager,
		\phpbb\extension\manager $ext_manager)
	{

		// External classes and variables injected into the class
		$this->auth = $auth;
		$this->common = $common;
		$this->config = $config;
		$this->db = $db;
		$this->ext_manager = $ext_manager;
		$this->helper = $helper;
		$this->language = $language;
		$this->phpEx = $php_ext;
		$this->phpbb_log = $phpbb_log;
		$this->phpbb_notifications = $notification_manager;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;

		// Other useful class variables. The values assigned to these largely come from the URI submitted when the URI is checked for errors and are used across multiple functions.
		$this->board_url = NULL;
		$this->bookmarks_only = NULL;
		$this->date_limit = NULL;
		$this->encrypted_pswd = NULL;
		$this->errors = array();
		$this->feed_style = NULL;
		$this->feed_type = NULL;
		$this->filter_foes = NULL;
		$this->first_post_only = NULL;
		$this->is_registered = false;
		$this->items_in_feed = 0;
		$this->lastvisit = NULL;
		$this->last_post_only = NULL;
		$this->mark_private_messages = NULL;
		$this->max_items = NULL;
		$this->max_words = NULL;
		$this->min_words = NULL;
		$this->public_only = false;
		$this->remove_my_posts = NULL;
		$this->show_topic_titles = NULL;
		$this->show_pms = NULL;
		$this->sort_by = NULL;
		$this->suppress_forum_names = NULL;
		$this->suppress_usernames = NULL;
		$this->time_limit = NULL;
		$this->user_id = ANONYMOUS;	// Assume guest
		$this->user_lastvisit = NULL;
		$this->user_topic_sortby_type = NULL;
		$this->user_topic_sortby_dir = NULL;
		$this->user_post_sortby_type = NULL;
		$this->user_post_sortby_dir = NULL;

	}
	
	private function check_for_errors ()
	{
		
		// This function checks for logical errors in the URL syntax. This is to ensure the URL cannot be hacked successfully.

		$true_false_array = array(0,1);

		// What is the feed type (ATOM 1.0, RSS 1.0 or RSS 2.0?)
		$this->feed_type = $this->request->variable(constants::SMARTFEED_FEED_TYPE, constants::SMARTFEED_ATOM);
		if (!($this->feed_type == constants::SMARTFEED_ATOM || $this->feed_type == constants::SMARTFEED_RSS1 || $this->feed_type == constants::SMARTFEED_RSS2))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_FEED_TYPE_ERROR', $this->feed_type);
		}

		// Determine if this is a public request. If so only posts in public forums will be shown in the feed.
		if ($this->user_id !== ANONYMOUS && $this->encrypted_pswd !== constants::SMARTFEED_NONE)
		{
			// If Apache authentication is used by the board, make sure the admin has asserted that they have made changes to
			// the .htaccess file to allow this program to not be blocked. They do this by changing the .htaccess file
			// and enabling a configuration variable.
			if (($this->config['auth_method'] == 'apache') && ($this->config['phpbbservices_smartfeed_apache_htaccess_enabled'] == 0))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_APACHE_AUTHENTICATION_WARNING_REG');
			}
			$this->is_registered = true;
		}
		else if (!(($this->user_id == ANONYMOUS) && ($this->encrypted_pswd == constants::SMARTFEED_NONE)))
		{
			// Logically if only the u or the e parameter is present, the URL is inconsistent, so generate an error.
			if ($this->user_id == ANONYMOUS)
			{
				$this->errors[] = $this->language->lang('SMARTFEED_NO_U_ARGUMENT');
			}
			if ($this->encrypted_pswd == constants::SMARTFEED_NONE)
			{
				$this->errors[] = $this->language->lang('SMARTFEED_NO_E_ARGUMENT');
			}
		}

		// Check the limit parameter. It limits the size of the newsfeed to a point in time from the present, either a
		// day/hour/minute interval, no limit or the time since the user's last visit.
		$time_limit_default = ($this->is_registered) ? constants::SMARTFEED_SINCE_LAST_VISIT_VALUE : constants::SMARTFEED_NO_LIMIT_VALUE;
		$this->time_limit = $this->request->variable(constants::SMARTFEED_TIME_LIMIT, $time_limit_default);
		if (!is_numeric($this->time_limit))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_LIMIT_FORMAT_ERROR');
		}
		// The time limit parameter must be an integer between $time_limit_default and 13
		else if ($this->is_registered && (((int) ($this->time_limit) < (int) ($time_limit_default)) || ((int) ($this->time_limit) > (int) (constants::SMARTFEED_LAST_15_MINUTES_VALUE))) )
		{
			$this->errors[] = $this->language->lang('SMARTFEED_LIMIT_FORMAT_ERROR');
		}

		// Validate the sort by parameter (integers 0 through 4). If not present or an incorrect value, use the board default sort.
		$this->sort_by = $this->request->variable(constants::SMARTFEED_SORT_BY, constants::SMARTFEED_STANDARD);
		if ((int) $this->sort_by < constants::SMARTFEED_BOARD || (int) $this->sort_by > constants::SMARTFEED_POSTDATE_DESC)
		{
			$this->errors[] = $this->language->lang('SMARTFEED_SORT_BY_ERROR');
		}

		// Validate the firstpostonly parameter (0 or 1 expected). If not present or an incorrect value, disable it.
		$this->first_post_only = $this->request->variable(constants::SMARTFEED_FIRST_POST, 0);
		if (!in_array((float) $this->first_post_only, $true_false_array))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_FIRST_POST_ONLY_ERROR');
		}

		// Validate the lastpostonly parameter (0 or 1 expected). If not present or an incorrect value, disable it.
		$this->last_post_only = $this->request->variable(constants::SMARTFEED_LAST_POST, 0);
		if (!in_array((float) $this->last_post_only, $true_false_array))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_LAST_POST_ONLY_ERROR');
		}

		// Check for max items parameter: the maximum number of feed items wanted. It is not required, but if present
		// should be a positive whole number only. The value must be less than or equal to $this->config['phpbbservices_smartfeed_max_items'].
		// But if $this->config['phpbbservices_smartfeed_max_items'] == 0 then any positive whole number is allowed.
		// If not present, the max items is $this->config['phpbbservices_smartfeed_max_items'] is used if positive, or unlimited if this value is zero.
		$this->max_items = $this->request->variable(constants::SMARTFEED_MAX_ITEMS, 0);
		if ((int) $this->max_items < 0)
		{
			$this->errors[] = $this->language->lang('SMARTFEED_MAX_ITEMS_ERROR');
		}

		// Validate the maximum number of words the user wants to see in a post
		$this->max_words = $this->request->variable(constants::SMARTFEED_MAX_WORDS, 0);
		if ($this->max_words < 0)
		{
			$this->errors[] = $this->language->lang('SMARTFEED_MAX_WORD_SIZE_ERROR');
		}

		// Validate the minimum number of words the user wants to see in a post
		$this->min_words = $this->request->variable(constants::SMARTFEED_MIN_WORDS, 0);	// 0 = No minimum
		if ($this->min_words < 0)
		{
			$this->errors[] = $this->language->lang('SMARTFEED_MIN_WORD_SIZE_ERROR');
		}

		// Validate the feed style parameter: HTML, Safe HTML, Basic or Compact
		$this->feed_style = $this->request->variable(constants::SMARTFEED_FEED_STYLE, constants::SMARTFEED_HTML);
		if (!($this->feed_style == constants::SMARTFEED_COMPACT || $this->feed_style == constants::SMARTFEED_BASIC || $this->feed_style == constants::SMARTFEED_HTMLSAFE || $this->feed_style == constants::SMARTFEED_HTML))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_STYLE_ERROR', $this->feed_style);
		}

		// Validate the suppress forum names parameter.
		$this->suppress_forum_names = $this->request->variable(constants::SMARTFEED_SUPPRESS_FORUM_NAMES, 0);
		if (!in_array($this->suppress_forum_names, $true_false_array))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_SUPPRESS_FORUM_NAMES_ERROR');
		}

		// Validate the suppress topic titles only parameter.
		$this->show_topic_titles = $this->request->variable(constants::SMARTFEED_TOPIC_TITLES, 0);
		if (!in_array($this->show_topic_titles, $true_false_array))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_SHOW_TOPIC_TITLES_ERROR');
		}

		// Validate the suppress usernames parameter.
		$this->suppress_usernames = $this->request->variable(constants::SMARTFEED_USERNAMES, 0);
		if (!in_array($this->suppress_usernames, $true_false_array))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_SUPPRESS_USERNAMES_ERROR');
		}

		// Special error checking logic is needed for registered users only
		if ($this->is_registered)
		{

			// If openssl is not compiled with PHP, a user cannot get a feed with posts from non-public forums, so tell the user this.
			// This should not be a problem because this extension cannot be installed if openssl is not compiled with PHP, but it's always possible the
			// PHP extension could be removed after this extension is installed.
			if (!extension_loaded('openssl'))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_NO_OPENSSL_MODULE');
			}

			//  Validate the remove my posts parameter, if present
			$this->remove_my_posts = $this->request->variable(constants::SMARTFEED_REMOVE_MINE, 0);
			if (!in_array($this->remove_my_posts, $true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_REMOVE_MINE_ERROR');
			}

			// Validate the private messages switch
			$this->show_pms = $this->request->variable(constants::SMARTFEED_PRIVATE_MESSAGE, 0);
			if (!in_array($this->show_pms, $true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_BAD_PMS_VALUE');
			}

			// Validate the mark read private messages switch
			$this->mark_private_messages = $this->request->variable(constants::SMARTFEED_MARK_PRIVATE_MESSAGES, 0);
			if (!in_array($this->mark_private_messages, $true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_BAD_MARK_PRIVATE_MESSAGES_READ_ERROR');
			}
			
			// Validate the bookmark topics only switch
			$this->bookmarks_only = $this->request->variable(constants::SMARTFEED_BOOKMARKS, 0);
			if (!in_array($this->bookmarks_only, $true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_BAD_BOOKMARKS_VALUE');
			}
			
			// Validate the filter foes switch
			$this->filter_foes = $this->request->variable(constants::SMARTFEED_FILTER_FOES, 0);
			if (!in_array($this->filter_foes, $true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_FILTER_FOES_ERROR');
			}

			// Validate the last visit parameter.
			$this->lastvisit = $this->request->variable(constants::SMARTFEED_SINCE_LAST_VISIT, 0);
			if (!in_array($this->lastvisit, $true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_LASTVISIT_ERROR');
			}

		}

		if (count($this->errors) > 0)
		{
			throw new \Exception(implode("\n", $this->errors));
		}

	}
	
	/**
	* Smartfeed controller for route /smartfeed/{name}
	*
	* returns an object containing rendered feed in Atom or RSS syntax
	*/
	public function handle()
	{

		$error = false;

		// Load language variables used specifically in this class
		$this->language->add_lang(array('feed'), 'phpbbservices/smartfeed');

		// General variables
		$this->board_url = generate_board_url() . '/';
		$continue = true;

		// Get the user id. The feed may be customized based on being a registered user. A public user won't be identified as a user in the URL.
		if ((bool) $this->config['phpbbservices_smartfeed_public_only'])
		{
			// In this mode the u and e parameters if present are ignored and only public forums can be used in a feed.
			$this->user_id = ANONYMOUS;
			$this->encrypted_pswd = constants::SMARTFEED_NONE;
		}
		else
		{
			$this->user_id = $this->request->variable(constants::SMARTFEED_USER_ID, ANONYMOUS);
			$this->encrypted_pswd = $this->request->variable(constants::SMARTFEED_ENCRYPTION_KEY, constants::SMARTFEED_NONE, true);
		}

		// Check for incorrect or invalid URL key/value pairs. We want to ensure unwanted behavior cannot occur based on hacking the URL.
		// Normally, ui.php creates a valid URL that is used.
		try
		{
			$this->check_for_errors();
		} 
		catch (\Exception $e)
		{
			$error = true;
		}

		// If board is disabled, disable feeds as well.
		if ($this->config['board_disable'])
		{
			$error = true;
			$continue = false;
			$this->errors[] = $this->language->lang('SMARTFEED_BOARD_DISABLED');
		}

		// The while loop construct allows a more elegant way of handling errors by breaking out of the loop if an error occurs.
		while ($continue)
		{

			// Function returns whether the user has been validated or not
			if (!$this->validate_user($this->user_id))
			{
				$error = true;
				break;
			}

			if (isset($this->errors) && count($this->errors) == 0)
			{
				// Limit the maximum number of items in the feed to the value set by the admin, if set.
				if (($this->config['phpbbservices_smartfeed_max_items'] > 0) && ($this->max_items <> 0))
				{
					$this->max_items = min($this->max_items, $this->config['phpbbservices_smartfeed_max_items']);
				}
				else if (($this->config['phpbbservices_smartfeed_max_items'] > 0) && ($this->max_items == 0))
				{
					$this->max_items = $this->config['phpbbservices_smartfeed_max_items'];
				}

				// Limit the maximum number of words in a feed item to the value set by the admin, if set.
				if (($this->config['phpbbservices_smartfeed_max_word_size'] > 0) && ($this->max_words <> 0))
				{
					$this->max_words = min($this->max_words, $this->config['phpbbservices_smartfeed_max_word_size']);
				}
				else if (($this->config['phpbbservices_smartfeed_max_word_size'] > 0) && ($this->max_words == 0))
				{
					$this->max_words = $this->config['phpbbservices_smartfeed_max_word_size'];
				}
			}

			// Function returns some SQL that limits the time range of posts retrieved for the feed.
			$date_limit_sql = $this->set_date_limit($this->time_limit, true);	// Returns SQL fragment
			$this->date_limit = (int) $this->set_date_limit($this->time_limit, false);	// Returns timestamp

			// We need to get a list of forum_ids that we will retrieve from. The forum_ids to be fetched
			// depend on whether bookmarked topics only are being requested. The result is a snippet of SQL used
			// in the ultimate query.
			$fetched_forums_str = ($this->is_registered && $this->bookmarks_only) ? $this->get_bookmarked_forums() : $this->get_fetched_forums();
			if (!$fetched_forums_str)
			{
				// An unexpected condition or error occurred, so break out of the loop to report it in the feed.
				$error = true;
				break;
			}

			// Call function to create the SQL ORDER BY statement
			$order_by_sql = $this->get_sort_sql();

			$new_topics_sql = '';
			$topics_posts_join_sql = 't.topic_id = p.topic_id';

			// Create the first post only SQL stubs
			if ($this->first_post_only)
			{
				$new_topics_sql = " AND t.topic_time > $this->date_limit ";
				$topics_posts_join_sql = ' t.topic_first_post_id = p.post_id AND t.forum_id = f.forum_id';
			}

			// Create the last post only SQL stubs
			if ($this->last_post_only)
			{
				$topics_posts_join_sql = ' t.topic_last_post_id = p.post_id AND t.forum_id = f.forum_id';
			}

			// Create SQL stub to remove your posts from the feed
			$remove_my_posts_sql = '';
			if ($this->is_registered && ($this->remove_my_posts == 1))
			{
				$remove_my_posts_sql = " AND p.poster_id <> $this->user_id";
			}

			// Create SQL to remove your foes from the feed
			$filter_foes_sql = $this->get_foes_sql();

			// If this is a topic feed, constrain SQL to return only the topic requested.
			$topic_id = $this->request->variable('tf',0);
			$topic_only_sql = ($topic_id !== 0) ? ' AND t.topic_id = ' . $topic_id : '';

			// At last, construct the SQL to return the relevant posts in a feed
			$sql_ary = array(
				'SELECT'	=> 'f.*, t.*, p.*, u.*',

				'FROM'		=> array(
					FORUMS_TABLE => 'f',
					TOPICS_TABLE => 't',
					POSTS_TABLE => 'p',
					USERS_TABLE => 'u'),

				'WHERE'		=> "f.forum_id = t.forum_id AND 
						$topics_posts_join_sql AND 
						p.poster_id = u.user_id 
						$date_limit_sql
						$fetched_forums_str
						$new_topics_sql
						$remove_my_posts_sql
						$filter_foes_sql
						$topic_only_sql
						AND p.post_visibility = " . ITEM_APPROVED . " 
						AND forum_password = ''
						AND topic_status <> " . ITEM_MOVED,

				'ORDER_BY'	=> $order_by_sql
			);

			$sql = $this->db->sql_build_query('SELECT', $sql_ary);

			// Now finally, let's fetch the actual posts to be placed in this newsfeed
			$result = $this->db->sql_query_limit($sql, $this->max_items); // Execute the SQL to retrieve the relevant posts. Note, if $this->max_items is 0 then there is no limit on the rows returned
			$rowset = $this->db->sql_fetchrowset($result); // Get all the posts as a set
			$this->db->sql_freeresult($result);

			// Add private messages, if requested
			if ($this->is_registered && $this->show_pms)
			{
				$pm_sql_ary = array(
					'SELECT'    => '*',

					'FROM'      => array(
						PRIVMSGS_TO_TABLE => 'pt',
						PRIVMSGS_TABLE	=> 'pm',
						USERS_TABLE => 'u',
					),

					'WHERE'     =>  "pt.msg_id = pm.msg_id
					AND pt.author_id = u.user_id
					AND pt.user_id = $this->user_id
					AND (pm_unread = 1 OR pm_new = 1)",
				);

				$pm_sql = $this->db->sql_build_query('SELECT', $pm_sql_ary);
				$pm_result = $this->db->sql_query($pm_sql);
				$pm_rowset = $this->db->sql_fetchrowset($pm_result);
				$this->db->sql_freeresult($pm_result);
			}
			else
			{
				$pm_result = NULL;
				$pm_rowset = NULL;
			}

			break;	// Force exit from loop, no more errors to check for
		}

		$display_name = $this->language->lang('SMARTFEED_FEED');	// As XML is generated to create a feed, there is no real page name to display so this is sort of moot.

		// Show the posts as feed items
		$this->assemble_feed($rowset,$pm_rowset, $error);

		// Reset the user's last visit date on the forum, if so requested
		if (!$error && $this->is_registered && isset($this->lastvisit))
		{
			if ($this->lastvisit)
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
							SET user_lastvisit = ' . time() . ' 
							WHERE user_id = ' . (int) $this->user_id;
					
				$this->db->sql_query($sql);
			}
		}
				
		return $this->helper->render('@phpbbservices_smartfeed/feed.xml', $display_name);
	
	}

	private function assemble_feed(&$rowset, &$pm_rowset, $error)

	{

		// This function creates the overall feed. The rows of posts and private messages are already fetched.
		//
		// $rowset = array of posts wanted in the feed
		// $pm_rowset = array of private messages wanated in the feed
		// $error = if true, report a logical feed error, otherwise present a normal feed

		// Get the version of the extension from the composer.json file
		$md_manager = $this->ext_manager->create_extension_metadata_manager('phpbbservices/smartfeed');
		$ext_version = $md_manager->get_metadata('version');

		// These template variables apply to the overall feed, not to items in it. A post is an item in the newsfeed.
		$this->template->assign_vars(array(
				'S_SMARTFEED_FEED_DESCRIPTION' 		=> html_entity_decode($this->config['site_desc']),
				'S_SMARTFEED_FEED_TITLE' 			=> html_entity_decode($this->config['sitename']),

				'S_SMARTFEED_FEED_LANGUAGE'			=> ($this->config['phpbbservices_smartfeed_rfc1766_lang'] <> '') ? $this->config['phpbbservices_smartfeed_rfc1766_lang'] : $this->config['default_lang'],	// For RSS 2.0 and ATOM 1.0
				'S_SMARTFEED_FEED_PUBDATE'			=> date('r'),	// for RSS 2.0
				'S_SMARTFEED_FEED_TTL' 				=> ($this->config['phpbbservices_smartfeed_ttl'] <> '') ? $this->config['phpbbservices_smartfeed_ttl'] : '60',	// for RSS 2.0
				'S_SMARTFEED_FEED_TYPE' 			=> $this->feed_type,	// Atom 1.0, RSS 1.0, RSS 2.0, used as a switch. Must be 0, 1 or 2. Atom 1.0 is used to show feed type errors if they occur.
				'S_SMARTFEED_FEED_UPDATED'			=> date('c'),	// for Atom and RSS 2.0
				'S_SMARTFEED_FEED_VERSION' 			=> $ext_version,
				'S_SMARTFEED_SHOW_WEBMASTER'		=> $this->config['phpbbservices_smartfeed_webmaster'] <> '',	// RSS 2.0

				'U_SMARTFEED_FEED_GENERATOR' 		=> constants::SMARTFEED_GENERATOR,
				'U_SMARTFEED_FEED_ID'				=> generate_board_url(),
				'U_SMARTFEED_FEED_IMAGE'			=> ($this->config['phpbbservices_smartfeed_feed_image_path'] <> '') ? generate_board_url() . '/styles/' . trim($this->user->style['style_path']) . '/' . $this->config['phpbbservices_smartfeed_feed_image_path'] : generate_board_url() . '/styles/' . trim($this->user->style['style_path']) . '/theme/images/site_logo.gif', // For RSS 1.0 and 2.0.
				'U_SMARTFEED_FEED_LINK' 			=> $this->helper->route('phpbbservices_smartfeed_ui_controller', array(), true, false, \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
				'U_SMARTFEED_FEED_PAGE_URL'			=> $this->config['phpbbservices_smartfeed_url'],
				'U_SMARTFEED_WEBMASTER'				=> $this->config['phpbbservices_smartfeed_webmaster'],	// RSS 2.0
			)
		);

		if ($error)
		{
			// Since one or more errors have occurred, generate a feed with just the errors.
			$this->template->assign_block_vars('items', array(

				// Common and Atom 1.0 block variables follow
				'L_CATEGORY'	=> $this->language->lang('SMARTFEED_ERROR'),
				'L_CONTENT'		=> implode('<br>',$this->errors),
				'L_EMAIL'		=> $this->config['board_contact'],
				'L_NAME'		=> ($this->config['board_contact_name'] <> '') ? $this->config['board_contact_name'] : $this->config['board_contact'],
				'L_SUMMARY'		=> implode('<br>',$this->errors),	// Should be a "line" or so, perhaps first 80 characters of the post, perhaps stripped of HTML. Irrelevant for errors.
				'L_TITLE'		=> $this->language->lang('SMARTFEED_ERROR'),
				'S_CREATOR'		=> ($this->config['board_contact_name'] <> '') ? $this->config['board_contact_name'] : $this->config['board_contact'],
				'S_PUBLISHED'	=> date('c'),
				'S_UPDATED'		=> date('c'),
				'U_ID'			=> $this->helper->route('phpbbservices_smartfeed_ui_controller', array(), true, false, \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),

				// RSS 1.0 block variables follow
				'U_SOURCE'		=> generate_board_url(),

				// RSS 2.0 block variables follow
				'S_COMMENTS' 	=> false,
				'S_PUBDATE'		=> date('D, d M Y H:i:s O'),	// RFC-822 format required

			));
		}
		else
		{

			// $allowable_tags is used when Safe HTML is wanted for item feed output. Only these tags are allowed for HTML in the feed. Others will be stripped. <br> is not technically Safe HTML but without it paragraphs cannot be discerned, so I allowed it.
			$allowable_tags = '<abbr><accept><accept-charset><accesskey><action><align><alt><axis><border><br><cellpadding><cellspacing><char><charoff><charset><checked><cite><class><clear><cols><colspan><color><compact><coords><datetime><disabled><enctype><for><headers><height><href><hreflang><hspace><id><ismap><label><lang><longdesc><maxlength><media><method><multiple><name><nohref><noshade><nowrap><prompt><readonly><rel><rev><rows><rowspan><rules><scope><selected><shape><size><span><src><start><summary><tabindex><target><title><type><usemap><valign><value><vspace><width>';

			// Get any external newsfeeds URLs
			$ext_feeds = explode("\n", trim($this->config['phpbbservices_smartfeed_external_feeds']));

			// If there are any unread private messages, publish them first.
			if (isset($pm_rowset))
			{
				$this->publish_private_messages($pm_rowset, $allowable_tags);
			}

			// If requested to put external items at the top of the feed, do it here.
			if (($this->config['phpbbservices_smartfeed_external_feeds_top'] == 1) &&
				(($this->max_items == 0) || ($this->max_items !== 0 && $this->items_in_feed < $this->max_items)))
			{
				$this->publish_external_feeds($ext_feeds, $allowable_tags);
			}

			// Loop through the rowset, each row is an item in the feed.
			if (isset($rowset))
			{
				$this->publish_posts($rowset, $allowable_tags);
			}

			// If requested to put external items at the bottom of the feed, do it here.
			if (($this->config['phpbbservices_smartfeed_external_feeds_top'] == 0) &&
				(($this->max_items == 0) || ($this->max_items !== 0 && $this->items_in_feed < $this->max_items)))
			{
				$this->publish_external_feeds($ext_feeds, $allowable_tags);
			}

		}

	}
	
	private function create_attachment_markup ($item_id, $is_post = true)
	{
		
		// Both posts and private messages can have attachments. The code for attaching these attachments to feed items is pretty much identical. Only
		// the source of the data differs (from a post or private message). Consequently it makes sense to have one function.
		//
		// $item_id = msg_id or post_id
		// $is_post = true if $item_id is a post, false if $item_id is private message

		$attachment_markup = sprintf("<div class=\"box\">\n<p>%s</p>\n", $this->language->lang('ATTACHMENTS'));
		
		// Get all attachments
		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE post_msg_id = ' . (int) $item_id . ' AND in_message = ';
		$sql .= ($is_post) ? '0' : '1';
		$sql .= ' ORDER BY attach_id';

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$file_size = round(($row['filesize']/1000),2);
			// Show images, link to other attachments
			if (substr($row['mimetype'],0,6) == 'image/')
			{
				$anchor_begin = '';
				$anchor_end = '';
				$pm_image_text = '';
				$thumbnail_parameter = '';
				$is_thumbnail = $row['thumbnail'] == 1;
				// Logic to resize the image, if needed
				if ($is_thumbnail)
				{
					$anchor_begin = sprintf("<a href=\"%s\">", generate_board_url() . "/download/file.$this->phpEx?id=" . $row['attach_id']);
					$anchor_end = '</a>';
					$pm_image_text = $this->language->lang('SMARTFEED_POST_IMAGE_TEXT');
					$thumbnail_parameter = '&t=1';
				}
				$attachment_markup .= sprintf("%s<br><em>%s</em> (%s %s)<br>%s<img src=\"%s\" alt=\"%s\" title=\"%s\" />%s\n<br>%s", $row['attach_comment'], $row['real_filename'], $file_size, $this->language->lang('KIB'), $anchor_begin, generate_board_url() . "/download/file.$this->phpEx?id=" . $row['attach_id'] . $thumbnail_parameter, $row['attach_comment'], $row['attach_comment'], $anchor_end, $pm_image_text);
			}
			else
			{
				$attachment_markup .= ($row['attach_comment'] == '') ? '' : '<em>' . $row['attach_comment'] . '</em><br>';
				$attachment_markup .= 
					sprintf("<b><a href=\"%s\">%s</a></b> (%s %s)<br>",
						generate_board_url() . "/download/file.$this->phpEx?id=" . $row['attach_id'],
						$row['real_filename'], 
						$file_size,
						$this->language->lang('KIB'));
			}
		}
		$this->db->sql_freeresult($result);
		
		$attachment_markup .= '</div>';

		return $attachment_markup;
	
	}

	private function decrypt($data_input, $key)
	{

		// This function decrypts $data_input with the given $key using the AES-128-CBC encryption algorithm.
		// The IV used for decryption is stored in base64 before the encrypted data.
		//
		// $data_input = the encrypted password in the URL for this user, generated by ui.php
		// $key = value of phpbb_users.user_smartfeed_key for the user

		// Thanks to klapray for this logic for creating a "urlsafe" fix for base64_encode and _decode.
		$data_input = base64_decode(strtr($data_input, '-_.', '+/='));

		// Get the IV so it can be decrypted with the $key
		$iv = substr($data_input, 0, openssl_cipher_iv_length('AES-128-CBC'));

		// Encrypted data starts after the IV portion of the string
		$encrypted_data = substr($data_input, openssl_cipher_iv_length('AES-128-CBC'));
		return openssl_decrypt($encrypted_data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

	}
	
	private function truncate_words($text, $max_words, $max_words_lang_string, $just_count_words = false)
	{
	
		// This function returns the first $max_words from the supplied $text. If $just_count_words === true, a word count is returned. Note:
		// for consistency, HTML is stripped. This can be annoying, but otherwise HTML rendered in the feed may not be valid.
		//
		// $text = text for possible truncation
		// $max_words = maximum number of words allowed
		// $max_words_lang_string = the language string to denote max words exceeded, typically ...
		// $just_count_words = if true, a word count is returned rather than the trimmed text
		
		if ($just_count_words)
		{
			return str_word_count(strip_tags($text, '<br>'));
		}
		
		$word_array = explode(' ', strip_tags($text, '<br>'));

		if (count($word_array) <= $max_words)
		{
			return rtrim($text);
		}
		else
		{
			$truncated_text = '';
			for ($i=0; $i < $max_words; $i++) 
			{
				$truncated_text .= $word_array[$i] . ' ';
			}
			return rtrim($truncated_text) . $max_words_lang_string;
		}
		
	}

	private function publish_external_feeds($feeds, $allowable_tags)
	{

		// If there are external feeds, publish one at a time.
		//
		// $feeds = an array of external URIs to newsfeeds.
		// $allowable_tags = a set of allowed HTML tags if HTML safe feed is wanted.

		if (isset($feeds) && is_array($feeds))
		{
			foreach($feeds as $feed_url)
			{

				if ($feed_url <> '')
				{

					// Fetch external feeds using SimplePie.

					$feed = new \SimplePie();
					$feed->set_feed_url($feed_url);
					$feed->set_cache_location($this->phpbb_root_path . 'cache');	// Use phpBB's cache folder
					$feed->enable_cache(true);

					$success = $feed->init();

					if ($success)
					{

						$item_title = $feed->get_title();

						foreach ($feed->get_items(0, 0) as $feed_item)
						{
							if (($this->max_items == 0) || ($this->max_items !== 0 && $this->items_in_feed < $this->max_items))
							{
								$this->items_in_feed++;
								if 	(
									($this->date_limit == 0) || // No date range is set OR
									($feed_item->get_date('U') >= $this->date_limit)	// Article is within post date range limit set
								)
								{
									// Add the external item to the feed
									$title = $feed_item->get_title();
									$content = $feed_item->get_content();

									if ($this->feed_style == constants::SMARTFEED_HTMLSAFE)
									{
										$content = strip_tags($content, $allowable_tags);
									}

									// Create proper email syntax for feed based on type of feed
									$author_names = array();
									$author_emails = array('no_email@example.com');
									$authors = $feed_item->get_authors();    // Should return an array
									if (isset($authors))
									{
										$i = 0;
										foreach ($authors as $author)
										{
											$author_names[$i] = $author->get_name();
											$author_emails[$i] = $author->get_email();
											$i++;
										}
									}
									$email = $author_emails[0];

									$this->template->assign_block_vars('items', array(

										// Common and Atom 1.0 block variables follow
										'L_CATEGORY'  => (!is_null($feed_item->get_category())) ? $feed_item->get_category() : $this->language->lang('SMARTFEED_EXTERNAL_ITEM'),
										'L_CONTENT'   => $content,
										'L_EMAIL'     => $email,
										'L_NAME'      => (count($author_names) > 0) ? $author_names[0] : '',
										'L_SUMMARY'   => $content,
										'L_TITLE'     => $this->language->lang('SMARTFEED_EXTERNAL_ITEM') . $this->language->lang('SMARTFEED_DELIMITER') . html_entity_decode($item_title) . $this->language->lang('SMARTFEED_DELIMITER') . html_entity_decode(censor_text($title)),
										'S_PUBLISHED' => $feed_item->get_date('c'),
										'S_UPDATED'   => $feed_item->get_date('c'),
										'U_ID'        => $feed_item->get_permalink(),

										// RSS 1.0 block variables follow
										'S_CREATOR'   => $feed_item->get_authors(),
										'U_SOURCE'    => generate_board_url(),

										// RSS 2.0 block variables follow
										'S_COMMENTS'  => false,
										'S_PUBDATE'   => $feed_item->get_date('D, d M Y H:i:s O'),    // RFC-822 data format required

									));

								}
							}
							else
							{
								break 2;	// Hit the limit of items in a feed so break out of all loops
							}
						}

					}
					else
					{
						// Send a note to administrator there is a bad feed URL
						$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_SMARTFEED_EXTFEED', false, array($feed_url));
					}

				}

			}
		}

	}

	private function validate_user($user_id)
	{

		// Make sure the user is legitimate by checking credentials. If a public request, this logic is bypassed.
		//
		// $user_id = user_id of the user getting the feed
		//
		// This function returns true if the user is fully validated, false if a validation error occurred.
		// Get data about the requesting user from the database

		$validated_user_types = array(USER_NORMAL, USER_FOUNDER); // Validated user types are Normal and Founder. Others (Inactive, Ignore) can only get a public feed.

		$sql_ary = array(
			'SELECT'    => 'user_id, user_password, user_smartfeed_key, user_topic_sortby_type, user_topic_sortby_dir, 
					user_post_sortby_type, user_post_sortby_dir, user_lastvisit, user_type',

			'FROM'      => array(
				USERS_TABLE => 'u',
			),

			'WHERE'     =>  'user_id = ' . (int) $user_id,
		);

		if ($this->user_id !== ANONYMOUS)
		{
			$sql_ary['WHERE'] .= ' AND ' . $this->db->sql_in_set('user_type', $validated_user_types); // Robots and inactive members are not allowed to get into restricted forums
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);

		if (count($rowset) == 0)
		{
			$this->errors[] = $this->language->lang('SMARTFEED_USER_ID_DOES_NOT_EXIST');
			return false;
		}

		$row = reset($rowset);

		// Save the user variables, although these are unneeded for guests.
		$user_smartfeed_key = $row['user_smartfeed_key'];
		$user_password = $row['user_password'];

		$this->user_lastvisit = $row['user_lastvisit'];
		$this->user_topic_sortby_type = $row['user_topic_sortby_type'];
		$this->user_topic_sortby_dir = $row['user_topic_sortby_dir'];
		$this->user_post_sortby_type = $row['user_post_sortby_type'];
		$this->user_post_sortby_dir = $row['user_post_sortby_dir'];

		$this->db->sql_freeresult($result); // Query be gone!

		if ($this->is_registered)
		{

			if (strlen($user_smartfeed_key) == 0)
			{
				// If the $user_smartfeed_key is an empty string, the password cannot be decrypted. It's hard to imagine how this could happen
				// unless the feed was called before the user interface was run.
				$this->errors[] = $this->language->lang('SMARTFEED_BAD_PASSWORD_ERROR', $this->encrypted_pswd, $this->user_id);
				return false;
			}

			// Decrypt password using the user_smartfeed_key column in the phpbb_users table. This should have been created
			// the first time the user interface was run by this user. There should not be a clear text password in the database.
			$encoded_pswd = $this->decrypt($this->encrypted_pswd, $user_smartfeed_key);

			// If IP Authentication was enabled, the encoded password is to the left of the ~ and the IP to the right of the ~
			$tilde = strpos($encoded_pswd, '~');
			$client_ip_parts = explode('.', $this->user->ip);    // Client's current IP, based on what the web server recorded.
			if (($tilde == 0) && ($this->config['phpbbservices_smartfeed_require_ip_authentication'] == '1'))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_IP_AUTH_ERROR');
				return false;
			}

			if (!$client_ip_parts || empty($client_ip_parts) || !filter_var($this->user->ip, FILTER_VALIDATE_IP))
			{
				// Something is really odd if the number of address ranges in the client is not 4 or 8!
				$this->errors[] = $this->language->lang('SMARTFEED_IP_RANGE_ERROR', $this->user->ip);
				return false;
			}

			if ($tilde > 0)
			{
				// Since a tilde is present, authenticate the client IP by comparing it with the IP embedded in the "e" parameter
				$authorized_ip = substr($encoded_pswd, $tilde + 1);
				$encoded_pswd = substr($encoded_pswd, 0, $tilde);

				$source_ip_parts = explode('.', $authorized_ip);	// IP range authorized for this user

				// Show error message if requested from incorrect range of IP addresses
				switch (count($client_ip_parts))
				{

					case 4:	 // IPV4, last part of the IP can be any number
						if (!(
							($client_ip_parts[0] == $source_ip_parts[0]) &&
							($client_ip_parts[1] == $source_ip_parts[1]) &&
							($client_ip_parts[2] == $source_ip_parts[2])
						))
						{
							$this->errors[] = $this->language->lang('SMARTFEED_IP_AUTH_ERROR');
							return false;
						}
					break;

					case 8:	 // IPV6, last part of the IP can be any number
						if (!(
							($client_ip_parts[0] == $source_ip_parts[0]) &&
							($client_ip_parts[1] == $source_ip_parts[1]) &&
							($client_ip_parts[2] == $source_ip_parts[2]) &&
							($client_ip_parts[3] == $source_ip_parts[3]) &&
							($client_ip_parts[4] == $source_ip_parts[4]) &&
							($client_ip_parts[5] == $source_ip_parts[5]) &&
							($client_ip_parts[6] == $source_ip_parts[6])
						))
						{
							$this->errors[] = $this->language->lang('SMARTFEED_IP_AUTH_ERROR');
							return false;
						}
					break;

					default:	// IP is not formatted correctly or may be absent. This should be caught by the filter_var() call.
						$this->errors[] = $this->language->lang('SMARTFEED_IP_AUTH_ERROR');
						return false;

				}

			}

			// Do not generate a feed if the asserted encrypted password does not equal the actual database encrypted password.
			if (trim($encoded_pswd) !== trim($user_password))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_BAD_PASSWORD_ERROR', $this->encrypted_pswd, $this->user_id);
				return false;
			}

		}

		// User is either authenticated or no authentication was needed (guest)
		return true;

	}

	private function set_date_limit($time_limit, $sql=true)
	{

		// This function contains the logic to limit the range of posts fetched in the feed follows by creating the appropriate SQL snippet
		//
		// $time_limit = Time limit from now for the feed requested, which comes from the URI supplied by the feed
		// $sql = true|false. If true, returns a SQL string. If false, returns the timestamp for the date limit
		//
		// Returns a snippet of SQL used to construct the SQL query

		$start_time = ($this->config['phpbbservices_smartfeed_default_fetch_time_limit'] == 0) ? 0 : time() - ($this->config['phpbbservices_smartfeed_default_fetch_time_limit'] * 60 * 60);

		switch ($time_limit)
		{

			case constants::SMARTFEED_NO_LIMIT_VALUE:
				$date_limit = $start_time;
			break;

			case constants::SMARTFEED_LAST_QUARTER_VALUE:
				$date_limit = max($start_time, time() - (90 * 24 * 60 * 60));
			break;

			case constants::SMARTFEED_LAST_MONTH_VALUE:
				$date_limit = max($start_time, time() - (30 * 24 * 60 * 60));
			break;

			case constants::SMARTFEED_LAST_TWO_WEEKS_VALUE:
				$date_limit = max($start_time, time() - (14 * 24 * 60 * 60));
			break;

			case constants::SMARTFEED_LAST_WEEK_VALUE:
				$date_limit = max($start_time, time() - (7 * 24 * 60 * 60));
			break;

			case constants::SMARTFEED_LAST_DAY_VALUE:
				$date_limit = max($start_time, time() - (24 * 60 * 60));
			break;

			case constants::SMARTFEED_LAST_12_HOURS_VALUE:
				$date_limit = max($start_time, time() - (12 * 60 * 60));
			break;

			case constants::SMARTFEED_LAST_6_HOURS_VALUE:
				$date_limit = max($start_time, time() - (6 * 60 * 60));
			break;

			case constants::SMARTFEED_LAST_3_HOURS_VALUE:
				$date_limit = max($start_time, time() - (3 * 60 * 60));
			break;

			case constants::SMARTFEED_LAST_1_HOURS_VALUE:
				$date_limit = max($start_time, time() - (60 * 60));
			break;

			case constants::SMARTFEED_LAST_30_MINUTES_VALUE:
				$date_limit = max($start_time, time() - (30 * 60));
			break;

			case constants::SMARTFEED_LAST_15_MINUTES_VALUE:
				$date_limit = max($start_time, time() - (15 * 60));
			break;

			case constants::SMARTFEED_SINCE_LAST_VISIT_VALUE:
			default:
				$date_limit = max($start_time, $this->user_lastvisit);
			break;

		}

		return $sql ? ' AND p.post_time > ' . $date_limit : $date_limit;

	}

	private function get_bookmarked_forums()
	{

		// Returns an array of bookmarked forum_ids for the user.
		//
		// When selecting bookmarked topics only, we can safely ignore the logic constraining the user to read only
		// from certain forums. Instead we will create the SQL to get the bookmarked topics, if any, hijacking the
		// $fetched_forums_str variable since it is convenient. This function returns a list of topic_ids corresponding
		// to any bookmarked topics, or false if an error occurs.

		$bookmarked_topic_ids = array();

		$sql_ary = array(
			'SELECT'    => 't.topic_id',

			'FROM'      => array(
				USERS_TABLE 		=> 'u',
				BOOKMARKS_TABLE    	=> 'b',
				TOPICS_TABLE    	=> 't',
			),

			'WHERE'     =>  "u.user_id = b.user_id AND b.topic_id = t.topic_id 
								AND t.topic_last_post_time > $this->date_limit
								AND b.user_id = $this->user_id",
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);

		// Run the built query statement
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$bookmarked_topic_ids[] = intval($row['topic_id']);
		}

		$this->db->sql_freeresult($result);
		if (count($bookmarked_topic_ids) > 0)
		{
			$fetched_forums_str = ' AND ' . $this->db->sql_in_set('t.topic_id', $bookmarked_topic_ids);
		}
		else
		{
			// Logically, if there are no bookmarked topics for this $this->user_id then there will be nothing in the feed.
			// Send a message to this effect in the feed.
			$this->errors[] = $this->language->lang('SMARTFEED_NO_BOOKMARKS');
			return false;
		}

		return $fetched_forums_str;

	}

	private function get_fetched_forums()
	{

		// This function returns a comma delimited string of forum_ids the will access based on their permissions and the forums wanted.

		// We need to know which auth_option_id corresponds to the forum read privilege (f_read) privilege.
		$auth_options = array('f_read');

		$sql_ary = array(
			'SELECT'    => 'auth_option, auth_option_id',

			'FROM'      => array(
				ACL_OPTIONS_TABLE => 'ao',
			),

			'WHERE'     =>  $this->db->sql_in_set('auth_option', $auth_options),
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['auth_option'] == 'f_read')
			{
				$read_id = $row['auth_option_id'];
				break;
			}
		}

		$this->db->sql_freeresult($result); // Query be gone!

		// Now let's get this user's forum permissions. Note that non-registered, robots etc. get a list of public forums
		// with read permissions.

		$allowed_forum_ids = array();
		$parent_array = array();

		$forum_array = $this->auth->acl_raw_data_single_user($this->user_id);
		foreach ($forum_array as $key => $value)
		{
			foreach ($value as $auth_option_id => $auth_setting)
			{
				if (isset($read_id) && $auth_option_id == $read_id)
				{
					if (($auth_setting == 1) && $this->common->check_all_parents($parent_array, $key))
					{
						$allowed_forum_ids[] = (int) $key;
					}
				}
			}
		}

		if (count($allowed_forum_ids) == 0)
		{
			// If this user cannot retrieve ANY forums, this suggests that this board is tightly locked down to members only,
			// or every member must belong to a user group or have special forum permissions
			$this->errors[] = $this->language->lang('SMARTFEED_NO_ACCESSIBLE_FORUMS');
			return false;
		}

		// Get the requested forums. If none are listed, user wants all forums for which they have read access.
		$query_string = $this->user->page['query_string'];	// The entire query string will be needed later to parse out the forums wanted.
		$requested_forum_ids = array();
		$params = explode('&', $query_string);
		$required_forums_only = false;
		foreach ($params as $item)
		{
			if ($item == constants::SMARTFEED_FORUMS . '=' . constants::SMARTFEED_REQUIRED_FORUMS_ONLY)
			{
				// This is an unusual case and it means that no forums were selected but there are required forums.
				// In this case the feed is restricted to returning content ONLY for required forums.
				$required_forums_only = true;
				break;
			}
			if (substr($item,0,2) == constants::SMARTFEED_FORUMS . '=')
			{
				$requested_forum_ids[] = (int) substr($item,2);
			}
		}

		// To capture global announcements when forums are specified, we have to add the pseudo-forum with a forum_id = 0.
		if (count($requested_forum_ids) > 0)
		{
			$requested_forum_ids[] = (int) 0;
		}

		// Sort requested forums by forum_id and ensure there are no duplicates
		asort($requested_forum_ids);
		$requested_forum_ids = array_unique($requested_forum_ids);

		// The forums that will be fetched is the set intersection of the requested and allowed forums. This prevents hacking
		// the URL to get feeds a user is not supposed to get. If no forums are specified on the URL field then all forums that
		// this user is authorized to access is assumed.

		if (!$required_forums_only)
		{
			$fetched_forums = (count($requested_forum_ids) > 0) ? array_intersect($allowed_forum_ids, $requested_forum_ids): $allowed_forum_ids;
			// Add in any required forums
			if (strlen($this->config['phpbbservices_smartfeed_include_forums']) > 0)
			{
				$fetched_forums = array_merge($fetched_forums, explode(',', $this->config['phpbbservices_smartfeed_include_forums']));
			}
		}
		else
		{
			$fetched_forums = explode(',', (int) $this->config['phpbbservices_smartfeed_include_forums']);
		}

		// Remove any prohibited forums
		$excluded_forums = (isset($this->config['phpbbservices_smartfeed_exclude_forums'])) ? explode(',', $this->config['phpbbservices_smartfeed_exclude_forums']) : array();
		if (count($excluded_forums) > 0)
		{
			$fetched_forums = array_diff($fetched_forums, $excluded_forums);
		}
		$fetched_forums = array_unique($fetched_forums);

		// Create a SQL fragment to return posts from the correct forums
		if (count($fetched_forums) > 0)
		{
			$fetched_forums_str = ' AND ' . str_replace("'", '', $this->db->sql_in_set('p.forum_id', $fetched_forums));
		}
		else
		{
			// If there are no forums to fetch, this will result in an empty newsfeed.
			$this->errors[] = $this->language->lang('SMARTFEED_NO_FORUMS_ACCESSIBLE');
			return false;
		}

		return $fetched_forums_str;

	}

	private function get_sort_sql()
	{

		// This function returns a SQL order by snippet used to order the posts presented in the feed.

		$order_by_sql = '';

		switch($this->sort_by)
		{
			case constants::SMARTFEED_BOARD:
				$topic_asc_desc = ($this->user_topic_sortby_dir == 'd') ? 'DESC' : '';
				$post_asc_desc = ($this->user_post_sortby_dir == 'd') ? 'DESC' : '';
				switch($this->user_topic_sortby_type)
				{
					case 'a':
						$order_by_sql = "t.topic_first_poster_name $topic_asc_desc, ";
						switch($this->user_post_sortby_type)
						{
							case 'a':
								$order_by_sql .= "u.username_clean $post_asc_desc";
							break;
							case 't':
								$order_by_sql .= "p.post_time $post_asc_desc";
							break;
							case 's':
								$order_by_sql .= "p.post_subject $post_asc_desc" ;
							break;
						}
					break;
					case 't':
						$order_by_sql = "t.topic_last_post_time $topic_asc_desc, ";
						switch($this->user_post_sortby_type)
						{
							case 'a':
								$order_by_sql .= "u.username_clean $post_asc_desc";
							break;
							case 't':
								$order_by_sql .= "p.post_time $post_asc_desc";
							break;
							case 's':
								$order_by_sql .= "p.post_subject $post_asc_desc" ;
							break;
						}
					break;
					case 'r':
						$order_by_sql = "t.posts_approved $topic_asc_desc, ";
						switch($this->user_post_sortby_type)
						{
							case 'a':
								$order_by_sql .= "u.username_clean $post_asc_desc";
							break;
							case 't':
								$order_by_sql .= "p.post_time $post_asc_desc";
							break;
							case 's':
								$order_by_sql .= "p.post_subject $post_asc_desc" ;
							break;
						}
					break;
					case 's':
						$order_by_sql = "t.topic_title $topic_asc_desc, " ;
						switch($this->user_post_sortby_type)
						{
							case 'a':
								$order_by_sql .= "u.username_clean $post_asc_desc";
							break;
							case 't':
								$order_by_sql .= "p.post_time $post_asc_desc";
							break;
							case 's':
								$order_by_sql .= "p.post_subject $post_asc_desc" ;
							break;
						}
					break;
					case 'v':
						$order_by_sql = "t.topic_views $topic_asc_desc";
					break;
				}
			break;
			case constants::SMARTFEED_STANDARD:
			default:
				$order_by_sql = 'f.left_id, f.right_id, t.topic_last_post_time, p.post_time';
			break;
			case constants::SMARTFEED_STANDARD_DESC:
				$order_by_sql = 'f.left_id, f.right_id, t.topic_last_post_time, p.post_time DESC';
			break;
			case constants::SMARTFEED_POSTDATE:
				$order_by_sql = 'p.post_time';
			break;
			case constants::SMARTFEED_POSTDATE_DESC:
				$order_by_sql = 'p.post_time DESC';
			break;
		}

		return $order_by_sql;

	}

	private function get_foes_sql()
	{

		// Returns a snippet of SQL of foes user_ids whose post should not be included in the feed, if this setting is enabled.
		// Most of the time this will be a null string.

		$filter_foes_sql = '';
		$foes = array();
		if ($this->is_registered && ($this->filter_foes == 1))
		{

			// Fetch your foes
			$sql_ary = array(
				'SELECT'    => 'zebra_id',

				'FROM'      => array(
					ZEBRA_TABLE => 'z',
				),

				'WHERE'     =>  "user_id = $this->user_id AND foe = 1",
			);

			$sql = $this->db->sql_build_query('SELECT', $sql_ary);

			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$foes[] = (int) $row['zebra_id'];
			}
			$this->db->sql_freeresult($result);

			if (count($foes) > 0)
			{
				$filter_foes_sql = ' AND ' . $this->db->sql_in_set('p.poster_id', $foes, true);
			}

		}

		return $filter_foes_sql;

	}

	private function publish_private_messages(&$pm_rowset, &$allowable_tags)
	{

		// This function handles publishing any private messages in the feed using a list of private messages
		// in an array already fetched for the user from the database.
		//
		// $pm_rowset = array of private messages for the user
		// $allowable_tags = a set of allowed HTML tags if HTML safe feed is wanted.

		$email = '';

		foreach ($pm_rowset as $row)
		{

			// Create the username, title and link for the private message
			if ($this->config['phpbbservices_smartfeed_new_post_notifications_only'])
			{
				$username = $this->language->lang('ADMINISTRATOR');
				$title = $this->language->lang('SMARTFEED_NEW_PMS_NOTIFICATIONS_SHORT');
				$link = htmlentities($this->board_url . 'ucp.' . $this->phpEx . '?i=pm&folder=inbox', ENT_QUOTES, 'UTF-8');
				$message = $this->language->lang('SMARTFEED_NEW_PMS_NOTIFICATIONS_ONLY');
			}
			else
			{
				$username = $row['username']; // Don't need to worry about Anonymous users for private messages, they cannot send them
				$title = $this->language->lang('PRIVATE_MESSAGE') . $this->language->lang('SMARTFEED_DELIMITER') . $row['message_subject'] . $this->language->lang('SMARTFEED_DELIMITER') . $this->language->lang('FROM') . ' ' . $username;
				$link = htmlentities($this->board_url . 'ucp.' . $this->phpEx . '?i=pm&mode=view&f=0&p=' . $row['msg_id'], ENT_QUOTES, 'UTF-8');

				// Set an email address associated with the poster of the private message. In most cases it should not be seen.
				if ($this->config['phpbbservices_smartfeed_privacy_mode'])
				{
					// Some feeds requires an email field to validate. Use a fake email address.
					$email = ($this->feed_type==constants::SMARTFEED_RSS2 || $this->feed_type==constants::SMARTFEED_ATOM) ? 'no_email@example.com' : '';
				}
				else
				{
					// Smartfeed privacy mode must be off AND the user must give permission for his/her email to appear in their profile for it show.
					$email = ($row['user_allow_viewemail']) ? $row['user_email'] : 'no_email@example.com';
				}

				$flags = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
					(($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) +
					(($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);

				$message = generate_text_for_display($row['message_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $flags);
				$message = rtrim($message, '</');	// Bug in generate_text_for_display?
				$message = censor_text($message);	// No naughty words

				$user_sig = ( $row['enable_sig'] && ($row['user_sig'] !== '') && $this->config['allow_sig'] && ($this->config['phpbbservices_smartfeed_privacy_mode'] == '0') ) ? censor_text($row['user_sig']) : '';

				if (($this->feed_style == constants::SMARTFEED_HTML) || ($this->feed_style == constants::SMARTFEED_HTMLSAFE))
				{
					// Add any attachments to the private message item
					if ($row['message_attachment'] > 0)
					{
						$message .= $this->create_attachment_markup ($row['msg_id'], false);
					}
					$this->append_user_signature($user_sig, $message, $row, $allowable_tags, $flags);

				}
				else
				{
					// Either Compact or Basic Style wanted
					$this->condense_feed_item($user_sig, $message);
				}
			}

			// Handle the maximum number of words requested per PM logic
			if ($this->max_words !== 0)
			{
				$message = $this->truncate_words($message, intval($this->max_words), $this->language->lang('SMARTFEED_MAX_WORDS_NOTIFIER'));
			}

			if (($this->max_items == 0) || ($this->max_items !== 0 && $this->items_in_feed < $this->max_items))
			{
				$this->items_in_feed++;

				// Attach the private message to the feed as an item
				$this->template->assign_block_vars('items', array(

					// Common and Atom 1.0 block variables follow
					'L_CATEGORY'  => $this->language->lang('PRIVATE_MESSAGE'),
					'L_CONTENT'   => $message,
					'L_EMAIL'     => $email,
					'L_NAME'      => $username,
					'L_SUMMARY'   => $message,
					'L_TITLE'     => html_entity_decode(censor_text($title)),
					'S_CREATOR'   => $email . ' (' . $username . ')',
					'S_PUBLISHED' => date('c', $row['message_time']),
					'S_UPDATED'   => ($row['message_edit_time'] > 0) ? date('c', $row['message_edit_time']) : date('c', $row['message_time']),
					'U_ID'        => $link,

					// RSS 1.0 block variables follow
					'U_SOURCE'    => generate_board_url(),

					// RSS 2.0 block variables follow
					'S_COMMENTS'  => true,
					'S_PUBDATE'   => ($row['message_edit_time'] > 0) ? date('D, d M Y H:i:s O', $row['message_edit_time']) : date('D, d M Y H:i:s O', $row['message_time']),    // RFC-822 date format required.
				));

				// If we are to get only a notification that there are new private messages or posts, we should go through this loop only once.
				if ($this->config['phpbbservices_smartfeed_new_post_notifications_only'])
				{
					break;
				}

				if ($this->mark_private_messages)
				{
					// Mark this private message as read
					$sql_ary = array(
						'pm_unread' => 0,
						'pm_new'    => 0,
						'folder_id' => 0
					);

					$this->db->sql_transaction('begin');

					$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
								SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE msg_id = ' . (int) $row['msg_id'] . ' 
									AND user_id = ' . (int) $this->user_id . '
									AND author_id = ' . (int) $row['author_id'] . ' 
									AND folder_id = ' . (int) $row['folder_id'];

					$this->db->sql_query($sql);

					// Since any unread and new private messages will be in the digest, it's safe to set these values to zero.

					$sql = 'UPDATE ' . USERS_TABLE . '
									SET user_unread_privmsg = 0, 
										user_new_privmsg = 0
									WHERE user_id = ' . (int) $this->user_id;

					$this->db->sql_query($sql);

					$this->db->sql_transaction('commit');

					// Next, mark all private message notification for the subscriber as read
					$this->phpbb_notifications->mark_notifications('notification.type.pm', false, $this->user_id, false);

				}

			}
			else
			{
				break;
			}

		}

	}

	private function publish_posts (&$rowset, &$allowable_tags)
	{

		// This function handles the publishing of any posts in the feed for the user, using the SQL already gathered
		// in the $rowset array for the user.
		//
		// $rowset = array of posts in the feed for the user
		// $allowable_tags = a set of allowed HTML tags if HTML safe feed is wanted.

		$topic_feed = $this->first_post_only || $this->last_post_only;

		foreach ($rowset as $row)
		{

			if (($this->min_words == 0) ||
				($this->min_words !== 0 && $this->truncate_words($row['post_text'], intval($this->max_words), $this->language->lang('SMARTFEED_MAX_WORDS_NOTIFIER'), true) >= $this->min_words)
			)
			{
				// This post goes in the newsfeed

				if ($this->config['phpbbservices_smartfeed_new_post_notifications_only'])
				{
					$username = $this->language->lang('ADMINISTRATOR');
				}
				else
				{
					$username = ($row['user_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'];
				}

				// Create the title for the item (post)
				if ($this->config['phpbbservices_smartfeed_new_post_notifications_only'])
				{
					if ($this->config['phpbbservices_smartfeed_suppress_forum_names'] || $this->suppress_forum_names)
					{
						$item_title = htmlentities($row['topic_title'], ENT_QUOTES, 'UTF-8');
					}
					else
					{
						$forum_name = ($row['forum_name'] == NULL) ? $this->language->lang('SMARTFEED_GLOBAL_ANNOUNCEMENT') : htmlentities($row['forum_name'], ENT_QUOTES, 'UTF-8');
						$item_title = $forum_name . $this->language->lang('SMARTFEED_DELIMITER') . htmlentities($row['topic_title'], ENT_QUOTES, 'UTF-8');
					}
				}
				else
				{
					$forum_name = ($row['forum_name'] == NULL) ? $this->language->lang('SMARTFEED_GLOBAL_ANNOUNCEMENT') : htmlentities($row['forum_name'], ENT_QUOTES, 'UTF-8');
					$item_title = ($this->show_topic_titles) ? $row['topic_title'] : $row['post_subject'];
					if ($item_title !== '')
					{
						$item_title = ($this->config['phpbbservices_smartfeed_suppress_forum_names'] || $this->suppress_forum_names) ? htmlentities($item_title, ENT_QUOTES, 'UTF-8') : $forum_name . $this->language->lang('SMARTFEED_DELIMITER') . htmlentities($item_title, ENT_QUOTES, 'UTF-8');
					}
					else
					{
						if (!$this->show_topic_titles)
						{
							$item_title = ($this->config['phpbbservices_smartfeed_suppress_forum_names'] || $this->suppress_forum_names) ? 'Re: ' . htmlentities($item_title, ENT_QUOTES, 'UTF-8') : $forum_name . $this->language->lang('SMARTFEED_DELIMITER') . 'Re: ' . htmlentities($item_title, ENT_QUOTES, 'UTF-8');

						}
						else
						{
							$item_title = ($this->config['phpbbservices_smartfeed_suppress_forum_names'] || $this->suppress_forum_names) ? htmlentities($item_title, ENT_QUOTES, 'UTF-8') : $forum_name . $this->language->lang('SMARTFEED_DELIMITER') . htmlentities($item_title, ENT_QUOTES, 'UTF-8');
						}
					}
					$item_title = html_entity_decode($item_title);

					if ($row['topic_first_post_id'] !== $row['post_id'])
					{
						if ($this->config['phpbbservices_smartfeed_show_username_in_replies'] && !$this->suppress_usernames)
						{
							$item_title .= ($row['username'] == '') ? $this->language->lang('SMARTFEED_DELIMITER') . $this->language->lang('SMARTFEED_REPLY_BY') . ' ' . $this->language->lang('GUEST') . ' ' . $username : $this->language->lang('SMARTFEED_DELIMITER') . $this->language->lang('SMARTFEED_REPLY_BY') . ' ' . $username;
						}
					}
					else
					{
						if ($this->config['phpbbservices_smartfeed_show_username_in_first_topic_post'] && !$this->suppress_usernames)
						{
							$item_title .= $this->language->lang('SMARTFEED_DELIMITER') . $this->language->lang('AUTHOR') . ' ' . $username;
						}
					}
				}

				$item_title = html_entity_decode(censor_text($item_title));

				if ($topic_feed)
				{
					$link = htmlentities($this->board_url . 'viewtopic.' . $this->phpEx . '?f=' . $row['forum_id'] . '&t=' . $row['topic_id'], ENT_QUOTES, 'UTF-8');
				}
				else
				{
					$link = htmlentities($this->board_url . 'viewtopic.' . $this->phpEx . '?f=' . $row['forum_id'] . '&t=' . $row['topic_id'] . '&p=' . $row['post_id']  . '#p' . $row['post_id'], ENT_QUOTES, 'UTF-8');
				}
				$item_category = html_entity_decode($row['forum_name']);

				// Set an email address associated with the poster. In most cases it should not be seen.
				if ($this->config['phpbbservices_smartfeed_privacy_mode'])
				{
					// Some feeds requires an email field to validate. Use a fake email address.
					$email = ($this->feed_type==constants::SMARTFEED_RSS2 || $this->feed_type==constants::SMARTFEED_ATOM) ? 'no_email@example.com' : '';
				}
				else
				{
					// Smartfeed privacy mode must be off AND the user must give permission for his/her email to appear in their profile for it show.
					$email = ($row['user_allow_viewemail']) ? $row['user_email'] : 'no_email@example.com';
				}

				// To "dress up" the post text with bbCode, images, smilies etc., we need to use generate_text_for_display() function.
				if ($this->config['phpbbservices_smartfeed_new_post_notifications_only'])
				{
					$new_topic = $row['post_id'] == $row['topic_first_post_id'];
					if ($new_topic)
					{
						$item_text = $this->language->lang('SMARTFEED_NEW_TOPIC_NOTIFICATION');
					}
					else
					{
						$item_text = $this->language->lang('SMARTFEED_NEW_POST_NOTIFICATION');
					}
				}
				else
				{
					$flags = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
						(($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) +
						(($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);

					$item_text = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $flags);
					$item_text = rtrim($item_text, '</');	// Bug in generate_text_for_display?
					$item_text = censor_text($item_text);

					$user_sig = ( $row['enable_sig'] && $row['user_sig'] !== '' && $this->config['allow_sig'] && (!($this->config['phpbbservices_smartfeed_privacy_mode']) || $this->is_registered) ) ? censor_text($row['user_sig']) : '';

					if (($this->feed_style == constants::SMARTFEED_HTML) || ($this->feed_style == constants::SMARTFEED_HTMLSAFE))
					{
						// If there is an image, show it. If there is a file, link to the attachment
						if ($row['post_attachment'] > 0)
						{
							$item_text .= $this->create_attachment_markup ($row['post_id'], true);
						}
						$this->append_user_signature($user_sig, $item_text, $row, $allowable_tags, $flags);
					}
					else
					{
						// Either Compact or Basic Style wanted
						$this->condense_feed_item($user_sig, $item_text);
					}

					// Handle the maximum number of words to display in a post.
					if ($this->config['phpbbservices_smartfeed_max_word_size'] > 0 && $this->max_words > 0)
					{
						$item_text = $this->truncate_words($item_text, min($this->config['phpbbservices_smartfeed_max_word_size'],$this->max_words), $this->language->lang('SMARTFEED_MAX_WORDS_NOTIFIER'));
					}
					else if ($this->config['phpbbservices_smartfeed_max_word_size'] > 0 && $this->max_words == 0)
					{
						$item_text = $this->truncate_words($item_text, $this->config['phpbbservices_smartfeed_max_word_size'], $this->language->lang('SMARTFEED_MAX_WORDS_NOTIFIER'));
					}
					else if ($this->max_words > 0)
					{
						$item_text = $this->truncate_words($item_text, intval($this->max_words), $this->language->lang('SMARTFEED_MAX_WORDS_NOTIFIER'));
					}
				}

				// Add the item (post) to the feed

				if (($this->max_items == 0) || ($this->max_items !== 0 && $this->items_in_feed < $this->max_items))
				{
					$this->items_in_feed++;

					$item_title = html_entity_decode(censor_text($item_title));

					$this->template->assign_block_vars('items', array(

						// Common and Atom 1.0 block variables follow
						'L_CATEGORY'  => $item_category,
						'L_CONTENT'   => $item_text,
						'L_EMAIL'     => $email,
						'L_NAME'      => $username,
						'L_SUMMARY'   => $item_text,
						'L_TITLE'     => $item_title,
						'S_CREATOR'   => $email . ' (' . $username . ')',
						'S_PUBLISHED' => date('c', $row['post_time']),
						'S_UPDATED'   => ($row['post_edit_time'] > 0) ? date('c', $row['post_edit_time']) : date('c', $row['post_time']),
						'U_ID'        => $link,

						// RSS 1.0 block variables follow
						'U_SOURCE'    => generate_board_url(),

						// RSS 2.0 block variables follow
						'S_COMMENTS'  => true,
						'S_PUBDATE'   => ($row['post_edit_time'] > 0) ? date('D, d M Y H:i:s O', $row['post_edit_time']) : date('D, d M Y H:i:s O', $row['post_time']),    // RFC-822 data format required

					));
				}
				else
				{
					break;
				}
			}

		}

	}

	function append_user_signature($user_sig, $item_text, $row, $allowable_tags, $flags)
	{

		// Intelligently applies a user's signature to a post/private message and pretties up the result for display.

		if ($user_sig !== '')
		{
			$user_sig = generate_text_for_display($user_sig, $row['user_sig_bbcode_uid'], $row['user_sig_bbcode_bitfield'], $flags);
			$user_sig = rtrim($user_sig, '</');	// Bug in generate_text_for_display?
		}

		$item_text = ($user_sig !== '') ? $item_text . $this->language->lang('SMARTFEED_POST_SIGNATURE_DELIMITER') . $user_sig : $item_text;

		$item_text = str_replace('<img src="./../../', '<img src="' . $this->board_url, $item_text);
		$item_text = str_replace('<img class="smilies" src="./../../', '<img class="smilies" src="' . $this->board_url, $item_text);

		if ($this->feed_style == constants::SMARTFEED_HTMLSAFE)
		{
			$item_text = strip_tags($item_text, $allowable_tags);
		}
		return $item_text;

	}

	function condense_feed_item($user_sig, $item_text)
	{

		// Condenses an item in the feed, stripping tags and returning plain text.
		
		if ($this->feed_style == constants::SMARTFEED_BASIC)
		{
			$item_text = ($user_sig !== '') ? $item_text . "\n\n" . $user_sig : $item_text;
		}
		
		strip_bbcode($item_text); 			// Remove the BBCode
		$item_text = strip_tags($item_text, '<br>');	// Gets rid of any embedded HTML except break for formatting
		
		// Either condense all text or make line feeds explicit
		$item_text = ($this->feed_style == constants::SMARTFEED_BASIC) ? nl2br($item_text) : str_replace("\n", ' ', $item_text);
		return $item_text;
		
	}
}
