<?php
namespace Hillrange\Security\Listener;

use Hillrange\Security\Util\ParameterInjector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Stores the locale of the user in the session after the
 * login. This can be used by the LocaleSubscriber afterwards.
 */
class UserLocale_Listener implements EventSubscriberInterface
{
	/**
	 * @var SessionInterface
	 */
	private $session;

	/**
	 * @var null|\Symfony\Component\HttpFoundation\Request
	 */
	private $request;

	/**
	 * @var RequestStack
	 */
	private $requestStack;

	/**
	 * UserLocaleListener constructor.
	 *
	 * @param SessionInterface  $session
	 * @param ParameterInjector $parameterInjector
	 */
	public function __construct(RequestStack $requestStack)
	{
		$this->requestStack = $requestStack;
	}

	/**
	 * @param InteractiveLoginEvent $event
	 */
	public function onInteractiveLogin(InteractiveLoginEvent $event)
	{
		$this->request = $this->requestStack->getCurrentRequest();
		$this->session = $this->request->getSession();

		$user = $event->getAuthenticationToken()->getUser();

		if (null !== $user->getLocale())
			$this->session->set('_locale', $user->getLocale());
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			SecurityEvents::INTERACTIVE_LOGIN => [['onInteractiveLogin', 15]],
		];
	}
}