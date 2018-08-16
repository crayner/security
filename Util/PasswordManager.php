<?php
namespace Hillrange\Security\Util;

use Doctrine\ORM\EntityManagerInterface;
use Hillrange\Security\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PasswordManager implements ContainerAwareInterface
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
     * @var
     */
	private $container;

	/**
	 * PasswordManager constructor.
	 *
	 * @param ContainerInterface          $container
	 * @param UserPasswordEncoderInterface $encoder
	 */
	public function __construct(ContainerInterface $container, EncoderFactoryInterface $encoderFactory, EntityManagerInterface $entityManager)
	{
		$this->encoder =  $encoderFactory->getEncoder(User::class);
		$this->entityManager = $entityManager;
        $this->setContainer($container);
        $this->getPassword();
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
        $this->getPassword();
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
     * getPasswordSettingStrings
     *
     * @param null $name
     * @return array
     */
    public function getPasswordSettingStrings($name = null): array
    {
        $data = [];

        $data['%specials%'] = $this->getPasswordSetting('specials') ? 'Yes' : 'No' ;
        $data['%mixedCase%'] = $this->getPasswordSetting('mixed_case') ? 'Yes' : 'No' ;
        $data['%numbers%'] = $this->getPasswordSetting('numbers') ? 'Yes' : 'No';
        $data['%minLength%'] = $this->getPasswordSetting('min_length');
        return $data;
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
     * encodePassword
     *
     * @param string $password
     * @return string
     */
	public function encodePassword(string $password)
	{
		return $this->encoder->encodePassword($password, null);
	}

    /**
     * confirmPassword
     *
     * Checks against the BCrypt Encoder
     * @param $user
     * @param $plainPassword
     * @return bool
     */
	public function confirmPassword($user, $plainPassword)
	{
		return $this->encoder->isPasswordValid($user->getPassword(), $plainPassword, null);
	}

    /**
     * saveNewPassword
     *
     * @param UserInterface $user
     * @param string $password
     */
	public function saveNewPassword(UserInterface $user, string $password)
	{
		$oldPasswords = $user->getUserSetting('old_passwords', []);

		if (! empty($user->getPassword()))
			array_unshift($oldPasswords, $user->getPassword());

		$q = count($oldPasswords);

		while ($q > 12)
		{
			array_pop($oldPasswords);
			$q = count($oldPasswords);
		}

		$user->setUserSetting('old_passwords', $oldPasswords, 'array');

		$user->setPassword($this->encodePassword($password));

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

    /**
     * validatePasswordChange
     *
     * @param UserInterface $user
     * @param string $password
     * @return array|bool
     */
	public function validatePasswordChange(UserInterface $user, string $password)
	{
		$oldPasswords = $user->getUserSetting('old_passwords', []);

		foreach($oldPasswords as $oldPassword)
			if (password_verify($password, $oldPassword))
				return [
					'security.password.error.used_before',
					[
						'%{password}' => $password,
					],
					'plainPassword[first]',
				];

		if (password_verify($password, $user->getPassword()))
			return [
				'security.password.error.current',
				[
					'%{password}' => $password,
				],
				'plainPassword[first]',
			];

		return true;
	}

    /**
     * @param ContainerInterface $container
     * @return PasswordManager
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * getPassword
     *
     * @return array
     */
    public function getPassword(): array
    {
        $this->password = [
            'min_length' => 8,
            'specials' => false,
            'numbers' => true,
            'mixed_case' => true,
        ];
        if ($this->getContainer()->hasParameter('security.password.settings'))
            $this->password = $this->getContainer()->getParameter('security.password.settings');

        return $this->password;
    }
}