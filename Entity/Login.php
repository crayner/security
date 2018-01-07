<?php
namespace Hillrange\Security\Entity;

class Login
{
	/**
	 * @var string
	 */
	private $_username;

	/**
	 * @var string
	 */
	private $_password;

	/**
	 * @var boolean
	 */
	private $_remember_me;

	public function __construct()
	{
		$this->setUsername('');
		$this->setPassword('');
		$this->setRememberMe(false);
	}

	/**
	 * @return string
	 */
	public function getUsername(): ?string
	{
		return $this->_username;
	}

	/**
	 * @param string $username
	 *
	 * @return Login
	 */
	public function setUsername(string $username = null): Login
	{
		$this->_username = $username;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->_password;
	}

	/**
	 * @param string $password
	 *
	 * @return Login
	 */
	public function setPassword(string $password): Login
	{
		$this->_password = $password;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isRememberMe(): bool
	{
		return $this->_remember_me;
	}

	/**
	 * @param bool $rememeber_me
	 *
	 * @return Login
	 */
	public function setRememberMe(bool $remember_me): Login
	{
		$this->_remember_me = $remember_me;

		return $this;
}
}