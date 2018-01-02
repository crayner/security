<?php
namespace Hillrange\Security\Util;

use Symfony\Component\Security\Core\User\UserInterface;

trait UserTrackTrait
{

	/**
	 * @var \DateTime
	 */
	protected $lastModified;

	/**
	 * @var \DateTime
	 */
	protected $createdOn;

	/**
	 * @var \Hillrange\Security\Entity\User
	 */
	protected $createdBy;

	/**
	 * @var \Hillrange\Security\Entity\User
	 */
	protected $modifiedBy;

	/**
	 * @return \DateTime
	 */
	public function getLastModified(): \DateTime
	{
		return $this->lastModified;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedOn(): \DateTime
	{
		return $this->createdOn;
	}

	/**
	 * @return \Hillrange\Security\Entity\User
	 */
	public function getCreatedBy(): UserInterface
	{
		return $this->createdBy;
	}

	/**
	 * @return \Hillrange\Security\Entity\User
	 */
	public function getModifiedBy(): UserInterface
	{
		return $this->modifiedBy;
	}

	/**
	 * @param \DateTime $lastModified
	 *
	 * @return UserTrackTrait
	 */
	public function setLastModified(\DateTime $lastModified): UserTrackInterface
	{
		$this->lastModified = $lastModified;

		return $this;
	}

	/**
	 * @param \DateTime $createdOn
	 *
	 * @return UserTrackTrait
	 */
	public function setCreatedOn(\DateTime $createdOn): UserTrackInterface
	{
		$this->createdOn = $createdOn;

		return $this;
	}

	/**
	 * @param \Hillrange\Security\Entity\User $createdBy
	 *
	 * @return UserTrackTrait
	 */
	public function setCreatedBy(UserInterface $createdBy = null): UserTrackInterface
	{
		$this->createdBy = $createdBy;

		return $this;
	}

	/**
	 * @param \Hillrange\Security\Entity\User $modifiedBy
	 *
	 * @return UserTrackTrait
	 */
	public function setModifiedBy(UserInterface $modifiedBy = null): UserTrackInterface
	{
		$this->modifiedBy = $modifiedBy;

		return $this;
	}

}