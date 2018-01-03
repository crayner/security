<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 1/01/2018
 * Time: 11:06
 */

namespace Hillrange\Security\Exception;


class UserException extends \RuntimeException
{
	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * {@inheritdoc}
	 */
	public function getMessageKey()
	{
		return $this->message;
	}

	/**
	 * Message data to be used by the translation component.
	 *
	 * @return array
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
	public function __construct($message = "", $data = [], $code = 0, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->data = $data ? $data : [];
	}
}