<?php
namespace Hillrange\Security\Util;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface UserTrackInterface
 * @package Hillrange\Security\Util
 *
 * @author Craig Rayner
 *
 *
 *         This interface is implemented by the trait UserTrackTrait.
 * To implement this interface add a use statement in the calling class to
 * use the trait.  The listeners will then identify your entity, add the
 * necessary fields and insert appropriate data as the entity is used.  It
 * is actually done with smoke and mirrors.
 *
 */
interface UserTrackInterface
{
	/**
	 * @return null|UserInterface
	 */
	public function getModifiedBy(): ?UserInterface;

	/**
	 * @param UserInterface|null $modifiedBy
	 *
	 * @return UserTrackInterface
	 */
	public function setModifiedBy(UserInterface $modifiedBy = null): UserTrackInterface;

	/**
	 * @return null|UserInterface
	 */
	public function getCreatedBy(): ?UserInterface;

	/**
	 * @param UserInterface|null $createdBy
	 *
	 * @return UserTrackInterface
	 */
	public function setCreatedBy(UserInterface $createdBy = null): UserTrackInterface;

	/**
	 * @return \DateTime|null
	 */
	public function getCreatedOn(): ?\DateTime;

	/**
	 * @param \DateTime $createdOn
	 *
	 * @return UserTrackInterface
	 */
	public function setCreatedOn(\DateTime $createdOn): UserTrackInterface;

	/**
	 * @return \DateTime|null
	 */
	public function getLastModified(): ?\DateTime;

	/**
	 * @param \DateTime $lastModified
	 *
	 * @return UserTrackInterface
	 */
	public function setLastModified(\DateTime $lastModified): UserTrackInterface;
}