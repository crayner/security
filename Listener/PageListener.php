<?php
namespace Hillrange\Security\Listener;

use Hillrange\Security\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Hillrange\Security\Util\ParameterInjector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

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
	 * @var ParameterInjector
	 */
	private $parameterInjector;

	/**
	 * @var RouterInterface
	 */
	private $router;

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			KernelEvents::TERMINATE => ['onTerminate', 16],
			KernelEvents::REQUEST => ['onKernelRequest', -16]
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

        if (strpos($route, 'installer') === 0)
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
	 * @param ParameterInjector      $parameterInjector
	 */
	public function __construct(EntityManagerInterface $entityManager, ParameterInjector $parameterInjector, RouterInterface $router)
	{
		$this->entityManager        = $entityManager;
		$this->roleHierarchy        = $parameterInjector->getParameter('security.role_hierarchy.roles');
		$this->parameterInjector    = $parameterInjector;
		$this->router               = $router;
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

	/**
	 * @param GetResponseEvent $event
	 *
	 * @return RedirectResponse|void
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
	    $session = null;
	    if ($event->getRequest()->hasSession())
		    $session = $event->getRequest()->getSession();

		if ($session instanceof SessionInterface)
		{
            if (!$event->isMasterRequest() || in_array($event->getRequest()->get('_route'),
                    [
                        'security_keep_alive',
                    ]
                )
            ) $session->set('_security_last_page', strtotime('now'));

			if ($session->get('_security_last_page') && $session->get('_security_main'))
			{
				if (strtotime('now') - $session->get('_security_last_page') > $this->parameterInjector->getParameter('idleTimeout', 15) * 60)
				{
					$session->clear('_security_last_page');
					$session->clear('_security_main');
					$session->invalidate();

					$response = new RedirectResponse($this->router->generate('login'));

					return $response;
				}
			}
			$session->set('_security_last_page', strtotime('now'));
		}

		return;
	}
}