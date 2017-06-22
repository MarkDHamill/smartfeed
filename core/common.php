<?php
/**
*
* @package phpBB Extension - Smartfeed
* @copyright (c) 2017 Mark D. Hamill (mark@phpbbservices.com)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbbservices\smartfeed\core;

class common
{

	/* @var \phpbb\auth\auth */
	protected $auth;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth	$auth
	*/
		
	public function __construct(\phpbb\auth\auth $auth)
	{
			$this->auth = $auth;
	}
	
	public function check_all_parents($parent_array, $forum_id)
	{
	
		// This function checks all parents for a given forum_id. If any of them do not have the f_list permission
		// the function returns false, meaning the forum should not be displayed because it has a parent that should
		// not be listed. Otherwise it returns true, indicating the forum can be listed.
		
		$there_are_parents = sizeof($parent_array) > 0;
		$current_forum_id = $forum_id;
		$include_this_forum = true;
		
		while ($there_are_parents)
		{
		
			if ($parent_array[$current_forum_id] == 0) 	// No parent
			{
				$there_are_parents = false;
			}
			else
			{
				if ($this->auth->acl_get('f_list', $current_forum_id) == 1)
				{
					// So far so good
					$current_forum_id = $parent_array[$current_forum_id];
				}
				else
				{
					// Danger Will Robinson! No list permission exists for a parent of the requested forum, so this forum should not be shown
					$there_are_parents = false;
					$include_this_forum = false;
				}
			}
			
		}
		
		return $include_this_forum;
			
	}
	
}