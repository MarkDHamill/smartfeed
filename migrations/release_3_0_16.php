<?php
/**
 *
 * @package phpBB Extension - Smartfeed
 * @copyright (c) 2021 Mark D. Hamill (mark@phpbbservices.com)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbbservices\smartfeed\migrations;

class release_3_0_16 extends \phpbb\db\migration\migration
{

	static public function depends_on()
	{
		return array(
			'\phpbbservices\smartfeed\migrations\release_3_0_15',
			'\phpbb\db\migration\data\v330\v330',
		);
	}

	// A table scan of the phpbb_posts table will be done to find posts by post time. This
	// can cause inefficient queries when running feed.php. To get around this, create an
	// index on post_time. The existing index on topic_id + post_time will cause a table scan.
	public function update_schema()
	{
		return array(
			'add_index' => array(
				$this->table_prefix . 'posts' => array(
					'post_time' => array('post_time'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_keys' => array(
				$this->table_prefix . 'posts' => array(
					'post_time',
				),
			),
		);
	}

	public function update_data()
	{
		// Fix Smartfeed generator URL and add a public-only control
		return array(
			array('config.remove', array('phpbbservices_smartfeed_url')),
			array('config.add',	array('phpbbservices_smartfeed_url', 'https://www.phpbbservices.com/my-software/smartfeed_wp/')),
			array('config.add',	array('phpbbservices_smartfeed_public_only', '0')),
		);
	}

}

