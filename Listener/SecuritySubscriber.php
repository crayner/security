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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;

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
	public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, AuthenticationUtils $authenticationUtils, RequestStack $request, LoggerInterface $logger)
	{
		$this->entityManager = $entityManager;
		$this->tokenStorage = $tokenStorage;
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
			SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityLoginSuccess',
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

	/**
	 * @param InteractiveLoginEvent $event
	 */
	public function onSecurityLoginSuccess(InteractiveLoginEvent $event)
	{
		$user = $this->tokenStorage->getToken()->getUser();
		$ip = $this->request->server->get('REMOTE_ADDR');
		$failure = $this->entityManager->getRepository(Failure::class)->loadOneByIP($ip);
		$now = new \DateTime('now');
		$session = $this->request->getSession();
		if ($failure->getId() > 0 && $failure->getFailures() >= 3 && $now->getTimestamp() - $failure->getFailureTime()->getTimeStamp() >= 1200)
		{
			$this->entityManager->remove($failure);
			$this->entityManager->flush();
			$this->logger->notice("IP Address ".$ip." was released for login.");

		} elseif ($failure->getId() > 0 && $failure->getFailures() >= 3) {
			$this->tokenStorage->getToken()->setUser('Anon.');
			$this->tokenStorage->setToken(null);
			$this->logger->notice("Log In Denied: The IP: ".$ip." has been blocked for 20 minutes since " . $failure->getFailureTime()->format('d M/Y H:i:s'));

			$session->set(Security::AUTHENTICATION_ERROR, new AuthenticationException("Log In Denied: The IP: ".$ip." has been blocked for 20 minutes since " . $failure->getFailureTime()->format('d M/Y H:i:s'), 773));
			return;
		}

		// Check for locked or expired
		if ($user->getExpiresAt() <= new \DateTime('now'))
		{
			$user->setExpired(true);
			$user->setExpiresAt(null);
			$this->entityManager->persist($user);
			$this->entityManager->flush();
		}
		if ($user->getLocked() || $user->getExpired())
		{
			$session->set(Security::AUTHENTICATION_ERROR, new AuthenticationException("Log In Denied: The user is locked or expired. Contact site support for help.", 774));
			$this->logger->notice("Log In Denied: The user is locked or expired. Contact site support for help.");
			$this->tokenStorage->getToken()->setUser('Anon.');
			$this->tokenStorage->setToken(null);
			return;

		}

		$user->setLastLogin(new \DateTime('now'));
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		
		$this->logger->notice("Log In: User #" . $user->getId()  . " (" . $user->getEmail() . ")");
	}
}