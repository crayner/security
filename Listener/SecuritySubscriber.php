<?php
/*
 * Listens to security related events like log-ins, failed logins, etc,
 * and sends them to ThisData.
 *
 */
namespace Hillrange\Security\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Hillrange\Security\Entity\Failure;
use Hillrange\Security\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SecuritySubscriber implements EventSubscriberInterface
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var AuthenticationUtils
	 */
	private $authenticationUtils;

	/**
	 * @var null|Request
	 */
	private $request;

	/**
	 * SecuritySubscriber constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 * @param TokenStorageInterface  $tokenStorage
	 * @param AuthenticationUtils    $authenticationUtils
	 * @param RequestStack           $request
	 * @param LoggerInterface        $logger
	 */
	public function __construct(EntityManagerInterface $entityManager, AuthenticationUtils $authenticationUtils, RequestStack $request, LoggerInterface $logger)
	{
		$this->entityManager = $entityManager;
		$this->authenticationUtils = $authenticationUtils;
		$this->request = $request->getCurrentRequest();
		$this->logger = $logger;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
		];
	}

	/**
	 * @param AuthenticationFailureEvent $event
	 */
	public function onAuthenticationFailure(AuthenticationFailureEvent $event)
	{
		$ip = $this->request->server->get('REMOTE_ADDR');
		$failure = $this->entityManager->getRepository(Failure::class)->loadOneByIP($ip);

		if ($failure->getFailures() < 3)
		{
			$failure->incFailures();
			$this->entityManager->persist($failure);
			$this->entityManager->flush();
			if ($failure->getFailures() >= 3)
				$this->logger->notice("The IP Address " . $ip . " has been locked for login for 20 minutes.");

		}

		$username = $this->authenticationUtils->getLastUsername();
		$existingUser = $this->entityManager->getRepository(User::class)->loadUserByUsername($username);
		if ($existingUser) {
			$this->logger->notice("Log In Denied: Wrong password for User #" . $existingUser->getId()  . " (" . $existingUser->getEmail() . ") from IP: ". $ip);
		} else {
			$this->logger->notice("Log In Denied: User doesn't exist: " . $username . "from IP: ".$ip);
		}
	}
}