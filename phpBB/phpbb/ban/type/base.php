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

use phpbb\ban\exception;
use phpbb\cache\driver\driver_interface as cache_interface;
use phpbb\db\driver\driver_interface as db_interface;
use phpbb\user;

abstract class base implements type_interface
{
	/**
	 * Timestamp that marks the ban end
	 *
	 * @var \DateTime
	 */
	protected $ban_end;

	/** @var cache_interface */
	protected $cache;

	/** @var db_interface */
	protected $db;

	/**
	 * Message that will be displayed to the affected user
	 *
	 * @var string
	 */
	protected $displayed_message;

	/**
	 * The items that are going to be banned/excluded/removed
	 *
	 * @var mixed
	 */
	protected $items;

	/**
	 * Ban reason
	 *
	 * @var string
	 */
	protected $message;

	/** @var user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param cache_interface $cache
	 * @param db_interface    $db
	 * @param user            $user
	 */
	public function __construct(cache_interface $cache, db_interface $db, user $user)
	{
		$this->ban_end = null;
		$this->cache = $cache;
		$this->db = $db;
		$this->displayed_message = '';
		$this->items = null;
		$this->message = '';
		$this->user = $user;
	}

	public function exclude()
	{
		return null;
	}

	public function tidy()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_ban_end($ban_end)
	{
		if (($ban_end !== null && !($ban_end instanceof \DateTime)) ||
			($ban_end instanceof \DateTime && $ban_end->getTimestamp() <= time()))
		{
			throw new exception\invalid_ban_end($ban_end);
		}

		$this->ban_end = $ban_end;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_displayed_message($displayed_message)
	{
		$this->displayed_message = $displayed_message;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_message($message)
	{
		$this->message = $message;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_exclude_possible()
	{
		return false;
	}
}
