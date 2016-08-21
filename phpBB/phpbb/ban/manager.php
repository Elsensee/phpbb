<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\ban;

use phpbb\ban\exception;
use phpbb\ban\type\type_interface;
use phpbb\datetime;
use phpbb\di\service_collection;
use phpbb\event\dispatcher_interface;
use phpbb\log\log_interface;
use phpbb\user;

/**
 * Manager class for bans
 */
class manager
{
	/** @var type\type_interface */
	protected $current_type;

	/** @var dispatcher_interface */
	protected $dispatcher;

	/** @var log_interface */
	protected $log;

	/** @var bool */
	protected $log_enabled;

	/** @var service_collection */
	protected $type_collection;

	/** @var user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param dispatcher_interface $dispatcher
	 * @param log_interface        $log
	 * @param service_collection   $type_collection
	 * @param user                 $user
	 */
	public function __construct(dispatcher_interface $dispatcher, log_interface $log, service_collection $type_collection, user $user)
	{
		$this->current_type = null;
		$this->dispatcher = $dispatcher;
		$this->log = $log;
		$this->log_enabled = true;
		$this->type_collection = $type_collection;
		$this->user = $user;
	}

	public function ban()
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		$result = $this->current_type->ban();

		if ($this->log_enabled)
		{
			$log_data = $this->current_type->get_log_data();
			foreach ($log_data as $log_mode => $log_entries)
			{
				foreach ($log_entries as $log_entry)
				{
					$this->log->add($log_mode, $this->user->data['user_id'], $this->user->ip, $log_entry['operation'], false, $log_entry['data']);
				}
			}
		}

		return $result;
	}

	public function exclude()
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		$result = $this->current_type->exclude();

		if ($this->log_enabled)
		{
			$log_data = $this->current_type->get_log_data();
			foreach ($log_data as $log_mode => $log_entries)
			{
				foreach ($log_entries as $log_entry)
				{
					$this->log->add($log_mode, $this->user->data['user_id'], $this->user->ip, $log_entry['operation'], false, $log_entry['data']);
				}
			}
		}

		return $result;
	}

	public function check($user_row)
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		return $this->current_type->check($user_row);
	}

	public function check_all($user_row)
	{
		$overall_result = type_interface::CHECK_NO_RESULT;

		foreach ($this->type_collection as $type)
		{
			$result = $type->check($user_row);

			if ($result === type_interface::CHECK_EXCLUDED)
			{
				return $result;
			}

			$overall_result |= $result;
		}

		return (int) $overall_result;
	}

	public function remove()
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		$result = $this->current_type->remove();

		if ($this->log_enabled)
		{
			$log_data = $this->current_type->get_log_data();
			foreach ($log_data as $log_mode => $log_entries)
			{
				foreach ($log_entries as $log_entry)
				{
					$this->log->add($log_mode, $this->user->data['user_id'], $this->user->ip, $log_entry['operation'], false, $log_entry['data']);
				}
			}
		}

		return $result;
	}

	public function tidy()
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		return $this->current_type->tidy();
	}

	public function tidy_all()
	{
		$result = false;

		/** @var type_interface $type */
		foreach ($this->type_collection as $type)
		{
			$result |= $type->tidy();
		}

		return $result;
	}

	/**
	 * Disable logging.
	 *
	 * @return $this
	 */
	public function disable_log()
	{
		$this->log_enabled = false;

		return $this;
	}

	/**
	 * Enable logging.
	 *
	 * @return $this
	 */
	public function enable_log()
	{
		$this->log_enabled = true;

		return $this;
	}

	/**
	 * Set the time the ban/exemption should end at.
	 * To set the ban infinite you can set $ban_end to null or <= 0.
	 *
	 * An exception will be thrown if no ban type is set.
	 *
	 * @param int|\DateTime|null	$ban_end
	 *
	 * @return $this
	 */
	public function set_ban_end($ban_end)
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		if (is_int($ban_end))
		{
			$ban_end = ($ban_end > 0) ? new datetime($this->user, '@' . $ban_end) : null;
		}
		else if (!($ban_end instanceof \DateTime) && $ban_end !== null)
		{
			$ban_end = new datetime($this->user, $ban_end);
		}

		$this->current_type->set_ban_end($ban_end);

		return $this;
	}

	/**
	 * Set the message that is displayed to the affected user.
	 * An exception will be thrown if no ban type is set.
	 *
	 * @param string	$displayed_message
	 *
	 * @return $this
	 */
	public function set_displayed_message($displayed_message)
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		$this->current_type->set_displayed_message($displayed_message);

		return $this;
	}

	/**
	 * Set the duration of this ban/exemption.
	 * To set the ban infinite you can set $ban_end to <= 0.
	 *
	 * An exception will be thrown if no ban type is set.
	 *
	 * @param int	$duration	The duration of this ban/exemption in seconds.
	 *
	 * @return $this
	 */
	public function set_duration($duration)
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		$ban_end = ($duration > 0) ? new datetime($this->user, time() + $duration) : null;

		$this->current_type->set_ban_end($ban_end);

		return $this;
	}

	/**
	 * Set the message that will be displayed as reason to other administrators or moderators.
	 * An exception will be thrown if no ban type is set.
	 *
	 * @param string	$message
	 *
	 * @return $this
	 */
	public function set_message($message)
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		$this->current_type->set_message($message);

		return $this;
	}

	public function set_items($items)
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		$current_type = $this->current_type;
		$override = false;

		/**
		 * You can use this event to change the items that will be banned/excluded.
		 * You also have access to the current ban type, but you can't change it.
		 *
		 * @event core.ban_manager_set_items
		 * @var	\phpbb\ban\type\type_interface	current_type	The current ban type
		 * 														Note: This variable can not be changed.
		 * @var mixed							items			The items that have to be set
		 * @var	bool							override		Set to true to skip the set_items() call.
		 * @since 3.3.0-a1
		 */
		$vars = array('current_type', 'items', 'override');
		extract($this->dispatcher->trigger_event('core.ban_manager_set_items', compact($vars)));

		if (!$override)
		{
			$this->current_type->set_items($items);
		}

		return $this;
	}

	/**
	 * Set the current ban type. This has to be a service name available in the service collection.
	 *
	 * @param string	$type
	 *
	 * @return $this
	 */
	public function set_type($type)
	{
		$override = false;

		/**
		 * You can use this event to change the ban type that will be set
		 * (and avoid a possible occurring exception)
		 *
		 * @event core.ban_manager_set_type
		 * @var	string	type		The ban type that is to be set
		 * 							(this has to be a service name available in the service collection)
		 * @var	bool	override	Set to true to directly set the type
		 * @since 3.3.0-a1
		 */
		$vars = array('type', 'override');
		extract($this->dispatcher->trigger_event('core.ban_manager_set_type', compact($vars)));

		if (!$override && !isset($this->type_collection[$type]))
		{
			if (!isset($this->type_collection['ban.type.' . $type]))
			{
				throw new exception\invalid_ban_type($type);
			}

			$type = 'ban.type.' . $type;
		}

		$this->current_type = $this->type_collection[$type];

		return $this;
	}

	/**
	 * Return if excluding is possible with the current ban type.
	 * An exception will be thrown if no ban type is set.
	 *
	 * @return bool
	 */
	public function is_exclude_possible()
	{
		if ($this->current_type === null)
		{
			throw new exception\no_ban_type();
		}

		return $this->current_type->is_exclude_possible();
	}
}
