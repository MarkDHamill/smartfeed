<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2017 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbservices\smartfeed\acp;

class main_module
{

	private $config;
	private $phpbb_log;
	private $request;
	private $template;
	private $user;

	var $u_action;

	function __construct()
	{
		global $phpbb_container;

		// Encapsulate certain phpBB objects inside this class to minimize security issues
		$this->config = $phpbb_container->get('config');
		$this->phpbb_log = $phpbb_container->get('log');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');
	}

	function main($id, $mode)
	{

		$this->user->add_lang_ext('phpbbservices/smartfeed', 'info_acp_common');

		$submit = $this->request->is_set_post('submit');

		$form_key = 'phpbbservices/smartfeed';
		add_form_key($form_key);

		/**
		*	Validation types are:
		*		string, int, bool,
		*		script_path (absolute path in url - beginning with / and no trailing slash),
		*		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		*/
		switch ($mode)
		{
			case 'ppt':
				$display_vars = array(
					'title'	=> 'ACP_SMARTFEED_PPT',
					'vars'	=> array(
						'legend1'											=> 'GENERAL_SETTINGS',
						'phpbbservices_smartfeed_max_items'					=> array('lang' => 'ACP_SMARTFEED_MAX_ITEMS',							'validate' => 'int:0',	'type' => 'text:5:5', 'explain' => true),
						'phpbbservices_smartfeed_default_fetch_time_limit'	=> array('lang' => 'ACP_SMARTFEED_DEFAULT_FETCH_TIME_LIMIT',			'validate' => 'int:0',	'type' => 'text:5:5', 'explain' => true, 'append' 				=> ' ' . $this->user->lang('ACP_SMARTFEED_HOURS')),
						'phpbbservices_smartfeed_max_word_size'				=> array('lang' => 'ACP_SMARTFEED_MAX_WORD_SIZE',							'validate' => 'int:0',	'type' => 'text:5:5', 'explain' => true),
						'phpbbservices_smartfeed_ttl'						=> array('lang' => 'ACP_SMARTFEED_TTL',									'validate' => 'int:0',	'type' => 'text:4:4', 'explain' => true, 'append' => ' ' . $this->user->lang('ACP_SMARTFEED_MINUTES')),
					)
				);
			break;
				
			case 'security':
				$display_vars = array(
					'title'	=> 'ACP_SMARTFEED_SECURITY',
					'vars'	=> array(						
						'legend1'													=> 'GENERAL_OPTIONS',
						'phpbbservices_smartfeed_require_ip_authentication'			=> array('lang' => 'ACP_SMARTFEED_REQUIRE_IP_AUTHENTICATION',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'phpbbservices_smartfeed_auto_advertise_public_feed'		=> array('lang' => 'ACP_SMARTFEED_AUTO_ADVERTISE_PUBLIC_FEED',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'phpbbservices_smartfeed_privacy_mode'						=> array('lang' => 'ACP_SMARTFEED_PRIVACY_MODE',						'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'phpbbservices_smartfeed_show_username_in_first_topic_post'	=> array('lang' => 'ACP_SMARTFEED_SHOW_USERNAME_IN_FIRST_TOPIC_POST',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'phpbbservices_smartfeed_show_username_in_replies'			=> array('lang' => 'ACP_SMARTFEED_SHOW_USERNAME_IN_REPLIES',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'phpbbservices_smartfeed_new_post_notifications_only'		=> array('lang' => 'ACP_SMARTFEED_NEW_POST_NOTIFICATIONS_ONLY',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
					)
				);
			break;
				
			case 'additional':
				$display_vars = array(
					'title'	=> 'ACP_SMARTFEED_ADDITIONAL',
					'vars'	=> array(						
						'legend1'											=> 'GENERAL_SETTINGS',
						'phpbbservices_smartfeed_include_forums'			=> array('lang' => 'ACP_SMARTFEED_INCLUDE_FORUMS',						'validate' => 'string',	'type' => 'text:15:255', 'explain' => true),
						'phpbbservices_smartfeed_exclude_forums'			=> array('lang' => 'ACP_SMARTFEED_EXCLUDE_FORUMS',						'validate' => 'string',	'type' => 'text:15:255', 'explain' => true),
						'phpbbservices_smartfeed_external_feeds'			=> array('lang' => 'ACP_SMARTFEED_EXTERNAL_FEEDS',						'validate' => 'string',	'type' => 'textarea:3:85', 'explain' => true),
						'phpbbservices_smartfeed_external_feeds_top'		=> array('lang' => 'ACP_SMARTFEED_EXTERNAL_FEEDS_TOP',					'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'phpbbservices_smartfeed_rfc1766_lang'				=> array('lang' => 'ACP_SMARTFEED_RFC1766_LANG',						'validate' => 'string',	'type' => 'text:8:8', 'explain' => true),
						'phpbbservices_smartfeed_feed_image_path'			=> array('lang' => 'ACP_SMARTFEED_FEED_IMAGE_PATH',						'validate' => 'string',	'type' => 'text:40:255', 'explain' => true),
						'phpbbservices_smartfeed_webmaster'					=> array('lang' => 'ACP_SMARTFEED_WEBMASTER',							'validate' => 'string',	'type' => 'text:40:255', 'explain' => true),
						'legend2'											=> 'GENERAL_OPTIONS',
						'phpbbservices_smartfeed_all_by_default'			=> array('lang' => 'ACP_SMARTFEED_ALL_BY_DEFAULT',						'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'phpbbservices_smartfeed_suppress_forum_names'		=> array('lang' => 'ACP_SMARTFEED_SUPPRESS_FORUM_NAMES',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'phpbbservices_smartfeed_apache_htaccess_enabled'	=> array('lang' => 'ACP_SMARTFEED_APACHE_HTACCESS_ENABLED',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
					)
				);
			break;

			default:
				$display_vars = array();	// Keep phpStorm happy
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
				
		}

		$new_config = $this->config;
		$cfg_array = $this->request->variable('config', array('' => ''), true);
		if (sizeof($cfg_array) == 0)
		{
			$cfg_array = $new_config;
		}
		$error = array();

		// We validate the complete config if wished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $this->user->lang('FORM_INVALID');
		}
		
		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $data)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			$new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				$this->config->set($config_name, $config_value);
			}
		}

		if ($submit)
		{
			$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_' . strtoupper($mode));
			$message = $this->user->lang('CONFIG_UPDATED');
			$message_type = E_USER_NOTICE;
			trigger_error($message . adm_back_link($this->u_action), $message_type);
		}

		$this->tpl_name = 'acp_smartfeed';
		$this->page_title = $display_vars['title'];

		$this->template->assign_vars(array(
			'L_TITLE'			=> $this->user->lang($display_vars['title']),
			'L_TITLE_EXPLAIN'	=> $this->user->lang($display_vars['title'] . '_EXPLAIN'),

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br>', $error),

			'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$this->template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (NULL !== $this->user->lang($vars)) ? $this->user->lang($vars) : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (NULL !== $this->user->lang($vars['lang_explain'])) ? $this->user->lang($vars['lang_explain']) : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (NULL !== $this->user->lang($vars['lang'] . '_EXPLAIN')) ? $this->user->lang($vars['lang'] . '_EXPLAIN') : '';
			}

			$content = build_cfg_template($type, $config_key, $new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$this->template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (NULL !== $this->user->lang($vars['lang'])) ? $this->user->lang($vars['lang']) : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}

	}

}
