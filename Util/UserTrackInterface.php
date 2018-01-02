<?php
namespace Hillrange\Security\Util;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserTrackInterface
{
	/**
	 * @return UserInterface
	 */
	public function getModifiedBy(): UserInterface;

	/**
	 * @param UserInterface|null $modifiedBy
	 *
	 * @return UserTrackInterface
	 */
	public function setModifiedBy(UserInterface $modifiedBy = null): UserTrackInterface;

	/**
	 * @return UserInterface
	 */
	public function getCreatedBy(): UserInterface;

	/**
	 * @param UserInterface|null $createdBy
	 *
	 * @return UserTrackInterface
	 */
	public function setCreatedBy(UserInterface $createdBy = null): UserTrackInterface;

	/**
	 * @return \DateTime
	 */
	public function getCreatedOn(): \DateTime;

	/**
	 * @param \DateTime $createdOn
	 *
	 * @return UserTrackInterface
	 */
	public function setCreatedOn(\DateTime $createdOn): UserTrackInterface;

	/**
	 * @return \DateTime
	 */
	public function getLastModified(): \DateTime;

	/**
	 * @param \DateTime $lastModified
	 *
	 * @return UserTrackInterface
	 */
	public function setLastModified(\DateTime $lastModified): UserTrackInterface;
}