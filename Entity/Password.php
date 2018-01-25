<?php
namespace Hillrange\Security\Entity;

class Password
{
	/**
	 * @var string
	 */
	private $plainPassword;

	/**
	 * @param string $plainPassword
	 *
	 * @return Password
	 */
	public function setPlainPassword(string $plainPassword): Password
	{
		$this->plainPassword = $plainPassword;

		return $this;
}

	/**
	 * @return null|string
	 */
	public function getPlainPassword(): ?string
	{
		return $this->plainPassword;
	}
}