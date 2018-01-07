<?php
namespace Hillrange\Security\Listener;

use Hillrange\Security\Util\UserTrackInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserTrackListener implements EventSubscriber
{
	/**
	 * @var null|UserInterface
	 */
	private $currentUser;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * UserTrackSubscriber constructor.
	 *
	 * @param TokenStorageInterface $tokenStorage
	 */
	public function __construct(TokenStorageInterface $tokenStorage, RequestStack $request)
	{
		$token = $tokenStorage->getToken();

		$user = $token ? $token->getUser() : null ;

		$this->currentUser = $user;

		$this->request = $request->getCurrentRequest();
	}

	/**
	 * @param TokenStorageInterface $tokenStorage
	 * @param Request               $request
	 */
	public function injectTokenStorage(TokenStorageInterface $tokenStorage, Request $request)
	{
		$token = $tokenStorage->getToken();

		$user = $token ? $token->getUser() : null ;

		$this->currentUser = $user;

		$this->request = $request;
	}

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		// Tells the dispatcher that you want to listen on the form.pre_submit
		// event and that the preSubmit method should be called.
		return [
			'prePersist',
			'preUpdate'
		];
	}

	/**
	 * @param LoadClassMetadataEventArgs $eventArgs
	 */
	public function prePersist(LifecycleEventArgs $eventArgs)
	{
		$this->modifyRow($eventArgs);
	}

	/**
	 * @param LoadClassMetadataEventArgs $eventArgs
	 */
	public function preUpdate(LifecycleEventArgs $eventArgs)
	{
		$this->modifyRow($eventArgs);
	}

	/**
	 * @param $args
	 */
	private function modifyRow(LifecycleEventArgs $args)
	{

		if (! $args->getObject() instanceof UserTrackInterface)
			return ;

		$entity        = $args->getEntity();

		$entity->setLastModified(new \Datetime('now'));

		if ($entity instanceof UserInterface && ! $this->currentUser instanceof UserInterface)
			$this->currentUser = $entity;

		if ($this->currentUser instanceof UserInterface)
		{
			if (empty($entity->getCreatedBy()))
				$entity->setCreatedBy($this->currentUser);
			$entity->setModifiedBy($this->currentUser);
		} elseif (! empty($this->request->get('_security')) || ! empty($this->request->get('_is_granted')))
			throw new \LogicException('No User Authenticated.');


		if (empty($entity->getCreatedOn()))
			$entity->setCreatedOn(new \DateTime('now'));
	}
}