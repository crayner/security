<?php
namespace Hillrange\Security\Exposed;

use Hillrange\Security\Entity\User;
use Hillrange\Security\Util\SingleTableChildInterface;
use Hillrange\Security\Util\UserTrackInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

class UserFieldsListener implements EventSubscriber
{
	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		// Tells the dispatcher that you want to listen on the form.pre_submit
		// event and that the preSubmit method should be called.
		return [
			Events::loadClassMetadata,
		];
	}

	/**
	 * @param LoadClassMetadataEventArgs $eventArgs
	 */
	public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
	{
		$metadata = $eventArgs->getClassMetadata();

		if ($metadata->getReflectionClass()->implementsInterface(UserTrackInterface::class) && empty($metadata->parentClasses))
		{
			$metadata->mapField(
				[
					'fieldName' => 'lastModified',
					'type'      => 'datetime',
				]
			);
			$metadata->mapField(
				[
					'fieldName' => 'createdOn',
					'type'      => 'datetime',
				]
			);
			$metadata->mapManyToOne(
				[
					'targetEntity'  => User::class,
					'fieldName'     => 'createdBy',
					'joinColumns'   => [
						'created_by'     => [
							'name'                  => 'created_by',
							'referencedColumnName'  => 'id',
						],
					],
				]
			);
			$metadata->mapManyToOne(
				[
					'targetEntity'  => User::class,
					'fieldName'     => 'modifiedBy',
					'joinColumns'   => [
						'modified_by'     => [
							'name'                  => 'modified_by',
							'referencedColumnName'  => 'id',
						],
					],
				]
			);
		}
	}
}