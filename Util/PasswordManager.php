<?php
namespace Hillrange\Security\Util;

use Doctrine\ORM\EntityManagerInterface;
use Hillrange\Security\Entity\User;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
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
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * PasswordManager constructor.
	 *
	 * @param ContainerInterface          $container
	 * @param UserPasswordEncoderInterface $encoder
	 */
	public function __construct(ContainerInterface $container, EncoderFactoryInterface $encoderFactory, EntityManagerInterface $entityManager)
	{
		$this->password = $container->getParameter('security.password.settings');
		$this->encoder =  $encoderFactory->getEncoder(User::class);
		$this->entityManager = $entityManager;
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
		return $this->encoder->encodePassword($password, null);
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function isForcedPasswordValid($data)
	{
		if (! $data instanceof UserInterface)
			return [
				'security.user.invalid',
				[],
				'currentPassword',
			];

		if (! $this->isPasswordValid($data->getPlainPassword()))
			return array(
				'security.password.error.message',
				[
					'%numbers%' => $this->getPasswordSetting('numbers') ? 'Yes' : 'No',
					'%mixedCase%' => $this->getPasswordSetting('mixed_case') ? 'Yes' : 'No',
					'%specials%' => $this->getPasswordSetting('specials') ? 'Yes' : 'No',
					'%minLength%' => $this->getPasswordSetting('min_length'),
				],
				'plainPassword[first]',
			);

		$oldPasswords = $data->getUserSettings();

		$oldPasswords = empty($oldPasswords['old_passwords']) ? [] : $oldPasswords['old_passwords'];

		foreach($oldPasswords as $oldPassword)
			if (password_verify($data->getPlainPassword(), $oldPassword))
				return [
					'security.password.error.used_before',
					[
						'%{password}' => $data->getPlainPassword(),
					],
					'plainPassword[first]',
				];

		return true;
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function confirmPassword($data)
	{
		return $this->encoder->isPasswordValid($data->getPassword(), $data->getCurrentPassword(), null);
	}

	/**
	 * @param UserInterface $user
	 */
	public function saveNewPassword(UserInterface $user)
	{
		$settings = $user->getUserSettings();
		$oldPasswords = empty($settings['old_passwords']) ? [] : $settings['old_passwords'];

		$q = array_unshift($oldPasswords, $user->getPassword());

		while ($q > 12)
		{
			array_pop($oldPasswords);
			$q = count($oldPasswords);
		}

		$settings['old_passwords'] = $oldPasswords;

		$user->setUserSettings($settings);

		$user->setPassword($this->encodePassword($user, $user->getPlainPassword()));

		$user->setCurrentPassword(null);
		$user->setPlainPassword(null);
		$user->setCredentialsExpired(false);
		$user->setConfirmationToken(null);
		$user->setPasswordRequestedAt(null);
		if (! is_null($user->getCredentialsExpireAt()) && $user->getCredentialsExpireAt() <= new \DateTime('now'))
			$user->setCredentialsExpireAt(null);

		$this->entityManager->persist($user);
		$this->entityManager->flush();

	}
}