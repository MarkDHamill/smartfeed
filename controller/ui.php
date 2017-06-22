<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2017 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbservices\smartfeed\controller;

use phpbbservices\smartfeed\constants\constants;

class ui
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;
	
	protected $phpEx;

	/* @var \phpbb\db\driver\factory  */
	protected $db;

	/* @var \phpbb\auth\auth */
	protected $auth;

	protected $phpbb_root_path; // Only used in functions.
	
	protected $common;

	/**
	* Constructor
	*
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param \phpbb\template\template				$template
	* @param \phpbb\user							$user
	* @param string									$php_ext
	* @param \phpbb\db\driver\factory				$db
	* @param \phpbb\auth\auth						$auth
	* @param string									$phpbb_root_path
	* @param \phpbbservices\smartfeed\core\common	$common
	* @param string           						$ext_root_path     Path to smartfeed extension root
	*/
	
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user,
		$php_ext, \phpbb\db\driver\factory $db, \phpbb\auth\auth $auth, $phpbb_root_path, \phpbbservices\smartfeed\core\common $common, $ext_root_path)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->phpEx = $php_ext;
		$this->db = $db;
		$this->auth = $auth;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->common = $common;
		$this->ext_root_path = $ext_root_path;

		// Load language variable specifically for this class
		$this->user->add_lang_ext('phpbbservices/smartfeed', 'ui');
	}

	/**
	* Smartfeed controller for route /smartfeed/{name}
	*
	* @return Response object containing rendered page
	*/
	public function handle()
	{

		$display_name = $this->user->lang('SMARTFEED_TITLE');
		
		// Smartfeed cannot be used with Apache authentication unless the .htaccess file is modified to allow smartfeed.php to bypass
		// Apache authentication. If you have made these changes then set the constant SMARTFEED_APACHE_HTACCESS_ENABLED to true in the ACP interface.
		if (($this->config['auth_method'] == 'apache') && ($this->config['phpbbservices_smartfeed_apache_htaccess_enabled'] != 1))
		{
			$msg_text = ($this->user->data['user_type'] == USER_FOUNDER) ? $this->user->lang('SMARTFEED_APACHE_AUTHENTICATION_WARNING_ADMIN') : $this->user->lang('SMARTFEED_APACHE_AUTHENTICATION_WARNING_REG');
			trigger_error($msg_text, E_USER_NOTICE);
		}
		
		// Create a list of required and excluded forum_ids
		$required_forum_ids = (isset($this->config['phpbbservices_smartfeed_include_forums']) && strlen(trim($this->config['phpbbservices_smartfeed_include_forums'])) > 0) ? explode(',', $this->config['phpbbservices_smartfeed_include_forums']) : array();
		$excluded_forum_ids = (isset($this->config['phpbbservices_smartfeed_exclude_forums']) && strlen(trim($this->config['phpbbservices_smartfeed_exclude_forums'])) > 0) ? explode(',', $this->config['phpbbservices_smartfeed_exclude_forums']) : array();

		// Pass encryption tokens to the user interface for generating URLs, unless the user is not registered, openssl is not supported or OAuth authentication is used
		$is_guest = !$this->user->data['is_registered'] || !extension_loaded('openssl') || $this->config['auth_method'] == 'oauth';
		
		if (!$is_guest)
		{
			// If the user is registered then great, they can authenticate and see private forums

			$smartfeed_user_id = $this->user->data['user_id'];
			$user_password = $this->user->data['user_password'];
			if ($this->user->data['user_smartfeed_key'])
			{
				$user_smartfeed_key = $this->user->data['user_smartfeed_key'];
			}
			else
			{
				// Generate a Smartfeed encryption key. This is a one time action. It is used to authenticate the user when they call smartfeed.php.
				$user_smartfeed_key = gen_rand_string(32);

				// Store the key
				$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_smartfeed_key = '" . $this->db->sql_escape($user_smartfeed_key) . "'
						WHERE user_id = " . (int) $this->user->data['user_id'];
				$this->db->sql_query($sql);
			}
			$encrypted_password = $this->encrypt($user_password, $user_smartfeed_key);
			$encrypted_password_with_ip = $this->encrypt($user_password . '~' . $this->user->ip, $user_smartfeed_key);
			$this->template->assign_vars(array('S_SMARTFEED_IS_GUEST' => false, 'S_SMARTFEED_DAY_DEFAULT' => ''));
		}
		else
		{
			// Public (anonymous) users do not need to authenticate so no encrypted passwords are needed
			$smartfeed_user_id = ANONYMOUS;
			$encrypted_password = 'NONE';
			$encrypted_password_with_ip = 'NONE';
			$this->template->assign_vars(array('S_SMARTFEED_IS_GUEST' => true, 'S_SMARTFEED_DAY_DEFAULT' => 'selected="selected"'));
		}

		$allowed_forum_ids = array();
		$forum_read_ary = array();
		
		// Get forum read permissions for this user. They are also usually stored in the user_permissions column, but sometimes the field is empty. This always works.
		$forum_array = $this->auth->acl_raw_data_single_user($smartfeed_user_id);
		
		foreach ($forum_array as $key => $value)
		{
			foreach ($value as $auth_option_id => $auth_setting)
			{
				if ($this->auth->acl_get('f_read', $key))
				{
					$forum_read_ary[$key]['f_read'] = 1;
				}
				if ($this->auth->acl_get('f_list', $key))
				{
					$forum_read_ary[$key]['f_list'] = 1;
				}
			}
		}

		// Get a list of parent_ids for each forum and put them in an array.
		$parent_array = array();
		$sql = 'SELECT forum_id, parent_id 
			FROM ' . FORUMS_TABLE . '
			ORDER BY forum_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$parent_array[$row['forum_id']] = $row['parent_id'];
		}
		$this->db->sql_freeresult($result);

		if (sizeof($forum_read_ary) > 0) // This should avoid a PHP Notice
		{
			foreach ($forum_read_ary as $forum_id => $allowed)
			{
				if ($this->auth->acl_get('f_read', $forum_id) && $this->auth->acl_get('f_list', $forum_id) && $this->common->check_all_parents($parent_array, $forum_id))
				{
					// Since this user has read access to this forum, add it to the $allowed_forum_ids array
					$allowed_forum_ids[] = (int) $forum_id;
					
					// Also add to $allowed_forum_ids the parents, if any, of this forum. Actually we have to find the parent's parents, etc., going up as far as necessary because 
					// $this->auth->act_getf does not return the parents for which the user has access, yet parents must be shown are in the user interface
					$there_are_parents = true;
					$this_forum_id = (int) $forum_id;
					
					while ($there_are_parents)
					{
						if ($parent_array[$this_forum_id] == 0)
						{
							$there_are_parents = false;
						}
						else
						{
							// Do not add this parent to the list of allowed forums if it is already in the array
							if (!in_array((int) $parent_array[$this_forum_id], $allowed_forum_ids))
							{
								$allowed_forum_ids[] = (int) $parent_array[$this_forum_id];
							} 
							$this_forum_id = (int) $parent_array[$this_forum_id];	// Keep looping...
						}
					}
				}
			}
		}

		// Get a list of forums as they appear on the main index for this user. For presentation purposes indent them so they show the natural phpBB3 hierarchy.
		// Indenting is cleverly handled by nesting <div> tags inside of other <div> tags, and the template defines the relative offset (20 pixels).

		$no_forums = false;
		
		if (sizeof($allowed_forum_ids) > 0)
		{
			
			$sql = 'SELECT forum_name, forum_id, parent_id, forum_type
					FROM ' . FORUMS_TABLE . ' 
					WHERE ' . $this->db->sql_in_set('forum_id', $allowed_forum_ids) . ' AND forum_type <> ' . FORUM_LINK . '
					ORDER BY left_id ASC';
			$result = $this->db->sql_query($sql);
			
			$this->template->assign_block_vars('show_forums', array());
			
			$current_level = 0;			// How deeply nested are we at the moment
			$parent_stack = array();	// Holds a stack showing the current parent_id of the forum
			$parent_stack[] = 0;		// 0, the first value in the stack, represents the <div_0> element, a container holding all the categories and forums in the template
			
			while ($row = $this->db->sql_fetchrow($result))
			{
			
				if ((int) $row['parent_id'] != (int) end($parent_stack) || (end($parent_stack) == 0))
				{
					if (in_array($row['parent_id'], $parent_stack))
					{
						// If parent is in the stack, then pop the stack until the parent is found, otherwise push stack adding the current parent. This creates a </div>
						while ((int) $row['parent_id'] != (int) end($parent_stack))
						{
							array_pop($parent_stack);
							$current_level--;
							// Need to close a category level here
							$this->template->assign_block_vars('forums', array( 
								'S_SMARTFEED_DIV_OPEN' => false,
								'S_SMARTFEED_PRINT' => false));
						}
					}
					else
					{
						// If the parent is not in the stack, then push the parent_id on the stack. This is also a trigger to indent the block. This creates a <div>
						array_push($parent_stack, (int) $row['parent_id']);
						$current_level++;
						// Need to add a category level here
						$this->template->assign_block_vars('forums', array( 
							'S_SMARTFEED_DIV_OPEN' => true,
							'CAT_ID' => 'div_' . $row['parent_id'],
							'S_SMARTFEED_PRINT' => false));
					}
				}
				
				// This section contains logic to handle forums that are either required or excluded by the Administrator
				
				// Is the forum either required or excluded from Smartfeed?
				$required_forum = (in_array((int) $row['forum_id'], $required_forum_ids)) ? true : false;
				$excluded_forum = (in_array((int) $row['forum_id'], $excluded_forum_ids)) ? true : false;
				$forum_disabled = $required_forum || $excluded_forum;
				
				// Markup to visually show required or excluded forums
				if ($required_forum)
				{
					$prefix = '<strong>';
					$suffix = '</strong>';
				}
				else
				{
					if ($excluded_forum)
					{
						$prefix = '<span style="text-decoration:line-through">';
						$suffix = '</span>';
					}
					else
					{
						$prefix = '';
						$suffix = '';
					}
				}
				
				// Markup to indicate whether the checkbox for the forum should be checked or not
				$forum_checked = ($this->config['phpbbservices_smartfeed_all_by_default'] == '1');
				if ($required_forum)
				{
					$forum_checked = true;
				}
				if ($excluded_forum)
				{
					$forum_checked = false;
				}
				
				$element_prefix = ($required_forum || $excluded_forum) ? 'xlt_' : 'elt_'; // 'xlt_' will exclude the element from the check/uncheck form feature
				
				// This code prints the forum or category, which will exist inside the previously created <div> block
				$this->template->assign_block_vars('forums', array( 
					'FORUM_NAME' => $element_prefix . (int) $row['forum_id'] . '_' . (int) $row['parent_id'],
					'FORUM_PREFIX' => $prefix,
					'FORUM_LABEL' => $row['forum_name'],
					'FORUM_SUFFIX' => $suffix,
					'FORUM_DISABLED' => ($forum_disabled) ? 'disabled="disabled"' : '',
					'FORUM_CHECKED' => ($forum_checked) ? 'checked="checked"' : '',
					'S_SMARTFEED_PRINT' => true,
					'S_SMARTFEED_IS_FORUM' => ($row['forum_type'] == FORUM_CAT) ? false : true));	// Switch to display a category different than a forum
				
			}
		
			$this->db->sql_freeresult($result);
			
			// Now out of the loop, it is important to remember to close any open <div> tags. Typically there is at least one.
			while ((int) $row['parent_id'] != (int) end($parent_stack))
			{
				array_pop($parent_stack);
				$current_level--;
				// Need to close the <div> tag
				$this->template->assign_block_vars('forums', array( 
					'S_SMARTFEED_DIV_OPEN' => false,
					'S_SMARTFEED_PRINT' => false));
			}
			
		}
		else
		{
			$no_forums = true;
		}

		// For IPV6 testing, if no IPV6 IP is available, uncomment the following line to test:
		// $this->user->ip = '2001:0DB8:AC10:FE01:0000:0000:0000:0000';

		// Set up text for the IP authentication explanation string
		$smartfeed_ip_auth_explain = sprintf($this->user->lang('SMARTFEED_IP_AUTHENTICATION_EXPLAIN'), $this->user->ip);
		$max_items = ($this->config['phpbbservices_smartfeed_max_items'] == '0') ? 0 : 1;
		$size_error_msg = $this->user->lang('SMARTFEED_SIZE_ERROR', $this->config['phpbbservices_smartfeed_max_items'], 0);

		// Set the template variables needed to generate a URL for Smartfeed. Note: most can be handled by template language variable substitution.
		$this->template->assign_vars(array(
		
			'L_POWERED_BY'						=> sprintf($this->user->lang('POWERED_BY'), '<a href="' . $this->config['phpbbservices_smartfeed_url'] . '" class="postlink" onclick="window.open(this.href);return false;">' . $this->user->lang('SMARTFEED_POWERED_BY') . '</a>'),
			'L_SMARTFEED_EXCLUDED_FORUMS'		=> implode(",", $excluded_forum_ids),
			'L_SMARTFEED_IGNORED_FORUMS'		=> implode(",", array_merge($required_forum_ids, $excluded_forum_ids)),
			'L_SMARTFEED_IP_AUTHENTICATION_EXPLAIN'	=> $smartfeed_ip_auth_explain,
			'L_SMARTFEED_LIMIT_SET_EXPLAIN'		=> ($this->config['phpbbservices_smartfeed_default_fetch_time_limit'] == '0') ? '' : sprintf($this->user->lang('SMARTFEED_LIMIT_SET_EXPLAIN'), round(($this->config['phpbbservices_smartfeed_default_fetch_time_limit']/24), 0)),
			'L_SMARTFEED_MAX_ITEMS_EXPLAIN_MAX' => ($this->config['phpbbservices_smartfeed_max_items'] == 0) ? $this->user->lang('SMARTFEED_MAX_ITEMS_EXPLAIN_BLANK') : sprintf($this->user->lang('SMARTFEED_MAX_ITEMS_EXPLAIN'), $this->config['phpbbservices_smartfeed_max_items'], $max_items),
			'L_SMARTFEED_MAX_WORD_SIZE_EXPLAIN' => ($this->config['phpbbservices_smartfeed_max_word_size'] == '0') ? $this->user->lang('SMARTFEED_MAX_WORD_SIZE_EXPLAIN_BLANK') : sprintf($this->user->lang('SMARTFEED_MAX_WORD_SIZE_EXPLAIN'), $this->config['phpbbservices_smartfeed_max_word_size']),
			'L_SMARTFEED_NOT_LOGGED_IN'			=> !extension_loaded('openssl') ? $this->user->lang('SMARTFEED_NO_OPENSSL_SUPPORT') : sprintf($this->user->lang('SMARTFEED_NOT_LOGGED_IN'), $this->phpEx, $this->phpEx),
			'LA_SMARTFEED_SIZE_ERROR'			=> $size_error_msg,
			'S_SMARTFEED_ALL_BY_DEFAULT'		=> ($this->config['phpbbservices_smartfeed_all_by_default'] == '1') ? 'checked="checked"' : '',
			'S_SMARTFEED_ATOM_10_VALUE'			=> constants::SMARTFEED_ATOM,
			'S_SMARTFEED_AUTO_ADVERTISE_FEED'	=> $this->config['phpbbservices_smartfeed_auto_advertise_public_feed'],  // can this be done here for all pages?
			'S_SMARTFEED_BASIC_VALUE'			=> constants::SMARTFEED_BASIC,
			'S_SMARTFEED_BOARD'					=> constants::SMARTFEED_BOARD,
			'S_SMARTFEED_BOOKMARKS' 			=> constants::SMARTFEED_BOOKMARKS,
			'S_SMARTFEED_COMPACT_VALUE'			=> constants::SMARTFEED_COMPACT,
			'S_SMARTFEED_ENCRYPTION_KEY' 		=> constants::SMARTFEED_ENCRYPTION_KEY,
			'S_SMARTFEED_FEED_STYLE' 			=> constants::SMARTFEED_FEED_STYLE,
			'S_SMARTFEED_FEED_TYPE' 			=> constants::SMARTFEED_FEED_TYPE,
			'S_SMARTFEED_FILTER_FOES' 			=> constants::SMARTFEED_FILTER_FOES, 
			'S_SMARTFEED_FIRST_POST' 			=> constants::SMARTFEED_FIRST_POST,
			'S_SMARTFEED_FORUMS' 				=> constants::SMARTFEED_FORUMS,
			'S_SMARTFEED_HTML_VALUE'			=> constants::SMARTFEED_HTML,
			'S_SMARTFEED_HTMLSAFE_VALUE'		=> constants::SMARTFEED_HTMLSAFE,
			'S_SMARTFEED_IN_SMARTFEED' 			=> true,	// Suppress inclusion of Smartfeed Javascript if not in Smartfeed user interface
			'S_SMARTFEED_IS_GUEST' 				=> $is_guest,
			'S_SMARTFEED_LAST_QUARTER_VALUE'	=> constants::SMARTFEED_LAST_QUARTER_VALUE,
			'S_SMARTFEED_LAST_MONTH_VALUE'		=> constants::SMARTFEED_LAST_MONTH_VALUE,
			'S_SMARTFEED_LAST_TWO_WEEKS_VALUE'	=> constants::SMARTFEED_LAST_TWO_WEEKS_VALUE,
			'S_SMARTFEED_LAST_WEEK_VALUE'		=> constants::SMARTFEED_LAST_WEEK_VALUE,
			'S_SMARTFEED_LAST_DAY_VALUE'		=> constants::SMARTFEED_LAST_DAY_VALUE,
			'S_SMARTFEED_LAST_12_HOURS_VALUE'	=> constants::SMARTFEED_LAST_12_HOURS_VALUE,
			'S_SMARTFEED_LAST_6_HOURS_VALUE'	=> constants::SMARTFEED_LAST_6_HOURS_VALUE,
			'S_SMARTFEED_LAST_3_HOURS_VALUE'	=> constants::SMARTFEED_LAST_3_HOURS_VALUE,
			'S_SMARTFEED_LAST_1_HOURS_VALUE'	=> constants::SMARTFEED_LAST_1_HOURS_VALUE,
			'S_SMARTFEED_LAST_30_MINUTES_VALUE'	=> constants::SMARTFEED_LAST_30_MINUTES_VALUE,
			'S_SMARTFEED_LAST_15_MINUTES_VALUE'	=> constants::SMARTFEED_LAST_15_MINUTES_VALUE,
			'S_SMARTFEED_MARK_PRIVATE_MESSAGES' => constants::SMARTFEED_MARK_PRIVATE_MESSAGES,
			'S_SMARTFEED_MAX_ITEMS'				=> $this->config['phpbbservices_smartfeed_max_items'], // was count_limit, now max_items
			'S_SMARTFEED_MAX_ITEMS_L' 			=> constants::SMARTFEED_MAX_ITEMS,
			'S_SMARTFEED_MAX_WORD_SIZE'			=> $this->config['phpbbservices_smartfeed_max_word_size'], // max_word_size
			'S_SMARTFEED_MAX_WORDS' 			=> constants::SMARTFEED_MAX_WORDS,
			'S_SMARTFEED_MIN_WORDS' 			=> constants::SMARTFEED_MIN_WORDS,
			'S_SMARTFEED_NO_FORUMS'				=> $no_forums,
			'S_SMARTFEED_NO_LIMIT_VALUE' 		=> constants::SMARTFEED_NO_LIMIT_VALUE, $this->config['phpbbservices_smartfeed_url'],
			'S_SMARTFEED_POSTDATE_ASCENDING'	=> constants::SMARTFEED_POSTDATE,
			'S_SMARTFEED_POSTDATE_DESCENDING'	=> constants::SMARTFEED_POSTDATE_DESC,
			'S_SMARTFEED_PRIVATE_MESSAGE' 		=> constants::SMARTFEED_PRIVATE_MESSAGE,
			'S_SMARTFEED_PWD'					=> $encrypted_password, 
			'S_SMARTFEED_PWD_WITH_IP'			=> $encrypted_password_with_ip, 
			'S_SMARTFEED_REMOVE_MINE' 			=> constants::SMARTFEED_REMOVE_MINE,
			'S_SMARTFEED_REQUIRED_FORUMS'		=> (sizeof($required_forum_ids) > 0) ? 'true' : 'false',
			'S_SMARTFEED_REQUIRED_IP_AUTHENTICATION'	=> $this->config['phpbbservices_smartfeed_require_ip_authentication'],
			'S_SMARTFEED_RSS_10_VALUE'			=> constants::SMARTFEED_RSS1,
			'S_SMARTFEED_RSS_20_VALUE'			=> constants::SMARTFEED_RSS2,
			'S_SMARTFEED_SINCE_LAST_VISIT'		=> constants::SMARTFEED_SINCE_LAST_VISIT,
			'S_SMARTFEED_SINCE_LAST_VISIT_VALUE'	=> constants::SMARTFEED_SINCE_LAST_VISIT_VALUE,
			'S_SMARTFEED_SORT_BY' 				=> constants::SMARTFEED_SORT_BY,
			'S_SMARTFEED_STANDARD'				=> constants::SMARTFEED_STANDARD,
			'S_SMARTFEED_STANDARD_DESC'			=> constants::SMARTFEED_STANDARD_DESC,
			'S_SMARTFEED_TIME_LIMIT' 			=> constants::SMARTFEED_TIME_LIMIT,
			'S_SMARTFEED_USER_ID' 				=> constants::SMARTFEED_USER_ID,
			'U_SMARTFEED_IMAGE_PATH'         	=> generate_board_url() . $this->ext_root_path . 'styles/all/theme/images/',
		 	'UA_SMARTFEED_SITE_URL'				=> generate_board_url() . '/app.' . $this->phpEx . '/smartfeed/',
			'UA_SMARTFEED_USER_ID'				=> $smartfeed_user_id,

			)
		);
				
		return $this->helper->render('smartfeed_body.html', $display_name);
	
	}

	private function encrypt($data_input, $key)
	{

		// This function encrypts $data_input (the encrypted user_password for the user in the phpbb_users table) using
		// $key (user_smartfeed_key in the phpbb_users table), the AES-128-CBC algorithm and a randomly generated IV.

		// Generate an initialization vector needed to properly encrypt the password
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-128-CBC'));

		// Encrypt the data using the random IV.
		$encrypted_string = openssl_encrypt($data_input, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

		// Thanks to phpBB forum user klapray for this logic for creating a "urlsafe" fix for base64_encode and _decode.
		$encrypted_data = strtr(base64_encode($iv . $encrypted_string), '+/=', '-_.');
		return $encrypted_data;

	}
	
}
