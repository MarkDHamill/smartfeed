<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2017 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbservices\smartfeed\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\phpbbservices\smartfeed\acp\main_module',
			'title'		=> 'ACP_SMARTFEED_TITLE',
			'version'	=> '3.0.8',
			'modes'		=> array(
				'ppt'			=> array('title' => 'ACP_SMARTFEED_PPT', 'auth' => 'ext_phpbbservices/smartfeed && acl_a_extensions', 'cat' => array('ACP_SMARTFEED_TITLE')),
				'security'		=> array('title' => 'ACP_SMARTFEED_SECURITY', 'auth' => 'ext_phpbbservices/smartfeed && acl_a_extensions', 'cat' => array('ACP_SMARTFEED_TITLE')),
				'additional'	=> array('title' => 'ACP_SMARTFEED_ADDITIONAL', 'auth' => 'ext_phpbbservices/smartfeed && acl_a_extensions', 'cat' => array('ACP_SMARTFEED_TITLE')),
			),
		);
	}
}
