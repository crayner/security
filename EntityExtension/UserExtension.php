<?php
namespace Hillrange\Security\EntityExtension;

use Hillrange\Security\Entity\User;
use Hillrange\Security\Util\UserTrackInterface;
use Hillrange\Security\Util\UserTrackTrait;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Yaml\Yaml;

/**
 *
 */
abstract class UserExtension implements AdvancedUserInterface, UserTrackInterface, \Serializable, EquatableInterface
{
	use UserTrackTrait;

	/**
	 * @var string
	 */
	protected $plainPassword;

	/**
	 * @var string
	 */
	protected $currentPassword;

	/**
	 * @var array
	 */
	protected $roles;

	/**
	 * @var array
	 */
	protected $groupList;

	/**
	 * @var array
	 */
	protected $roleList;

	/**
	 * @var bool
	 */
	protected $installer = false;

	/**
	 * UserExtension constructor.
	 *
	 * @param array $roles
	 * @param array $groups
	 */
	public function __construct()
	{
		$this->roles = [];
		$this->setEnabled(false);
		$this->setExpired(false);
		$this->setCredentialsExpired(false);
		$this->setLocale('en');
		$this->setPassword('This password will never work.');
		$this->setUserSettings([]);
	}

	public function getPlainPassword()
	{
		return $this->plainPassword;
	}

	public function setPlainPassword($password)
	{
		$this->plainPassword = $password;

		return $this;
	}

	public function getSalt()
	{
		return null;
	}

	public function isSuperAdmin()
	{
		return $this->hasRole(static::ROLE_SYSTEM_ADMIN);
	}

	public function setSuperAdmin($boolean)
	{

		if (true === $boolean)
			$this->addRole(static::ROLE_SYSTEM_ADMIN);
		else
			$this->removeRole(static::ROLE_SYSTEM_ADMIN);

		return $this;
	}

	public function isPasswordRequestNonExpired($ttl)
	{
		return $this->getPasswordRequestedAt() instanceof \DateTime && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
	}

	public function isAccountNonExpired()
	{
		if (true === $this->expired)
		{
			return false;
		}

		if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time())
		{
			return false;
		}

		return true;
	}

	public function isAccountNonLocked()
	{
		return $this->isEnabled();
	}

	public function isCredentialsNonExpired()
	{
		return !$this->credentialsExpired;
	}

	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * Removes sensitive data from the user.
	 */
	public function eraseCredentials()
	{
		$this->plainPassword = null;
	}

	/**
	 * Serializes the user.
	 *
	 * The serialized data have to contain the fields used by the equals method and the username.
	 *
	 * @return string
	 */
	public function serialize()
	{
		return serialize(array(
			$this->password,
			$this->usernameCanonical,
			$this->credentialsExpired,
			$this->enabled,
			$this->id,
		));
	}

	/**
	 * Unserializes the user.
	 *
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		// add a few extra elements in the array to ensure that we have enough keys when unserializing
		// older data which does not include all properties.
		$data = array_merge($data, array_fill(0, 2, null));

		list(
			$this->password,
			$this->usernameCanonical,
			$this->credentialsExpired,
			$this->enabled,
			$this->id
			) = $data;
	}

	/**
	 * @return bool
	 */
	public function getChangePassword()
	{
		return $this->getCredentialsExpired();
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getId() . '-' . $this->getUsername();
	}

	/**
	 * @return bool
	 */
	public function canDelete()
	{
		if ($this->getId() == 1)
			return false;

		if ($this->enabled)
			return false;

		return true;
	}

	public function rolesToString()
	{
		$roles = $this->getRoles();

		return implode(', ', $roles);
	}

	/**
	 * Returns the roles granted to the user.
	 *
	 * <code>
	 * public function getRoles()
	 * {
	 *     return array('ROLE_USER');
	 * }
	 * </code>
	 *
	 * Alternatively, the roles might be stored on a ``roles`` property,
	 * and populated in any number of different ways when the user object
	 * is created.
	 *
	 * @param  bool $refresh
	 * @return Role[] The user roles
	 */
	public function getRoles($refresh = false)
	{
		if (!empty($this->roles) && !$refresh)
			return $this->roles;
		$this->roles = [];

		$groups = $this->getGroups();

		$groupData = $this->getGroupList();

		foreach ($groups as $group)
		{
			$roles = empty($groupData[$group]) ? [] : $groupData[$group];

			foreach ($roles as $role)
				$this->roles[] = $role;
		}


		foreach ($this->getDirectroles() as $role)
			$this->roles = array_merge($this->roles, array($role));

		return $this->roles;
	}

	/**
	 * @return array
	 */
	public function getGroupList()
	{
		return $this->groupList;
	}

	/**
	 * @param array $groupList
	 *
	 * @return $this
	 */
	public function setGroupList(array $groupList)
	{
		$this->groupList = $groupList;

		return $this;
	}

	/**
	 * @param array $groupList
	 *
	 * @return $this
	 */
	public function setRoleList(array $roleList)
	{
		$this->roleList = $roleList;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isInstaller(): bool
	{
		return $this->installer;
	}

	/**
	 * @param $installer
	 *
	 * @return User
	 */
	public function setInstaller($installer): User
	{
		$this->installer = $installer;

		return $this;
	}

	/**
	 * Format Name
	 * @return string
	 */
	public function formatName()
	{
		return $this->getUsername();
	}

	/**
	 * @param \Symfony\Component\Security\Core\User\UserInterface $user
	 *
	 * @return bool
	 */
	public function isEqualTo(UserInterface $user)
	{
		if ($this->getPassword() !== $user->getPassword()) {
			return false;
		}

		if ($this->getUsername() !== $user->getUsername()) {
			return false;
		}

		if ($this->getEmail() !== $user->getEmail()) {
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function obfuscateEmail(): string
	{
		if ($this->getEmail())
			return preg_replace('/(?:^|@).\K|\.[^@]*$(*SKIP)(*F)|.(?=.*?\.)/', '*', $this->getEmail());

		return '';
	}

	/**
	 * @return string
	 */
	public function getCurrentPassword(): ?string
	{
		return $this->currentPassword;
	}

	/**
	 * @param string $currentPassword
	 *
	 * @return UserModel
	 */
	public function setCurrentPassword(string $currentPassword = null): User
	{
		$this->currentPassword = $currentPassword;

		return $this;
}
}
