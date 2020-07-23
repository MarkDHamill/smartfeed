<?php
/**
 *
 * @package phpBB Extension - Smartfeed
 * @copyright (c) 2020 Mark D. Hamill (mark@phpbbservices.com)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbbservices\smartfeed\migrations;

class release_3_0_14 extends \phpbb\db\migration\migration
{

	static public function depends_on()
	{
		return array(
			'\phpbbservices\smartfeed\migrations\release_3_0_11',
			'\phpbb\db\migration\data\v320\v320',
		);
	}


	public function update_data()
	{

		$logo_path = (phpbb_version_compare($this->config['version'], '3.3', '<')) ? 'theme/images/site_logo.gif' : 'theme/images/site_logo.svg';

		return array(
			array('config.update',	array('phpbbservices_smartfeed_feed_image_path', $logo_path)),
		);
	}

}