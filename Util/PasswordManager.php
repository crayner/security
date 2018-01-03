<?php
namespace Hillrange\Security\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PasswordManager
{
	/**
	 * @var array
	 */
	private $password;

	/**
	 * @var string
	 */
	private $generatedPassword;

	/**
	 * @var UserPasswordEncoderInterface
	 */
	private $encoder;

	/**
	 * PasswordManager constructor.
	 *
	 * @param ContainerInterface           $container
	 * @param UserPasswordEncoderInterface $encoder
	 */
	public function __construct(ContainerInterface $container, UserPasswordEncoderInterface $encoder)
	{
		$this->password = $container->getParameter('password');
		$this->encoder = $encoder;
	}

	/**
	 * Is Password Valid
	 *
	 * @param               $password
	 * @param Miscellaneous $misc
	 *
	 * @return bool
	 */
	public function isPasswordValid($password)
	{
		$pattern = "/^(.*(?=.*[a-z])";
		if ($this->getPasswordSetting('mixed_case'))
			$pattern .= "(?=.*[A-Z])";

		if ($this->getPasswordSetting('numbers'))
			$pattern .= "(?=.*[0-9])";

		if ($this->getPasswordSetting('specials'))
			$pattern .= "(?=.*?[#?!@$%^+=&*-])";
		$pattern .= ".*){" . $this->getPasswordSetting('min_length') . ",}$/";

		return (preg_match($pattern, $password) === 1);
	}

	/**
	 * @param null $name
	 *
	 * @return array|string|bool
	 */
	public function getPasswordSetting($name = null)
	{
		switch ($name)
		{
			case 'specials':
			case 'numbers':
			case 'mixed_case':
			case 'min_length':
				return $this->password[$name];
				break;
			default:
				return $this->password;
		}
	}

	/**
	 * @return string
	 */
	public function generatePassword($generate = false)
	{
		if (! $this->generatedPassword)
			$generate = true;
		if (! $generate)
			return $this->generatedPassword;
		$source = 'abcdefghijklmnopqrstuvwxyz';
		if ($this->getPasswordSetting('numbers'))
			$source .= '0123456789';
		if ($this->getPasswordSetting('mixed_case'))
			$source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if ($this->getPasswordSetting('specials'))
			$source .= '#?!@$%^+=&*-';

		do {
			$password = '';
			for($x = 0; $x < $this->getPasswordSetting('min_length'); $x++)
				$password .= substr($source, random_int(0, strlen($source) - 1), 1);
		} while (! $this->isPasswordValid($password));

		$this->generatedPassword = $password;

		return $this->generatedPassword;
	}

	/**
	 * @param UserInterface $user
	 * @param string        $password
	 *
	 * @return string
	 */
	public function encodePassword(UserInterface $user, string $password)
	{
		return $this->encoder->encodePassword($user, $password);
	}
}