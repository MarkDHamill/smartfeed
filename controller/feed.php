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
	private $auth;
	private $common;
	private $config;
	private $db;
	private $helper;
	private $language;
	private $template;
	private $phpbb_log;
	private $phpbb_notifications;
	private $phpbb_root_path; // Only used in functions.
	private $phpEx;
	private $request;
	private $user;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth						$auth
	* @param \phpbbservices\smartfeed\core\common	$common
	* @param \phpbb\config\config					$config
	* @param \phpbb\db\driver\factory				$db
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\language\language 				$language
	* @param \phpbb\log\log							$phpbb_log
	* @param string									$phpbb_root_path
	* @param string									$php_ext
	* @param \phpbb\request\request 				$request
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param \phpbb\notification\manager 			$notification_manager 	Notifications manager
	*/
	
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user,
		$php_ext, \phpbb\db\driver\factory $db, \phpbb\auth\auth $auth, $phpbb_root_path, \phpbb\request\request $request, \phpbb\log\log $phpbb_log, 
		\phpbbservices\smartfeed\core\common $common, \phpbb\language\language $language, \phpbb\notification\manager $notification_manager)
	{

		// External classes and variables injected into the class
		$this->auth = $auth;
		$this->common = $common;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->language = $language;
		$this->phpEx = $php_ext;
		$this->phpbb_log = $phpbb_log;
		$this->phpbb_notifications = $notification_manager;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;

		// Other useful class variables
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
		$this->remove_my_posts = NULL;
		$this->show_topic_titles = NULL;
		$this->show_pms = NULL;
		$this->sort_by = NULL;
		$this->suppress_forum_names = NULL;
		$this->suppress_usernames = NULL;
		$this->time_limit = NULL;
		$this->true_false_array = array(0, 1);
		$this->user_id = ANONYMOUS;	// Assume guest

	}
	
	private function check_for_errors ()
	{
		
		// This function checks for logical errors in the URL

		// If board is disabled, disable feeds as well.
		if ($this->config['board_disable'])
		{
			$this->errors[] = $this->language->lang('SMARTFEED_BOARD_DISABLED');
			return;
		}
	
		// What is the feed type (ATOM 1.0, RSS 1.0 or RSS 2.0?) -- if not specified or an incorrect value, default to Atom 1.0.
		$this->feed_type = $this->request->variable(constants::SMARTFEED_FEED_TYPE, constants::SMARTFEED_ATOM);
		if (!($this->feed_type == constants::SMARTFEED_ATOM || $this->feed_type == constants::SMARTFEED_RSS1 || $this->feed_type == constants::SMARTFEED_RSS2))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_FEED_TYPE_ERROR', $this->feed_type);
		}

		// Determine if this is a public request. If so only posts in public forums will be shown in the feed.
		if ($this->user_id !== ANONYMOUS && $this->encrypted_pswd !== constants::SMARTFEED_NONE)
		{
			// Feed privileges are dependent upon the auth_method. This code makes this program consistent with the user interface.
			if (($this->config['auth_method'] == 'apache') && ($this->config['phpbbservices_smartfeed_apache_htaccess_enabled'] == 0))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_APACHE_AUTHENTICATION_WARNING_REG');
			}
			$this->is_registered = true;
		}
		else if (!(($this->user_id == ANONYMOUS) && ($this->encrypted_pswd == constants::SMARTFEED_NONE)))
		{
			// Logically if only the u or the e parameter is present, the URL is inconsisent, so generate an error.
			if ($this->user_id == ANONYMOUS)
			{
				$this->errors[] = $this->language->lang('SMARTFEED_NO_U_ARGUMENT');
			}
			if ($this->encrypted_pswd == constants::SMARTFEED_NONE)
			{
				$this->errors[] = $this->language->lang('SMARTFEED_NO_E_ARGUMENT');
			}
		}

		// Get the limit parameter. It limits the size of the newsfeed to a point in time from the present, either a day/hour/minute interval, no limit
		// or the time since the user's last visit. If it doesn't exist, $this->config['phpbbservices_smartfeed_default_fetch_time_limit'] is used.
		$this->time_limit = $this->request->variable(constants::SMARTFEED_TIME_LIMIT, $this->config['phpbbservices_smartfeed_default_fetch_time_limit']);
		if (!is_numeric($this->time_limit))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_LIMIT_FORMAT_ERROR');
		}

		if ($this->is_registered && ((int) $this->time_limit < (int) constants::SMARTFEED_SINCE_LAST_VISIT_VALUE) || ((int) $this->time_limit > (int) constants::SMARTFEED_LAST_15_MINUTES_VALUE) )
		{
			$this->errors[] = $this->language->lang('SMARTFEED_LIMIT_FORMAT_ERROR');
		}
		if (!$this->is_registered && ((int) $this->time_limit < (int) constants::SMARTFEED_NO_LIMIT_VALUE) || ((int) $this->time_limit > (int) constants::SMARTFEED_LAST_15_MINUTES_VALUE) )
		{
			$this->errors[] = $this->language->lang('SMARTFEED_LIMIT_FORMAT_ERROR');
		}
		
		// Validate the sort by parameter. If not present or an incorrect value, use the board default sort.
		$this->sort_by = $this->request->variable(constants::SMARTFEED_SORT_BY, constants::SMARTFEED_STANDARD);
		if ( (int) $this->sort_by < constants::SMARTFEED_BOARD || (int) $this->sort_by > constants::SMARTFEED_POSTDATE_DESC)
		{
			$this->errors[] = $this->language->lang('SMARTFEED_SORT_BY_ERROR');
		}

		// Validate the firstpostonly parameter. If not present or an incorrect value, disable it.
		$this->first_post_only = $this->request->variable(constants::SMARTFEED_FIRST_POST, 0);
		if (!in_array((float) $this->first_post_only, $this->true_false_array))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_FIRST_POST_ONLY_ERROR');
		}
		$this->first_post_only = (int) $this->first_post_only;

		// Validate the lastpostonly parameter. If not present or an incorrect value, disable it.
		$this->last_post_only = $this->request->variable(constants::SMARTFEED_LAST_POST, 0);
		if (!in_array((float) $this->last_post_only, $this->true_false_array))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_LAST_POST_ONLY_ERROR');
		}
		$this->last_post_only = (int) $this->last_post_only;

		// Check for max items parameter. It is not required, but if present should be a positive number only. The value must
		// be less than or equal to $this->config['phpbbservices_smartfeed_max_items']. But if 
		// $this->config['phpbbservices_smartfeed_max_items'] == 0 then any positive whole number is allowed.
		// If not present the max items is $this->config['phpbbservices_smartfeed_max_items'] if positive, or unlimited if this value is zero.
		$this->max_items = $this->request->variable(constants::SMARTFEED_MAX_ITEMS, 0);
		if ((int) $this->max_items < 0)
		{
			$this->errors[] = $this->language->lang('SMARTFEED_MAX_ITEMS_ERROR');
		}
		
		$this->max_items = (int) $this->max_items;
		if (($this->config['phpbbservices_smartfeed_max_items'] > 0) && ($this->max_items <> 0))
		{
			$this->max_items = min($this->max_items, $this->config['phpbbservices_smartfeed_max_items']);
		}
		else if (($this->config['phpbbservices_smartfeed_max_items'] > 0) && ($this->max_items == 0))
		{
			$this->max_items = $this->config['phpbbservices_smartfeed_max_items'];
		}

		// Validate the maximum number of words the user wants to see in a post
		$this->max_words = $this->request->variable(constants::SMARTFEED_MAX_WORDS, 0);
		if ((int) $this->max_words < 0)
		{
			$this->errors[] = $this->language->lang('SMARTFEED_MAX_WORD_SIZE_ERROR');
		}
		
		$this->max_words = (int) $this->max_words;
		if (($this->config['phpbbservices_smartfeed_max_word_size'] > 0) && ($this->max_words <> 0))
		{
			$this->max_words = min($this->max_words, $this->config['phpbbservices_smartfeed_max_word_size']);
		}
		else if (($this->config['phpbbservices_smartfeed_max_word_size'] > 0) && ($this->max_words == 0))
		{
			$this->max_words = $this->config['phpbbservices_smartfeed_max_word_size'];
		}

		// Validate the minimum number of words the user wants to see in a post
		$this->min_words = $this->request->variable(constants::SMARTFEED_MIN_WORDS, 0);
		if ((int) $this->min_words < 0)
		{
			$this->errors[] = $this->language->lang('SMARTFEED_MIN_WORD_SIZE_ERROR');
		}

		// Validate the feed style parameter.
		$this->feed_style = $this->request->variable(constants::SMARTFEED_FEED_STYLE, constants::SMARTFEED_HTML);
		if (!($this->feed_style == constants::SMARTFEED_COMPACT || $this->feed_style == constants::SMARTFEED_BASIC || $this->feed_style == constants::SMARTFEED_HTMLSAFE || $this->feed_style == constants::SMARTFEED_HTML))
		{
			$this->errors[] = $this->language->lang('SMARTFEED_STYLE_ERROR', $this->feed_style);
		}

		// Special error checking logic is needed for registered users only
		if ($this->is_registered)
		{

			// If openssl is not compiled with PHP, a user cannot get a feed with posts from non-public forums, so tell the user what to do.
			if (!extension_loaded('openssl'))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_NO_OPENSSL_MODULE');
			}

			//  Validate the remove my posts parameter, if present
			$this->remove_my_posts = $this->request->variable(constants::SMARTFEED_REMOVE_MINE, 0);
			if (!in_array($this->remove_my_posts, $this->true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_REMOVE_MINE_ERROR');
			}

			// Validate the private messages switch
			$this->show_pms = $this->request->variable(constants::SMARTFEED_PRIVATE_MESSAGE, 0);
			if (!in_array($this->show_pms, $this->true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_BAD_PMS_VALUE');
			}

			// Validate the mark read private messages switch
			$this->mark_private_messages = $this->request->variable(constants::SMARTFEED_MARK_PRIVATE_MESSAGES, 0);
			if (!in_array($this->mark_private_messages, $this->true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_BAD_MARK_PRIVATE_MESSAGES_READ_ERROR');
			}
			
			// Validate the bookmark topics only switch
			$this->bookmarks_only = $this->request->variable(constants::SMARTFEED_BOOKMARKS, 0);
			if (!in_array($this->bookmarks_only, $this->true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_BAD_BOOKMARKS_VALUE');
			}
			
			// Validate the filter foes switch
			$this->filter_foes = $this->request->variable(constants::SMARTFEED_FILTER_FOES, 0);
			if (!in_array($this->filter_foes, $this->true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_FILTER_FOES_ERROR');
			}

			// Validate the last visit parameter.
			$this->lastvisit = $this->request->variable(constants::SMARTFEED_SINCE_LAST_VISIT, 0);
			if (!in_array($this->lastvisit, $this->true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_LASTVISIT_ERROR');
			}

			// Validate the suppress forum names parameter.
			$this->suppress_forum_names = $this->request->variable(constants::SMARTFEED_SUPPRESS_FORUM_NAMES, 0);
			if (!in_array($this->suppress_forum_names, $this->true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_SUPPRESS_FORUM_NAMES_ERROR');
			}

			// Validate the suppress topic titles only parameter.
			$this->show_topic_titles = $this->request->variable(constants::SMARTFEED_TOPIC_TITLES, 0);
			if (!in_array($this->show_topic_titles, $this->true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_SHOW_TOPIC_TITLES_ERROR');
			}

			// Validate the suppress usernames parameter.
			$this->suppress_usernames = $this->request->variable(constants::SMARTFEED_USERNAMES, 0);
			if (!in_array($this->suppress_usernames, $this->true_false_array))
			{
				$this->errors[] = $this->language->lang('SMARTFEED_SUPPRESS_USERNAMES_ERROR');
			}

			if (count($this->errors) > 0)
			{
				throw new \Exception(implode("\n", $this->errors));
			}

		}
	}
	
	/**
	* Smartfeed controller for route /smartfeed/{name}
	*
	* @return Response object containing rendered page
	*/
	public function handle()
	{

		$error = false;

		// Load language variable specifically for this class
		$this->language->add_lang(array('feed'), 'phpbbservices/smartfeed');

		// General variables
		$allowed_user_types = array(USER_NORMAL, USER_FOUNDER); // Allowed user types are Normal and Founder. Others (Inactive, Ignore) can only get a public feed.
		$this->board_url = generate_board_url() . '/';

		// Get the user id. The feed may be customized based on a user's privilege. A public user won't be identified as a user in the URL.
		$this->user_id = $this->request->variable(constants::SMARTFEED_USER_ID, ANONYMOUS);
		
		// Get the encrypted password. When decrypted it is still encoded as it shows in the database.
		$this->encrypted_pswd = $this->request->variable(constants::SMARTFEED_ENCRYPTION_KEY, constants::SMARTFEED_NONE, true);

		// Check for incorrect or invalid URL key/value pairs
		try 
		{
			$this->check_for_errors();
		} 
		catch (\Exception $e) 
		{
			$error = true;
		}

		// This construct allows a more elegant way of handling errors by breaking out of the loop if an error occurs.
		// This avoids extended if ($error) conditionals.
		$continue = true;
		while ($continue)
		{

			$sql_ary = array(
				'SELECT'    => 'user_id, user_password, user_smartfeed_key, user_topic_sortby_type, user_topic_sortby_dir, 
					user_post_sortby_type, user_post_sortby_dir, user_lastvisit, user_type',

				'FROM'      => array(
					USERS_TABLE => 'u',
				),

				'WHERE'     =>  'user_id = ' . (int) $this->user_id,
			);

			if ($this->user_id !== ANONYMOUS)
			{
				$sql_ary['WHERE'] .= ' AND ' . $this->db->sql_in_set('user_type', $allowed_user_types); // Robots and inactive members are not allowed to get into restricted forums
			}

			$sql = $this->db->sql_build_query('SELECT', $sql_ary);
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset($result);

			if (count($rowset) == 0)
			{
				$error = true;
				$this->errors[] = $this->language->lang('SMARTFEED_USER_ID_DOES_NOT_EXIST');
				break;
			}

			// Make sure user_id exists in database and has normal or founder status
			$row = reset($rowset);

			// Save the user variables, although these are unneeded for guests.
			$user_smartfeed_key = $row['user_smartfeed_key'];

			// These other variables are only used by registered users
			$user_password = $row['user_password'];
			$user_lastvisit = $row['user_lastvisit'];

			$this->db->sql_freeresult($result); // Query be gone!

			if ($this->is_registered)
			{

				if (strlen($user_smartfeed_key) == 0)
				{
					// If the $user_smartfeed_key is an empty string, the password cannot be decrypted. It's hard to imagine how this could happen
					// unless the feed was called before the user interface was run.
					$error = true;
					$this->errors[] = $this->language->lang('SMARTFEED_BAD_PASSWORD_ERROR', $this->encrypted_pswd, $this->user_id);
					break;
				}

				// Decrypt password using the user_smartfeed_key column in the phpbb_users table. This should have been created
				// the first time the user interface was run by this user. There should not be a clear text password in the database.
				$encoded_pswd = $this->decrypt($this->encrypted_pswd, $user_smartfeed_key);

				// If IP Authentication was enabled, the encoded password is to the left of the ~ and the IP to the right of the ~
				$tilde = strpos($encoded_pswd, '~');
				$client_ip_parts = explode('.', $this->user->ip);    // Client's current IP, based on what the web server recorded.
				if (($tilde == 0) && ($this->config['phpbbservices_smartfeed_require_ip_authentication'] == '1'))
				{
					$error = true;
					$this->errors[] = $this->language->lang('SMARTFEED_IP_AUTH_ERROR');
					break;
				}

				if (!$client_ip_parts || empty($client_ip_parts) || !filter_var($this->user->ip, FILTER_VALIDATE_IP))
				{
					// Something is really odd if the number of address ranges in the client is not 4 or 8!
					$error = true;
					$this->errors[] = $this->language->lang('SMARTFEED_IP_RANGE_ERROR', $this->user->ip);
					break;
				}

				if ($tilde > 0)
				{
					// Since a tilde is present, authenticate the client IP by comparing it with the IP embedded in the "e" parameter
					$authorized_ip = substr($encoded_pswd, $tilde + 1);
					$encoded_pswd = substr($encoded_pswd, 0, $tilde);
				}
				else
				{
					$authorized_ip = $this->user->ip;
				}

				$source_ip_parts = explode('.', $authorized_ip);	// IP range authorized for this user

				// Show error message if requested from incorrect range of IP addresses
				switch (count($client_ip_parts))
				{

					case 4:	 // IPV4
						if (!(
							($client_ip_parts[0] == $source_ip_parts[0]) &&
							($client_ip_parts[1] == $source_ip_parts[1]) &&
							(($client_ip_parts[2] == $source_ip_parts[2]) || ($source_ip_parts[2] == '*'))
						))
						{
							$error = true;
							$this->errors[] = $this->language->lang('SMARTFEED_IP_AUTH_ERROR');
							break 2;
						}
					break;

					case 8:	 // IPV6
						if (!(
							($client_ip_parts[0] == $source_ip_parts[0]) &&
							($client_ip_parts[1] == $source_ip_parts[1]) &&
							($client_ip_parts[2] == $source_ip_parts[2]) &&
							($client_ip_parts[3] == $source_ip_parts[3]) &&
							($client_ip_parts[4] == $source_ip_parts[4]) &&
							($client_ip_parts[5] == $source_ip_parts[5]) &&
							($client_ip_parts[6] == $source_ip_parts[6]) || ($source_ip_parts[6] == '*')
						))
						{
							$error = true;
							$this->errors[] = $this->language->lang('SMARTFEED_IP_AUTH_ERROR');
							break 2;
						}
					break;

					default:	// IP is not formatted correctly or may be absent. This should be caught by the filter_var() call.
						$error = true;
						$this->errors[] = $this->language->lang('SMARTFEED_IP_AUTH_ERROR');
					break 2;

				}

				// Do not generate a feed if the asserted encrypted password does not equal the actual database encrypted password.
				if (trim($encoded_pswd) !== trim($user_password))
				{
					$error = true;
					$this->errors[] = $this->language->lang('SMARTFEED_BAD_PASSWORD_ERROR', $this->encrypted_pswd, $this->user_id);
					break;
				}

			}

			// Logic to limit the range of posts fetched in the feed follows by creating the appropriate SQL qualification

			$start_time = ($this->config['phpbbservices_smartfeed_default_fetch_time_limit'] == 0) ? 0 : time() - ($this->config['phpbbservices_smartfeed_default_fetch_time_limit'] * 60 * 60);

			switch ($this->time_limit)
			{

				case constants::SMARTFEED_NO_LIMIT_VALUE:
					$this->date_limit = $start_time;
				break;

				case constants::SMARTFEED_LAST_QUARTER_VALUE:
					$this->date_limit = max($start_time, time() - (90 * 24 * 60 * 60));
				break;

				case constants::SMARTFEED_LAST_MONTH_VALUE:
					$this->date_limit = max($start_time, time() - (30 * 24 * 60 * 60));
				break;

				case constants::SMARTFEED_LAST_TWO_WEEKS_VALUE:
					$this->date_limit = max($start_time, time() - (14 * 24 * 60 * 60));
				break;

				case constants::SMARTFEED_LAST_WEEK_VALUE:
					$this->date_limit = max($start_time, time() - (7 * 24 * 60 * 60));
				break;

				case constants::SMARTFEED_LAST_DAY_VALUE:
					$this->date_limit = max($start_time, time() - (24 * 60 * 60));
				break;

				case constants::SMARTFEED_LAST_12_HOURS_VALUE:
					$this->date_limit = max($start_time, time() - (12 * 60 * 60));
				break;

				case constants::SMARTFEED_LAST_6_HOURS_VALUE:
					$this->date_limit = max($start_time, time() - (6 * 60 * 60));
				break;

				case constants::SMARTFEED_LAST_3_HOURS_VALUE:
					$this->date_limit = max($start_time, time() - (3 * 60 * 60));
				break;

				case constants::SMARTFEED_LAST_1_HOURS_VALUE:
					$this->date_limit = max($start_time, time() - (60 * 60));
				break;

				case constants::SMARTFEED_LAST_30_MINUTES_VALUE:
					$this->date_limit = max($start_time, time() - (30 * 60));
				break;

				case constants::SMARTFEED_LAST_15_MINUTES_VALUE:
					$this->date_limit = max($start_time, time() - (15 * 60));
				break;

				case constants::SMARTFEED_SINCE_LAST_VISIT_VALUE:
				default:
					$this->date_limit = max($start_time, $user_lastvisit);
				break;

			}

			$date_limit_sql = ' AND p.post_time > ' . $this->date_limit;

			if ($this->is_registered && $this->bookmarks_only)
			{

				// When selecting bookmarked topics only, we can safely ignore the logic constraining the user to read only
				// from certain forums. Instead we will create the SQL to get the bookmarked topics, if any, hijacking the
				// $fetched_forums_str variable since it is convenient.

				$bookmarked_topic_ids = array();

				$sql_ary = array(
					'SELECT'    => 't.topic_id',

					'FROM'      => array(
						USERS_TABLE => 'u',
						BOOKMARKS_TABLE    => 'b',
						TOPICS_TABLE    => 't',
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
					$error = true;
					$this->errors[] = $this->language->lang('SMARTFEED_NO_BOOKMARKS');
					break;
				}

			}
			else
			{

				// Getting a list of allowed forums is now much simpler now that I know about the acl_raw_data_single_user function.

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
					$error = true;
					$this->errors[] = $this->language->lang('SMARTFEED_NO_ACCESSIBLE_FORUMS');
					break;
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
					$error = true;
					$this->errors[] = $this->language->lang('SMARTFEED_NO_FORUMS_ACCESSIBLE');
					break;
				}

			}

			// Create the SQL stub for the sort order
			$order_by_sql = '';
			$user_post_sortby_dir = '';
			$user_post_sortby_type = '';
			$user_topic_sortby_dir = '';
			$user_topic_sortby_type = '';

			switch($this->sort_by)
			{
				case constants::SMARTFEED_BOARD:
					$topic_asc_desc = ($user_topic_sortby_dir == 'd') ? constants::SMARTFEED_DESC : '';
					switch($user_topic_sortby_type)
					{
						case 'a':
							$order_by_sql = "t.topic_first_poster_name $topic_asc_desc, ";
						break;
						case 't':
							$order_by_sql = "t.topic_last_post_time $topic_asc_desc, ";
						break;
						case 'r':
							$order_by_sql = "t.posts_approved $topic_asc_desc, ";
						break;
						case 's':
							$order_by_sql = "t.topic_title $topic_asc_desc, " ;
						break;
						case 'v':
							$order_by_sql = "t.topic_views $topic_asc_desc, ";
						break;
					}
					$post_asc_desc = ($user_post_sortby_dir == 'd') ? constants::SMARTFEED_DESC : '';
					switch($user_post_sortby_type)
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
				case constants::SMARTFEED_STANDARD:
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

			// Create SQL to remove your posts from the feed
			$remove_my_posts_sql = '';
			if ($this->is_registered && ($this->remove_my_posts == 1))
			{
				$remove_my_posts_sql = " AND p.poster_id <> $this->user_id ";
			}

			// Create SQL to remove your foes from the feed
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

			// At last, construct the SQL to return the relevant posts in a feed
			$sql_ary = array(
				'SELECT'	=> 'f.*, t.*, p.*, u.*, tt.mark_time AS topic_mark_time, ft.mark_time AS forum_mark_time',

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
						AND " . ITEM_APPROVED . " 
						AND forum_password = ''
						AND topic_status <> " . ITEM_MOVED,

				'ORDER_BY'	=> $order_by_sql
			);

			$sql_ary['LEFT_JOIN'] = array(
				array(
					'FROM'	=> array(TOPICS_TRACK_TABLE => 'tt'),
					'ON'	=> 't.topic_id = tt.topic_id AND tt.user_id = u.user_id'
				),
				array(
					'FROM'	=> array(FORUMS_TRACK_TABLE => 'ft'),
					'ON'	=> 'f.forum_id = ft.forum_id AND ft.user_id = u.user_id'
				)
			);

			$sql = $this->db->sql_build_query('SELECT', $sql_ary);

			// Now finally, let's fetch the actual posts to be placed in this newsfeed
			$result = $this->db->sql_query_limit($sql, $this->max_items); // Execute the SQL to retrieve the relevant posts. Note, if $this->max_items is 0 then there is no limit on the rows returned
			$rowset = $this->db->sql_fetchrowset($result); // Get all the posts as a set

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

			}
			else
			{
				$pm_result = NULL;
				$pm_rowset = NULL;
			}

			$continue = false;	// Force exit from loop, no more errors to check for
		}

		$display_name = $this->language->lang('SMARTFEED_FEED');	// As XML is generated to create a feed, there is no real page name to display so this is sort of moot.

		// Show the posts as feed items

		$this->assemble_feed($rowset, $pm_rowset, $error);

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

	private function assemble_feed($rowset, $pm_rowset, $error)

	{

		// $allowable_tags used when Safe HTML is wanted for item feed output. Only these tags are allowed for HTML in the feed. Others will be stripped. <br> is not technically Safe HTML but without it paragraphs cannot be discerned, so I allowed it.
		$allowable_tags = '<abbr><accept><accept-charset><accesskey><action><align><alt><axis><border><br><cellpadding><cellspacing><char><charoff><charset><checked><cite><class><clear><cols><colspan><color><compact><coords><datetime><disabled><enctype><for><headers><height><href><hreflang><hspace><id><ismap><label><lang><longdesc><maxlength><media><method><multiple><name><nohref><noshade><nowrap><prompt><readonly><rel><rev><rows><rowspan><rules><scope><selected><shape><size><span><src><start><summary><tabindex><target><title><type><usemap><valign><value><vspace><width>';

		// Get any external newsfeeds URLs
		$ext_feeds = explode("\n", trim($this->config['phpbbservices_smartfeed_external_feeds']));

		// These template variables apply to the overall feed, not to items in it. A post is an item in the newsfeed.
		$this->template->assign_vars(array(
				'L_SMARTFEED_FEED_DESCRIPTION' 		=> html_entity_decode($this->config['site_desc']),
				'L_SMARTFEED_FEED_TITLE' 			=> html_entity_decode($this->config['sitename']),

				'S_SMARTFEED_FEED_LANGUAGE'			=> ($this->config['phpbbservices_smartfeed_rfc1766_lang'] <> '') ? $this->config['phpbbservices_smartfeed_rfc1766_lang'] : $this->config['default_lang'],	// For RSS 2.0 and ATOM 1.0
				'S_SMARTFEED_FEED_PUBDATE'			=> date('r'),	// for RSS 2.0
				'S_SMARTFEED_FEED_TTL' 				=> ($this->config['phpbbservices_smartfeed_ttl'] <> '') ? $this->config['phpbbservices_smartfeed_ttl'] : '60',	// for RSS 2.0
				'S_SMARTFEED_FEED_TYPE' 			=> $this->feed_type,	// Atom 1.0, RSS 1.0, RSS 2.0, used as a switch. Must be 0, 1 or 2. Atom 1.0 is used to show feed type errors if they occur.
				'S_SMARTFEED_FEED_UPDATED'			=> date('c'),	// for Atom and RSS 2.0
				'S_SMARTFEED_FEED_VERSION' 			=> constants::SMARTFEED_VERSION,
				'S_SMARTFEED_SHOW_WEBMASTER'		=> ($this->config['phpbbservices_smartfeed_webmaster'] <> '') ? true : false,	// RSS 2.0

				'U_SMARTFEED_FEED_ID'				=> generate_board_url(),
				'U_SMARTFEED_FEED_IMAGE'			=> ($this->config['phpbbservices_smartfeed_feed_image_path'] <> '') ? generate_board_url() . '/styles/' . trim($this->user->style['style_path']) . '/' . $this->config['phpbbservices_smartfeed_feed_image_path'] : generate_board_url() . '/styles/' . trim($this->user->style['style_path']) . '/theme/images/site_logo.gif', // For RSS 1.0 and 2.0.
				'U_SMARTFEED_FEED_GENERATOR' 		=> constants::SMARTFEED_GENERATOR,
				'U_SMARTFEED_FEED_LINK' 			=> $this->helper->route('phpbbservices_smartfeed_ui_controller', array(), true, false, \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
				'U_SMARTFEED_FEED_PAGE_URL'			=> $this->config['phpbbservices_smartfeed_url'],
				'U_SMARTFEED_WEBMASTER'				=> $this->config['phpbbservices_smartfeed_webmaster'],	// RSS 2.0
			)
		);

		if ($error)
		{
			// Since an error has occurred, generate a feed with just one item in it: the error.
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

			// If there are any unread private messages, publish them first.
			if (isset($pm_rowset))
			{
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

							if ($user_sig !== '')
							{
								$user_sig = generate_text_for_display($user_sig, $row['user_sig_bbcode_uid'], $row['user_sig_bbcode_bitfield'], $flags);
								$user_sig = rtrim($user_sig, '</');	// Bug in generate_text_for_display?
							}

							$message = ($user_sig !== '') ? $message . $this->language->lang('SMARTFEED_POST_SIGNATURE_DELIMITER') . $user_sig : $message;

							$message = str_replace('<img src="./../../', '<img src="' . $this->board_url, $message);
							$message = str_replace('<img class="smilies" src="./../../', '<img class="smilies" src="' . $this->board_url, $message);

							if ($this->feed_style == constants::SMARTFEED_HTMLSAFE)
							{
								$message = strip_tags($message, $allowable_tags);
							}

						}
						else
						{
							// Either Compact or Basic Style wanted
							if ($this->feed_style == constants::SMARTFEED_BASIC)
							{
								$message = ($user_sig !== '') ? $message . "\n\n" . $user_sig : $message;
							}
							strip_bbcode($message); 			// Remove the BBCode
							$message = strip_tags($message, '<br>');	// Gets rid of any embedded HTML except break for formatting
							// Either condense all text or make line feeds explicit
							$message = ($this->feed_style == constants::SMARTFEED_BASIC) ? nl2br($message) : str_replace("\n", ' ', $message);
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
		}

		// If requested to put external items at the top of the feed, do it here.
		if (($this->config['phpbbservices_smartfeed_external_feeds_top'] == 1) &&
			(($this->max_items == 0) || ($this->max_items !== 0 && $this->items_in_feed < $this->max_items)))
		{
			$this->publish_external_feeds($ext_feeds);
		}

		// Loop through the rowset, each row is an item in the feed.
		if (isset($rowset))
		{

			foreach ($rowset as $row)
			{

				// Determine if a new topic
				$new_topic = ($row['topic_time'] > $this->date_limit);

				// Is this topic or forum associated with the post being tracked by this user? If so, exclude the post if the topic track
				// time or forum track time is before the earliest time allowed for a post.
				if (((!is_null($row['forum_mark_time']) && ($row['forum_mark_time']) < $this->date_limit)) ||
					((!is_null($row['topic_mark_time']) && ($row['topic_mark_time']) < $this->date_limit)))
				{
					$include_post = false;
				}
				else
				{
					$include_post = true;
				}

				if 	($include_post &&
					(
						($this->min_words == 0) ||
						($this->min_words !== 0 && $this->truncate_words($row['post_text'], intval($this->max_words), $this->language->lang('SMARTFEED_MAX_WORDS_NOTIFIER'), true) >= $this->min_words)
					)
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
							$title = htmlentities($row['topic_title'], ENT_QUOTES, 'UTF-8');
						}
						else
						{
							$forum_name = ($row['forum_name'] == NULL) ? $this->language->lang('SMARTFEED_GLOBAL_ANNOUNCEMENT') : htmlentities($row['forum_name'], ENT_QUOTES, 'UTF-8');
							$title = $forum_name . $this->language->lang('SMARTFEED_DELIMITER') . htmlentities($row['topic_title'], ENT_QUOTES, 'UTF-8');
						}
					}
					else
					{
						$forum_name = ($row['forum_name'] == NULL) ? $this->language->lang('SMARTFEED_GLOBAL_ANNOUNCEMENT') : htmlentities($row['forum_name'], ENT_QUOTES, 'UTF-8');
						$title = ($this->show_topic_titles) ? $row['topic_title'] : $row['post_subject'];
						if ($title !== '')
						{
							$title = ($this->config['phpbbservices_smartfeed_suppress_forum_names'] || $this->suppress_forum_names) ? htmlentities($title, ENT_QUOTES, 'UTF-8') : $forum_name . $this->language->lang('SMARTFEED_DELIMITER') . htmlentities($title, ENT_QUOTES, 'UTF-8');
						}
						else
						{
							if (!$this->show_topic_titles)
							{
								$title = ($this->config['phpbbservices_smartfeed_suppress_forum_names'] || $this->suppress_forum_names) ? 'Re: ' . htmlentities($title, ENT_QUOTES, 'UTF-8') : $forum_name . $this->language->lang('SMARTFEED_DELIMITER') . 'Re: ' . htmlentities($title, ENT_QUOTES, 'UTF-8');

							}
							else
							{
								$title = ($this->config['phpbbservices_smartfeed_suppress_forum_names'] || $this->suppress_forum_names) ? htmlentities($title, ENT_QUOTES, 'UTF-8') : $forum_name . $this->language->lang('SMARTFEED_DELIMITER') . htmlentities($title, ENT_QUOTES, 'UTF-8');
							}
						}
						$title = html_entity_decode($title);

						if ($row['topic_first_post_id'] !== $row['post_id'])
						{
							if ($this->config['phpbbservices_smartfeed_show_username_in_replies'] && !$this->suppress_usernames)
							{
								$title .= ($row['username'] == '') ? $this->language->lang('SMARTFEED_DELIMITER') . $this->language->lang('SMARTFEED_REPLY_BY') . ' ' . $this->language->lang('GUEST') . ' ' . $username : $this->language->lang('SMARTFEED_DELIMITER') . $this->language->lang('SMARTFEED_REPLY_BY') . ' ' . $username;
							}
						}
						else
						{
							if ($this->config['phpbbservices_smartfeed_show_username_in_first_topic_post'] && !$this->suppress_usernames)
							{
								$title .= $this->language->lang('SMARTFEED_DELIMITER') . $this->language->lang('AUTHOR') . ' ' . $username;
							}
						}
					}

					$title = html_entity_decode(censor_text($title));

					$link = htmlentities($this->board_url . 'viewtopic.' . $this->phpEx . '?f=' . $row['forum_id'] . '&t=' . $row['topic_id'] . '&p=' . $row['post_id']  . '#p' . $row['post_id'], ENT_QUOTES, 'UTF-8');
					$category = html_entity_decode($row['forum_name']);

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
						if ($new_topic)
						{
							$post_text = $this->language->lang('SMARTFEED_NEW_TOPIC_NOTIFICATION');
						}
						else
						{
							$post_text = $this->language->lang('SMARTFEED_NEW_POST_NOTIFICATION');
						}
					}
					else
					{
						$flags = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
							(($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) +
							(($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);

						$post_text = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $flags);
						$post_text = rtrim($post_text, '</');	// Bug in generate_text_for_display?
						$post_text = censor_text($post_text);

						$user_sig = ( $row['enable_sig'] && $row['user_sig'] !== '' && $this->config['allow_sig'] && (!($this->config['phpbbservices_smartfeed_privacy_mode']) || $this->is_registered) ) ? censor_text($row['user_sig']) : '';

						if (($this->feed_style == constants::SMARTFEED_HTML) || ($this->feed_style == constants::SMARTFEED_HTMLSAFE))
						{

							// If there is an image, show it. If there is a file, link to the attachment
							if ($row['post_attachment'] > 0)
							{
								$post_text .= $this->create_attachment_markup ($row['post_id'], true);
							}

							if ($user_sig !== '')
							{
								$user_sig = generate_text_for_display($user_sig, $row['user_sig_bbcode_uid'], $row['user_sig_bbcode_bitfield'], $flags);
								$user_sig = rtrim($user_sig, '</');	// Bug in generate_text_for_display?
							}

							$post_text = ($user_sig !== '') ? $post_text . $this->language->lang('SMARTFEED_POST_SIGNATURE_DELIMITER') . $user_sig : $post_text;

							$post_text = str_replace('<img src="./../../', '<img src="' . $this->board_url, $post_text);
							$post_text = str_replace('<img class="smilies" src="./../../', '<img class="smilies" src="' . $this->board_url, $post_text);

							if ($this->feed_style == constants::SMARTFEED_HTMLSAFE)
							{
								$post_text = strip_tags($post_text, $allowable_tags);
							}

						}
						else
						{
							// Either Compact or Basic Style wanted
							if ($this->feed_style == constants::SMARTFEED_BASIC)
							{
								$post_text = ($user_sig !== '') ? $post_text . "\n\n" . $user_sig : $post_text;
							}
							strip_bbcode($post_text); 			// Remove the BBCode
							$post_text = strip_tags($post_text, '<br>');	// Gets rid of any embedded HTML
							// Either condense all text or make line feeds explicit
							$post_text = ($this->feed_style == constants::SMARTFEED_BASIC) ? nl2br($post_text) : str_replace("\n", ' ', $post_text);
						}

						// Handle the maximum number of words to display in a post.
						if ($this->config['phpbbservices_smartfeed_max_word_size'] > 0 && $this->max_words > 0)
						{
							$post_text = $this->truncate_words($post_text, min($this->config['phpbbservices_smartfeed_max_word_size'],$this->max_words), $this->language->lang('SMARTFEED_MAX_WORDS_NOTIFIER'));
						}
						else if ($this->config['phpbbservices_smartfeed_max_word_size'] > 0 && $this->max_words == 0)
						{
							$post_text = $this->truncate_words($post_text, $this->config['phpbbservices_smartfeed_max_word_size'], $this->language->lang('SMARTFEED_MAX_WORDS_NOTIFIER'));
						}
						else if ($this->max_words > 0)
						{
							$post_text = $this->truncate_words($post_text, intval($this->max_words), $this->language->lang('SMARTFEED_MAX_WORDS_NOTIFIER'));
						}
					}

					// Add the item (post) to the feed

					if (($this->max_items == 0) || ($this->max_items !== 0 && $this->items_in_feed < $this->max_items))
					{
						$this->items_in_feed++;

						$title = html_entity_decode(censor_text($title));

						$this->template->assign_block_vars('items', array(

							// Common and Atom 1.0 block variables follow
							'L_CATEGORY'  => $category,
							'L_CONTENT'   => $post_text,
							'L_EMAIL'     => $email,
							'L_NAME'      => $username,
							'L_SUMMARY'   => $post_text,
							'L_TITLE'     => $title,
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

		// If requested to put external items at the bottom of the feed, do it here.
		if (($this->config['phpbbservices_smartfeed_external_feeds_top'] == 0) &&
			(($this->max_items == 0) || ($this->max_items !== 0 && $this->items_in_feed < $this->max_items)))
		{
			$this->publish_external_feeds($ext_feeds);
		}

	}
	
	private function create_attachment_markup ($item_id, $is_post = true)
	{
		
		// Both posts and private messages can have attachments. The code for attaching these attachments to feed items is pretty much identical. Only
		// the source of the data differs (from a post or private message). Consequently it makes sense to have one function.

		static $my_styles;
		
		$attachment_markup = sprintf("<div class=\"box\">\n<p>%s</p>\n", $this->language->lang('ATTACHMENTS'));
		
		// Get all attachments
		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE post_msg_id = ' . (int) $item_id . ' AND in_message = ';
		$sql .= ($is_post) ? '0' : '1';
		$sql .= ' ORDER BY attach_id';

		// Find the first occurrence of icon_topic_attach.gif in the user's styles. Most styles use the image from the parent style but there's no way to know
		// for sure. We want to present the image "closest" to the user's preferred style. As a practical matter it's unlikely that anything other than prosilver's
		// image will be used.
		$icon_topic_attach_style = '';
		if (!isset($my_styles))
		{
			$my_styles = $this->template->get_user_style();
			
			$found_image = false;
			foreach ($my_styles as $this_style)
			{
				if (file_exists($this->phpbb_root_path . 'styles/' . $this_style . '/theme/images/icon_topic_attach.gif'))
				{
					$icon_topic_attach_style = $this_style;
					$found_image = true;
					break;
				}
			}
			if (!$found_image)
			{
				// This should not happen but if it does assume prosilver is installed and can be used
				$icon_topic_attach_style = 'prosilver';
			}
		}
		
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
				$is_thumbnail = ($row['thumbnail'] == 1) ? true : false;
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
					sprintf("<img src=\"%s\" title=\"\" alt=\"\" /> ", 
						generate_board_url() . '/styles/' . $icon_topic_attach_style . '/theme/images/icon_topic_attach.gif') .
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

		// Thanks to klapray for this logic for creating a "urlsafe" fix for base64_encode and _decode.
		$data_input = base64_decode(strtr($data_input, '-_.', '+/='));

		// Get the IV so it can be decrypted with the $key
		$iv = substr($data_input, 0, openssl_cipher_iv_length('AES-128-CBC'));

		// Encrypted data starts after the IV portion of the string
		$encrypted_data = substr($data_input, openssl_cipher_iv_length('AES-128-CBC'));
		$decrypted_data = openssl_decrypt($encrypted_data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

		return $decrypted_data;

	}
	
	private function truncate_words($text, $max_words, $max_words_lang_string, $just_count_words = false)
	{
	
		// This function returns the first $max_words from the supplied $text. If $just_count_words === true, a word count is returned. Note:
		// for consistency, HTML is stripped. This can be annoying, but otherwise HTML rendered in the feed may not be valid.
		
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

	private function publish_external_feeds($feeds)
	{

		// If there are external feeds, publish one at a time
		if (is_array($feeds))
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

						$feed_title = $feed->get_title();

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
										'L_TITLE'     => $this->language->lang('SMARTFEED_EXTERNAL_ITEM') . $this->language->lang('SMARTFEED_DELIMITER') . html_entity_decode($feed_title) . $this->language->lang('SMARTFEED_DELIMITER') . html_entity_decode(censor_text($title)),
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

		return true;

	}

}
