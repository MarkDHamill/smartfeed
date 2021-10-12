<?php
/**
 *
 * @package phpBB Extension - Smartfeed
 * @copyright (c) 2021 Mark D. Hamill (mark@phpbbservices.com)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbbservices\smartfeed\controller;

use phpbbservices\smartfeed\constants\constants;

/**
 * Smartfeed ACP controller.
 */
class acp_controller
{

	protected $config;
	protected $language;
	protected $phpbb_log;
	protected $request;
	protected $template;
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config                     $config                  Config object
	 * @param \phpbb\language\language                 $language                Language object
	 * @param \phpbb\log\log                           $phpbb_log               phpBB log object
	 * @param \phpbb\request\request                   $request                 Request object
	 * @param \phpbb\template\template                 $template                Template object
	 * @param \phpbb\user                              $user                    User object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \phpbb\log\log $phpbb_log, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->config = $config;
		$this->language = $language;
		$this->phpbb_log = $phpbb_log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function display_options($mode, $u_action)
	{

		$error = array();

		if (!isset($message_type))
		{
			$message_type = E_USER_NOTICE;
		}

		$submit = $this->request->is_set_post('submit');

		$form_key = 'phpbbservices/smartfeed';
		add_form_key($form_key);

		if ($submit)
		{

			if (!check_form_key($form_key))
			{
				$error[] = $this->language->lang('FORM_INVALID');
				$mode = false;
			}

			switch ($mode)
			{
				case 'ppt':
					$this->config->set('phpbbservices_smartfeed_max_items', $this->request->variable('phpbbservices_smartfeed_max_items', 0));
					$this->config->set('phpbbservices_smartfeed_default_fetch_time_limit', $this->request->variable('phpbbservices_smartfeed_default_fetch_time_limit', 0));
					$this->config->set('phpbbservices_smartfeed_max_word_size', $this->request->variable('phpbbservices_smartfeed_max_word_size', 0));
					$this->config->set('phpbbservices_smartfeed_ttl', $this->request->variable('phpbbservices_smartfeed_ttl', 0));
				break;

				case 'security':
					$this->config->set('phpbbservices_smartfeed_public_only', $this->request->variable('phpbbservices_smartfeed_public_only', 0));
					$this->config->set('phpbbservices_smartfeed_require_ip_authentication', $this->request->variable('phpbbservices_smartfeed_require_ip_authentication', 0));
					$this->config->set('phpbbservices_smartfeed_auto_advertise_public_feed', $this->request->variable('phpbbservices_smartfeed_auto_advertise_public_feed', 0));
					$this->config->set('phpbbservices_smartfeed_privacy_mode', $this->request->variable('phpbbservices_smartfeed_privacy_mode', 0));
					$this->config->set('phpbbservices_smartfeed_show_username_in_first_topic_post', $this->request->variable('phpbbservices_smartfeed_show_username_in_first_topic_post', 0));
					$this->config->set('phpbbservices_smartfeed_show_username_in_replies', $this->request->variable('phpbbservices_smartfeed_show_username_in_replies', 0));
					$this->config->set('phpbbservices_smartfeed_new_post_notifications_only', $this->request->variable('phpbbservices_smartfeed_new_post_notifications_only', 0));
				break;

				case 'additional':
					$this->config->set('phpbbservices_smartfeed_ui_location', $this->request->variable('phpbbservices_smartfeed_ui_location', 0));
					$this->config->set('phpbbservices_smartfeed_include_forums', $this->request->variable('phpbbservices_smartfeed_include_forums', ''));
					$this->config->set('phpbbservices_smartfeed_exclude_forums', $this->request->variable('phpbbservices_smartfeed_exclude_forums', ''));
					$this->config->set('phpbbservices_smartfeed_external_feeds', $this->request->variable('phpbbservices_smartfeed_external_feeds', ''));
					$this->config->set('phpbbservices_smartfeed_external_feeds_top', $this->request->variable('phpbbservices_smartfeed_external_feeds_top', 0));
					$this->config->set('phpbbservices_smartfeed_rfc1766_lang', $this->request->variable('phpbbservices_smartfeed_rfc1766_lang', ''));
					$this->config->set('phpbbservices_smartfeed_feed_image_path', $this->request->variable('phpbbservices_smartfeed_feed_image_path', ''));
					$this->config->set('phpbbservices_smartfeed_webmaster', $this->request->variable('phpbbservices_smartfeed_webmaster', ''));
					$this->config->set('phpbbservices_smartfeed_all_by_default', $this->request->variable('phpbbservices_smartfeed_all_by_default', 0));
					$this->config->set('phpbbservices_smartfeed_suppress_forum_names', $this->request->variable('phpbbservices_smartfeed_suppress_forum_names', 0));
					$this->config->set('phpbbservices_smartfeed_htaccess_enabled', $this->request->variable('phpbbservices_smartfeed_htaccess_enabled', 0));
					$this->config->set('phpbbservices_smartfeed_apache_htaccess_enabled', $this->request->variable('phpbbservices_smartfeed_apache_htaccess_enabled', 0));
				break;

				default:
					trigger_error('NO_MODE', E_USER_ERROR);
			}

			if (!isset($message_type))
			{
				$message_type = E_USER_NOTICE;
			}

			if (count($error))
			{
				$message = implode('<br>', $error);
			}
			else
			{
				$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_' . strtoupper($mode));
				$message = $this->language->lang('CONFIG_UPDATED');
			}

			trigger_error($message . adm_back_link($u_action), $message_type);
		}

		$this->template->assign_vars(array(
			'U_ACTION'			=> $u_action)
		);

		switch ($mode)
		{

			case 'ppt':
				$this->template->assign_vars(array(
					'L_TITLE'								=> $this->language->lang('ACP_SMARTFEED_PPT'),
					'L_TITLE_EXPLAIN'						=> $this->language->lang('ACP_SMARTFEED_PPT_EXPLAIN'),
					'S_SMARTFEED_PPT'						=> true,
					'SMARTFEED_DEFAULT_FETCH_TIME_LIMIT'	=> $this->config['phpbbservices_smartfeed_default_fetch_time_limit'],
					'SMARTFEED_MAX_ITEMS'					=> $this->config['phpbbservices_smartfeed_max_items'],
					'SMARTFEED_MAX_WORD_SIZE'				=> $this->config['phpbbservices_smartfeed_max_word_size'],
					'SMARTFEED_TTL'							=> $this->config['phpbbservices_smartfeed_ttl'],
				));
			break;

			case 'security':
				$this->template->assign_vars(array(
					'L_TITLE'									=> $this->language->lang('ACP_SMARTFEED_SECURITY'),
					'L_TITLE_EXPLAIN'							=> $this->language->lang('ACP_SMARTFEED_SECURITY_EXPLAIN'),
					'S_SMARTFEED_SECURITY'						=> true,
					'SMARTFEED_AUTO_ADVERTISE_PUBLIC_FEED_NO'	=> ($this->config['phpbbservices_smartfeed_auto_advertise_public_feed'] == 0) ? ' checked="checked"' : '',
					'SMARTFEED_AUTO_ADVERTISE_PUBLIC_FEED_YES'	=> ($this->config['phpbbservices_smartfeed_auto_advertise_public_feed'] == 1) ? ' checked="checked"' : '',
					'SMARTFEED_NEW_POST_NOTIFICATIONS_ONLY_NO'	=> ($this->config['phpbbservices_smartfeed_new_post_notifications_only'] == 0) ? ' checked="checked"' : '',
					'SMARTFEED_NEW_POST_NOTIFICATIONS_ONLY_YES'	=> ($this->config['phpbbservices_smartfeed_new_post_notifications_only'] == 1) ? ' checked="checked"' : '',
					'SMARTFEED_PRIVACY_MODE_NO'					=> ($this->config['phpbbservices_smartfeed_privacy_mode'] == 0) ? ' checked="checked"' : '',
					'SMARTFEED_PRIVACY_MODE_YES'				=> ($this->config['phpbbservices_smartfeed_privacy_mode'] == 1) ? ' checked="checked"' : '',
					'SMARTFEED_PUBLIC_ONLY_NO'					=> $this->config['phpbbservices_smartfeed_public_only'] == 0 ? ' checked="checked"' : '',
					'SMARTFEED_PUBLIC_ONLY_YES'					=> $this->config['phpbbservices_smartfeed_public_only'] == 1 ? ' checked="checked"' : '',
					'SMARTFEED_REQUIRE_IP_AUTHENTICATION_NO'	=> ($this->config['phpbbservices_smartfeed_require_ip_authentication'] == 0) ? ' checked="checked"' : '',
					'SMARTFEED_REQUIRE_IP_AUTHENTICATION_YES'	=> ($this->config['phpbbservices_smartfeed_require_ip_authentication'] == 1) ? ' checked="checked"' : '',
					'SMARTFEED_SHOW_USERNAME_IN_FIRST_TOPIC_POST_NO'	=> ($this->config['phpbbservices_smartfeed_show_username_in_first_topic_post'] == 0) ? ' checked="checked"' : '',
					'SMARTFEED_SHOW_USERNAME_IN_FIRST_TOPIC_POST_YES'	=> ($this->config['phpbbservices_smartfeed_show_username_in_first_topic_post'] == 1) ? ' checked="checked"' : '',
					'SMARTFEED_SHOW_USERNAME_IN_REPLIES_NO'		=> ($this->config['phpbbservices_smartfeed_show_username_in_replies'] == 0) ? ' checked="checked"' : '',
					'SMARTFEED_SHOW_USERNAME_IN_REPLIES_YES'	=> ($this->config['phpbbservices_smartfeed_show_username_in_replies'] == 1) ? ' checked="checked"' : '',
				));
			break;

			case 'additional':
				$this->template->assign_vars(array(
					'L_TITLE'								=> $this->language->lang('ACP_SMARTFEED_ADDITIONAL'),
					'L_TITLE_EXPLAIN'						=> $this->language->lang('ACP_SMARTFEED_ADDITIONAL_EXPLAIN'),
					'S_SMARTFEED_ADDITIONAL'				=> true,
					'SMARTFEED_ALL_BY_DEFAULT_NO'			=> $this->config['phpbbservices_smartfeed_all_by_default'] == 0 ? ' checked="checked"' : '',
					'SMARTFEED_ALL_BY_DEFAULT_YES'			=> $this->config['phpbbservices_smartfeed_all_by_default'] == 1 ? ' checked="checked"' : '',
					'SMARTFEED_APACHE_HTACCESS_ENABLED_NO'	=> $this->config['phpbbservices_smartfeed_apache_htaccess_enabled'] == 0 ? ' checked="checked"' : '',
					'SMARTFEED_APACHE_HTACCESS_ENABLED_YES'	=> $this->config['phpbbservices_smartfeed_apache_htaccess_enabled'] == 1 ? ' checked="checked"' : '',
					'SMARTFEED_EXCLUDE_FORUMS'				=> $this->config['phpbbservices_smartfeed_exclude_forums'],
					'SMARTFEED_EXTERNAL_FEEDS'				=> $this->config['phpbbservices_smartfeed_external_feeds'],
					'SMARTFEED_EXTERNAL_FEEDS_TOP_NO'		=> $this->config['phpbbservices_smartfeed_external_feeds_top'] == 0 ? ' checked="checked"' : '',
					'SMARTFEED_EXTERNAL_FEEDS_TOP_YES'		=> $this->config['phpbbservices_smartfeed_external_feeds_top'] == 1 ? ' checked="checked"' : '',
					'SMARTFEED_FEED_IMAGE_PATH'				=> $this->config['phpbbservices_smartfeed_feed_image_path'],
					'SMARTFEED_INCLUDE_FORUMS'				=> $this->config['phpbbservices_smartfeed_include_forums'],
					'SMARTFEED_RFC1766_LANG'				=> $this->config['phpbbservices_smartfeed_rfc1766_lang'],
					'SMARTFEED_SUPPRESS_FORUM_NAMES_NO'		=> $this->config['phpbbservices_smartfeed_suppress_forum_names'] == 0 ? ' checked="checked"' : '',
					'SMARTFEED_SUPPRESS_FORUM_NAMES_YES'	=> $this->config['phpbbservices_smartfeed_suppress_forum_names'] == 1 ? ' checked="checked"' : '',
					'SMARTFEED_UI_LOCATION_0'				=> ($this->config['phpbbservices_smartfeed_ui_location'] == 0) ? ' selected="selected"' : '',
					'SMARTFEED_UI_LOCATION_1'				=> ($this->config['phpbbservices_smartfeed_ui_location'] == 1) ? ' selected="selected"' : '',
					'SMARTFEED_UI_LOCATION_2'				=> ($this->config['phpbbservices_smartfeed_ui_location'] == 2) ? ' selected="selected"' : '',
					'SMARTFEED_UI_LOCATION_3'				=> ($this->config['phpbbservices_smartfeed_ui_location'] == 3) ? ' selected="selected"' : '',
					'SMARTFEED_UI_LOCATION_4'				=> ($this->config['phpbbservices_smartfeed_ui_location'] == 4) ? ' selected="selected"' : '',
					'SMARTFEED_UI_LOCATION_5'				=> ($this->config['phpbbservices_smartfeed_ui_location'] == 5) ? ' selected="selected"' : '',
					'SMARTFEED_WEBMASTER'					=> $this->config['phpbbservices_smartfeed_webmaster'],
				));
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);

		}

	}

}
