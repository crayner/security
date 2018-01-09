<?php
namespace Hillrange\Security\Entity;

use Hillrange\Security\EntityExtension\PageExtension;

/**
 * Page
 */
class Page extends PageExtension
{
	/**
	 * @var integer
	 */
	private $id;

	/**
	 * @var string
	 */
	private $route;

	/**
	 * @var array
	 */
	private $roles;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var \DateTime
	 */
	private $cacheTime;

	/**
	 * @var integer
	 */
	private $accessCount;

	/**
	 * @var \DateTime
	 */
	private $lastAccessed;

	/**
	 * @var \DateTime
	 */
	private $firstAccessed;

	/**
	 * Page constructor.
	 */
	public function __construct()
	{
		$this->setCacheTime();
		$this->setAccessCount(0);
		$this->setFirstAccessed();
		$this->setLastAccessed();
		$this->setRoles([]);
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get route
	 *
	 * @return string
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * Set route
	 *
	 * @param string $route
	 *
	 * @return Page
	 */
	public function setRoute($route)
	{
		$this->route = $route;

		return $this;
	}

	/**
	 * Get roles
	 *
	 * @return array
	 */
	public function getRoles(): array
	{
		if (empty($this->roles))
			$this->roles = [];

		return $this->roles;
	}

	/**
	 * Set roles
	 *
	 * @param array $roles
	 *
	 * @return Page
	 */
	public function setRoles($roles): Page
	{
		foreach ($roles as $q => $w)
			if (is_null($w))
				unset($roles[$q]);

		$this->roles = empty($roles) ? [] : $roles;

		return $this;
	}

	public function addRole($role): Page
	{
		if (in_array($role, $this->getRoles()))
			return $this;

		if (strpos($role, 'ROLE_') !== 0)
			return $this;

		$this->roles[] = $role;

		return $this;
	}

	/**
	 * Get path
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Set path
	 *
	 * @param string $path
	 *
	 * @return Page
	 */
	public function setPath($path)
	{
		$this->path = $path;

		return $this;
	}

	/**
	 * Get CacheTime
	 *
	 * @return \DateTime
	 */
	public function getCacheTime(): \DateTime
	{
		if (empty($this->cacheTime))
			$this->cacheTime = new \DateTime('-16 Minutes');

		return $this->cacheTime;
	}

	/**
	 * Set CacheTime
	 *
	 * @param \DateTime|null $cacheTime
	 *
	 * @return Page
	 */
	public function setCacheTime(\DateTime $cacheTime = null): Page
	{
		if (empty($cacheTime))
			$cacheTime = new \DateTime('Now');
		$this->cacheTime = $cacheTime;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getAccessCount(): int
	{
		return $this->accessCount ? $this->accessCount : 0 ;
	}

	/**
	 * @return int
	 */
	public function incAccessCount(): int
	{
		$this->setAccessCount($this->getAccessCount() + 1);

		return $this->getAccessCount();
	}

	/**
	 * @param int $accessCount
	 *
	 * @return Page
	 */
	public function setAccessCount(int $accessCount): Page
	{
		$this->accessCount = $accessCount;

		return $this;
}

	/**
	 * @return \DateTime
	 */
	public function getLastAccessed(): ?\DateTime
	{
		return $this->lastAccessed;
	}

	/**
	 * @param \DateTime $lastAccessed
	 *
	 * @return Page
	 */
	public function setLastAccessed(\DateTime $lastAccessed = null): Page
	{
		$this->lastAccessed = $lastAccessed ? $lastAccessed : new \DateTime('now');

		return $this;
}

	/**
	 * @return \DateTime
	 */
	public function getFirstAccessed(): \DateTime
	{
		return $this->firstAccessed;
	}

	/**
	 * @param \DateTime $firstAccessed
	 *
	 * @return Page
	 */
	public function setFirstAccessed(\DateTime $firstAccessed = null): Page
	{
		$this->firstAccessed = $firstAccessed ? $firstAccessed : new \DateTime('now');

		return $this;
}
}
