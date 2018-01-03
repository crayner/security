<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 1/01/2018
 * Time: 11:06
 */

namespace Hillrange\Security\Exception;


class UnauthorisedUserException extends UserException
{
	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * Message data to be used by the translation component.
	 *
	 * @return array|object
	 */
	public function getMessageData()
	{
		return $this->data;
	}

	/**
	 * UserException constructor.
	 *
	 * @param string          $message
	 * @param                 $data
	 * @param int             $code
	 * @param \Throwable|null $previous
	 */
	public function __construct($message = "The user is not authorised.", $data = [], $code = 0, \Throwable $previous = null)
	{
		parent::__construct($message, $data, $code, $previous);
		$this->data = $data ? $data : [];
	}
}