<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2017 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbservices\smartfeed\migrations;

class release_3_0_4 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return false;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v318');
	}

	public function update_data()
	{

		global $phpbb_container;

		$language = $phpbb_container->get('language');

		return array(
		
			// Remove old Smartfeed mod configuration variables, if they were carried over from a 3.0.x to 3.1.x conversion.
         	array('config.remove', array(
				'sf_all_by_default', 
				'sf_apache_htaccess_enabled', 
				'sf_atom_10_value',
				'sf_auto_advertise_public_feed',
				'sf_default_fetch_time_limit',
				'sf_exclude_forums',
				'sf_external_feeds',
				'sf_external_feeds_prefix',
				'sf_external_feeds_top',
				'sf_feed_image_path',
				'sf_include_forums',
				'sf_max_items',
				'sf_max_size',
				'sf_new_post_notifications_only',
				'sf_privacy_mode',
				'sf_public_feed_url_suffix_atom',
				'sf_public_feed_url_suffix_rss',
				'sf_require_ip_authentication',
				'sf_rfc1766_lang',
				'sf_rss_10_value',
				'sf_rss_20_value',
				'sf_show_sessions',
				'sf_show_username_in_first_topic_post',
				'sf_show_username_in_replies',
				'sf_smartfeed_host',
				'sf_smartfeed_page_url',
				'sf_smartfeed_title',
				'sf_smartfeed_title_short',
				'sf_suppress_forum_names',
				'sf_ttl',
				'sf_webmaster')),
			
			// Add Smartfeed extension configuration variables
			array('config.add',	array('phpbbservices_smartfeed_all_by_default', '1')),
			array('config.add',	array('phpbbservices_smartfeed_apache_htaccess_enabled', '0')),
			array('config.add',	array('phpbbservices_smartfeed_auto_advertise_public_feed', '1')),
			array('config.add',	array('phpbbservices_smartfeed_default_fetch_time_limit', (30 * 24))),
			array('config.add',	array('phpbbservices_smartfeed_exclude_forums', '')),
			array('config.add',	array('phpbbservices_smartfeed_feed_image_path', 'theme/images/site_logo.gif')),
			array('config.add',	array('phpbbservices_smartfeed_include_forums', '')),
			array('config.add',	array('phpbbservices_smartfeed_max_items', '0')),
			array('config.add',	array('phpbbservices_smartfeed_max_word_size', '0')),
			array('config.add',	array('phpbbservices_smartfeed_new_post_notifications_only', '0')),
			array('config.add',	array('phpbbservices_smartfeed_privacy_mode', '1')),
			array('config.add',	array('phpbbservices_smartfeed_public_feed_url_suffix_atom', '')),
			array('config.add',	array('phpbbservices_smartfeed_public_feed_url_suffix_rss', 'y=2')),
			array('config.add',	array('phpbbservices_smartfeed_require_ip_authentication', '0')),
			array('config.add',	array('phpbbservices_smartfeed_rfc1766_lang', $language->lang('USER_LANG'))),
			array('config.add',	array('phpbbservices_smartfeed_show_username_in_first_topic_post', '1')),
			array('config.add',	array('phpbbservices_smartfeed_show_username_in_replies', '1')),
			array('config.add',	array('phpbbservices_smartfeed_suppress_forum_names', '0')),
			array('config.add',	array('phpbbservices_smartfeed_ttl', '60')),
			array('config.add',	array('phpbbservices_smartfeed_url', 'https://phpbbservices.com/smartfeed_wp/')),
			array('config.add',	array('phpbbservices_smartfeed_webmaster','')),
			
			// Add categories and modules to user interface to support this extension
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_SMARTFEED_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_SMARTFEED_TITLE',
				array(
					'module_basename'	=> '\phpbbservices\smartfeed\acp\main_module',
					'modes'				=> array('ppt', 'security', 'additional'),
				),
			)),
		);
	}
	
	public function update_schema()
	{
		return array(
		
        'add_columns'        => array(
            $this->table_prefix . 'users'    => array(
                'user_smartfeed_key'	=> array('VCHAR:32', '')),
            ),
		);
	}
	
	public function revert_schema()
	{
		return array(
			'drop_columns'        => array(
				$this->table_prefix . 'users'        => array(
                	'user_smartfeed_key',
				),
			),
		);
	}
	
}
