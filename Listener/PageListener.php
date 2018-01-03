<?php
namespace Hillrange\Security\Listener;

use Hillrange\Security\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PageListener implements EventSubscriberInterface
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var array
	 */
	private $roleHierarchy;

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			KernelEvents::TERMINATE => ['onTerminate', 16],
		];
	}

	/**
	 * @param PostResponseEvent|GetResponseEvent $event
	 *
	 * @return void
	 */
	public function onTerminate($event)
	{
		if (! $event instanceof PostResponseEvent && ! $event instanceof GetResponseEvent)
			return ;

		$route = $event->getRequest()->get('_route');
		$params = $event->getRequest()->get('_route_params') ?: [];

		if (strpos($route, '_') === 0)
			return ;

		$page  = $this->entityManager->getRepository(Page::class)->loadOneByRoute($route, $params);
		if ($page)
		{
			$page->incAccessCount();
			$page->setLastAccessed(new \DateTime('now'));

			if (! empty($event->getRequest()->get('_security')))
				$this->setSecurityRoles($event->getRequest()->get('_security'), $page);

			if (! empty($event->getRequest()->get('_is_granted')))
				$this->setIsGrantedRoles($event->getRequest()->get('_is_granted'), $page);

			$this->entityManager->persist($page);
			$this->entityManager->flush();
		}

		return ;
	}

	/**
	 * InstallListener constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 * @param ContainerInterface     $container
	 */
	public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
	{
		$this->entityManager   = $entityManager;
		$this->roleHierarchy   = $container->getParameter('security.role_hierarchy.roles');
	}

	/**
	 * @param $granted
	 * @param $page
	 */
	private function setIsGrantedRoles($granted, $page)
	{
		if (! is_array($granted)) return ;

		foreach ($granted as $grant)
			$page->addRole($grant->getAttributes());
	}

	/**
	 * @param $security
	 * @param $page
	 */
	private function setSecurityRoles($security, $page)
	{
		foreach ($security as $grant)
			foreach($this->roleHierarchy as $role=>$ccc)
				if (false !== strpos($grant->getExpression(), $role))
					$page->addRole($role);
	}
}