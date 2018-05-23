<?php
namespace Hillrange\Security\EntityExtension;

use Hillrange\Security\Entity\User;
use Hillrange\Security\Exception\UserException;
use Hillrange\Security\Util\UserTrackInterface;
use Hillrange\Security\Util\UserTrackTrait;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *
 */
abstract class UserExtension implements UserTrackInterface, EquatableInterface
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

    /**
     * getPlainPassword
     *
     * @return null|string
     */
    public function getPlainPassword():?string
	{
		return $this->plainPassword;
	}

    /**
     * setPlainPassword
     *
     * @param null|string $password
     * @return UserExtension
     */
    public function setPlainPassword(?string $password): UserExtension
	{
		$this->plainPassword = $password;

		return $this;
	}

    /**
     * @return bool
     */
    public function isSuperAdmin(): bool
	{
		return $this->hasRole('ROLE_SYSTEM_ADMIN');
	}

    /**
     * @param bool $boolean
     * @return UserException
     */
    public function setSuperAdmin(bool $boolean): UserExtension
	{
		if ($boolean)
			$this->addDirectRole('ROLE_SYSTEM_ADMIN');
		else
			$this->removeDirectRole('ROLE_SYSTEM_ADMIN');

		return $this;
	}

    /**
     * @param $ttl
     * @return bool
     */
    public function isPasswordRequestNonExpired($ttl): bool
	{
		return $this->getPasswordRequestedAt() instanceof \DateTime && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
	}

    /**
     * @return bool
     */
    public function isAccountNonExpired(): bool
	{
		if ($this->isExpired())
			return false;

		if (null !== $this->getExpiresAt() && $this->getExpiresAt()->getTimestamp() < time())
			return false;

		return true;
	}

    /**
     * @return bool
     */
    public function isAccountNonLocked(): bool
	{
		return ! $this->isLocked();
	}

    /**
     * @return bool
     */
    public function isCredentialsNonExpired(): bool
	{
		return !$this->isCredentialsExpired();
	}

	/**
	 * Removes sensitive data from the user.
	 */
	public function eraseCredentials()
	{
		$this->plainPassword = null;
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
		if ($this->getPassword() !== $user->getPassword())
		    return false;

		if ($this->getUsername() !== $user->getUsername())
			return false;

        if ($this->getEmail() !== $user->getEmail())
            return false;

        if ($this->getId() !== $user->getId())
            return false;

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
	 * @return UserExtension
	 */
	public function setCurrentPassword(string $currentPassword = null): UserExtension
	{
		$this->currentPassword = $currentPassword;

		return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isEnabled();
    }

    /**
     * @return null
     */
    public function getSalt(): ?string
    {
        return null;
    }

    public function hasRole(string $role): bool
    {
        if (in_array($role, $this->getRoles()))
            return true;
        return false;
    }
}
