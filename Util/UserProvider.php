<?php
namespace Hillrange\Security\Util;

use Hillrange\Security\Entity\User;
use Hillrange\Security\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, UserLoaderInterface
{
	/**
	 * @var UserRepository
	 */
	private $userRepository;

	/**
	 * @var array
	 */
	private static $roles;

	/**
	 * @var array
	 */
	private $groups;

	/**
	 * UserProvider constructor.
	 *
	 * @param UserRepository    $userRepository
	 * @param ParameterInjector $parameterInjector
	 */
	public function __construct(UserRepository $userRepository, ParameterInjector $parameterInjector)
	{
		$this->userRepository = $userRepository;
		self::$roles =  $parameterInjector->getParameter('security.hierarchy.roles');
		$this->groups = $parameterInjector->getParameter('security.groups');
	}

    /**
     * getAllRoles
     *
     * @return array
     */
    public static function getAllRoles(): array
    {
        return self::$roles;
    }

    /**
	 * @param $username
	 *
	 * @return null|\Symfony\Component\Security\Core\User\UserInterface
	 */
	public function loadUserByUsername($username)
	{
		$user = $this->userRepository->loadUserByUsername($username);

		if (is_null($user))
			throw new UsernameNotFoundException(
				sprintf('Username "%s" does not exist.', $username)
			);

		$user->setRoleList(self::$roles);
		$user->setGroupList($this->groups);

		return $user;
	}

	/**
	 * @param $username
	 *
	 * @return null|\Symfony\Component\Security\Core\User\UserInterface
	 */
	public function loadUserByEmail($email)
	{
		return $this->loadUserByUsername($email);
	}

	/**
	 * @param UserInterface $user
	 *
	 * @return null|\Symfony\Component\Security\Core\User\UserInterface
	 */
	public function refreshUser(UserInterface $user)
	{
		if (! $user instanceof User) {
			throw new UnsupportedUserException(
				sprintf('Instances of "%s" are not supported.', get_class($user))
			);
		}

		return $this->loadUserByUsername($user->getUsername());
	}

	/**
	 * @param $class
	 *
	 * @return bool
	 */
	public function supportsClass($class)
	{
		return User::class === $class;
	}

	/**
	 * @param $username
	 *
	 * @return null|UserInterface
	 */
	public function find($id): ?UserInterface
	{
		$user = $this->userRepository->find($id);

		if (is_null($user))
			return null;

		$user->setRoleList(self::$roles);
		$user->setGroupList($this->groups);

		return $user;
	}

	/**
	 * @return array
	 */
	public function getRoles(): array
	{
		return self::$roles;
	}

	/**
	 * @return array
	 */
	public function getGroups(): array
	{
		return $this->groups;
	}

    /**
     * newUser
     *
     * @return UserInterface
     */
    public function newUser(): UserInterface
	{
		$user = new User(self::$roles, $this->groups);

		return $user;

	}
}