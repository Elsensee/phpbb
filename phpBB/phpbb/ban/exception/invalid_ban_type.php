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

namespace phpbb\ban\exception;

class invalid_ban_type extends ban_exception
{
	public function __construct($type, \Exception $previous = null, $code = 0)
	{
		// @TODO
		//parent::__construct($message, $parameters, $previous, $code);
	}
}
