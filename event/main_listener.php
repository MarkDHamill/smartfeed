<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2017 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbservices\smartfeed\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use phpbbservices\smartfeed\constants\constants;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	/* @var \phpbb\config\config */
	protected $config;
	
	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	protected $phpEx;
	
	/**
	* Constructor
	*
	* @param \phpbb\config\config		$config
	* @param \phpbb\controller\helper	$helper		Controller helper object
	* @param \phpbb\template\template	$template	Template object
	* @param string						$php_ext
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, $php_ext)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->phpEx = $php_ext;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header'						=> 'add_page_header_link',
			'core.page_header_after'  				=> 'overall_header_head_append',
		);
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'phpbbservices/smartfeed',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link()
	{
		$this->template->assign_vars(array(
			'U_SMARTFEED_PAGE'	=> $this->helper->route('phpbbservices_smartfeed_ui_controller'),
		));
	}
	
	public function overall_header_head_append()
	{
		
		// Parse ATOM parameters from the configuration variable because the helper requires an array of parameters
		if (strstr($this->config['phpbbservices_smartfeed_public_feed_url_suffix_atom'],'&'))
		{
			$atom_parameters = explode('&', $this->config['phpbbservices_smartfeed_public_feed_url_suffix_atom']);
		}
		else
		{
			$atom_parameters[] = $this->config['phpbbservices_smartfeed_public_feed_url_suffix_atom'];
		}
		
		$atom_array = array();
		foreach ($atom_parameters as $atom_parameter)
		{
			$pos = strpos($atom_parameter, '=');
			if ($pos)
			{
				$atom_array[substr($atom_parameter, 0, $pos)] = substr($atom_parameter, $pos + 1);
			}
		}
		
		// Parse rss parameters from the configuration variable because the helper requires an array of parameters
		if (strstr($this->config['phpbbservices_smartfeed_public_feed_url_suffix_rss'],'&'))
		{
			$rss_parameters = explode('&', $this->config['phpbbservices_smartfeed_public_feed_url_suffix_rss']);
		}
		else
		{
			$rss_parameters[] = $this->config['phpbbservices_smartfeed_public_feed_url_suffix_rss'];
		}
		
		$rss_array = array();
		foreach ($rss_parameters as $rss_parameter)
		{
			$pos = strpos($rss_parameter, '=');
			if ($pos)
			{
				$rss_array[substr($rss_parameter, 0, $pos)] = substr($rss_parameter, $pos + 1);
			}
		}
		
		$this->template->assign_vars(array(
			'S_AUTO_ADVERTISE_PUBLIC_FEED'	=> !empty($this->config['phpbbservices_smartfeed_auto_advertise_public_feed']) ? $this->config['phpbbservices_smartfeed_auto_advertise_public_feed'] : false,
			'U_SMARTFEED_URL_ATOM'				=> $this->helper->route('phpbbservices_smartfeed_feed_controller', $atom_array),
			'U_SMARTFEED_URL_RSS'				=> $this->helper->route('phpbbservices_smartfeed_feed_controller', $rss_array),
		));
	}
   	
}
