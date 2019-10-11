<?php
/**
 *
 * @package phpBB Extension - Smartfeed
 * @copyright (c) 2019 Mark D. Hamill (mark@phpbbservices.com)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbbservices\smartfeed\migrations;

class release_3_0_11 extends \phpbb\db\migration\migration
{

	static public function depends_on()
	{
		return array(
			'\phpbbservices\smartfeed\migrations\release_3_0_7',
			'\phpbb\db\migration\data\v320\v320',
		);
	}


	public function update_data()
	{
		return array(
			array('config.remove',	array('phpbbservices_smartfeed_public_feed_url_suffix_atom', 'phpbbservices_smartfeed_public_feed_url_suffix_rss')),
		);
	}

}
