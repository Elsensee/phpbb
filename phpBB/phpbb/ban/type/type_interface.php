<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\ban\type;

interface type_interface
{
	/**
	 * Default cache ttl for sql queries to check ban status
	 */
	const CACHE_TTL = 3600;

	const CHECK_EXCLUDED = -1;
	const CHECK_NO_RESULT = 0;
	const CHECK_BANNED = 1;

	public function ban();

	public function exclude();

	public function check($user_row);

	public function remove();

	public function tidy();

	/**
	 * Get an array of log data in the following format:
	 * 		{LOG_TYPE}	=>	array(
	 *			array('operation'	=> {LOG_OPERATION}, 'data'	=> {ADDITIONAL_DATA}),
	 * 			...
	 * 		),
	 * 		...
	 * LOG_OPERATION and ADDITIONAL_DATA will be given as is to their respective parameters in @see \phpbb\log\log_interface::add
	 *
	 * @return array
	 */
	public function get_log_data();

	/**
	 * Set the time the ban/exemption should end at.
	 * To set the ban infinite you can set $ban_end to null.
	 *
	 * An exception will be thrown if no ban type is set.
	 *
	 * @param \DateTime|null	$ban_end
	 *
	 * @return $this
	 */
	public function set_ban_end($ban_end);

	/**
	 * Set the message that is displayed to the affected user.
	 *
	 * @param string	$displayed_message
	 *
	 * @return $this
	 */
	public function set_displayed_message($displayed_message);

	/**
	 * Set the message that will be displayed as reason to other administrators or moderators.
	 *
	 * @param string	$message
	 *
	 * @return $this
	 */
	public function set_message($message);

	public function set_items($items);

	/**
	 * Return if excluding is possible.
	 *
	 * @return bool
	 */
	public function is_exclude_possible();
}
