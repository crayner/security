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
	private $roles;

	/**
	 * @var array
	 */
	private $groups;

	/**
	 * UserProvider constructor.
	 *
	 * @param UserRepository $userRepository
	 * @param array          $roles
	 * @param array          $groups
	 */
	public function __construct(UserRepository $userRepository, array $roles = [], array $groups = [])
	{
		$this->userRepository = $userRepository;
		$this->roles = $roles;
		$this->groups = $groups;
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

		$user->setRoleList($this->roles);
		$user->setGroupList($this->groups);

		return $user;
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

		$user->setRoleList($this->roles);
		$user->setGroupList($this->groups);

		return $user;
	}
}