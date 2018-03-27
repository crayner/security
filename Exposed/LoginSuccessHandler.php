<?php
namespace Hillrange\Security\Exposed;

use Hillrange\Security\Entity\User;
use Hillrange\Security\Util\ParameterInjector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Hillrange\Security\Entity\Failure;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
	use TargetPathTrait;
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
	 * @var RouterInterface
	 */
	private $router;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var HttpUtils
	 */
	private $httpUtils;

	/**
	 * @var array
	 */
	private $securityRoutes;

	/**
	 * @var array
	 */
	private $options;

	/**
	 * @var string
	 */
	private $providerKey = 'main';

	/**
	 * LoginSuccessHandler constructor.
	 *
	 * @param HttpUtils              $httpUtils
	 * @param EntityManagerInterface $entityManager
	 * @param TokenStorageInterface  $tokenStorage
	 * @param AuthenticationUtils    $authenticationUtils
	 * @param LoggerInterface        $logger
	 * @param RouterInterface        $router
	 * @param array                  $securityRoutes
	 */
	public function __construct(HttpUtils $httpUtils, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, AuthenticationUtils $authenticationUtils, LoggerInterface $logger, RouterInterface $router, ParameterInjector $parameterInjector)
	{
		$this->httpUtils           = $httpUtils;
		$this->entityManager       = $entityManager;
		$this->tokenStorage        = $tokenStorage;
		$this->authenticationUtils = $authenticationUtils;
		$this->logger              = $logger;
		$this->router              = $router;
		$this->securityRoutes      = $parameterInjector->getParameter('security.routes');
	}

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
	{
		$user = $this->tokenStorage->getToken()->getUser();

		$user = $this->entityManager->getRepository(User::class)->find($user->getId());

		$ip      = $request->server->get('REMOTE_ADDR');
		$failure = $this->entityManager->getRepository(Failure::class)->loadOneByIP($ip);
		$now     = new \DateTime('now');
		$session = $request->getSession();
		if ($failure->getId() > 0 && $failure->getFailures() >= 3 && $now->getTimestamp() - $failure->getFailureTime()->getTimeStamp() >= 1200)
		{
			$this->entityManager->remove($failure);
			$this->entityManager->flush();
			$this->logger->notice("IP Address " . $ip . " was released for login.");
		}
		elseif ($failure->getId() > 0 && $failure->getFailures() >= 3)
		{
			$this->tokenStorage->getToken()->setUser('Anon.');
			$this->tokenStorage->setToken(null);
			$this->logger->notice("Log In Denied: The IP: " . $ip . " has been blocked for 20 minutes since " . $failure->getFailureTime()->format('d M/Y H:i:s'));

			$session->set(Security::AUTHENTICATION_ERROR, new AuthenticationException("Log In Denied: The IP: " . $ip . " has been blocked for 20 minutes since " . $failure->getFailureTime()->format('d M/Y H:i:s'), 773));

			$response = new RedirectResponse($this->router->generate($this->securityRoutes['security_user_logout']));

			return $response;
		} elseif ($failure->getId() > 0)
		{
			$this->entityManager->remove($failure);
			$this->entityManager->flush();
			$this->logger->notice("IP Address " . $ip . " was removed form the failure registrar.");
		}

		// Check for locked or expired
		if (!empty($user->getExpiresAt()) && $user->getExpiresAt() <= new \DateTime('now'))
		{
			$user->setExpired(true);
			$user->setExpiresAt(null);
			$this->entityManager->persist($user);
			$this->entityManager->flush();
		}

		if ($user->isLocked())
		{
			$session->set(Security::AUTHENTICATION_ERROR, new AuthenticationException("Log In Denied: The user is locked or expired or not enabled. Contact site support for help.", 774));
			$this->logger->notice("Log In Denied: The user is locked or expired or not enabled. Contact site support for help.");
			$this->tokenStorage->getToken()->setUser('Anon.');
			$this->tokenStorage->setToken(null);

			$response = new RedirectResponse($this->router->generate($this->securityRoutes['security_user_logout']));

			return $response;
		}

		$user->setLastLogin(new \DateTime('now'));
		$this->entityManager->persist($user);
		$this->entityManager->flush();

		if ($user->isCredentialsExpired())
		{
			$user->setCredentialsExpired(true);
			$user->setCredentialsExpireAt(null);
			$this->entityManager->persist($user);
			$this->entityManager->flush();

			$this->logger->notice("Log In: User #" . $user->getId() . " (" . $user->getEmail() . ") The user is required to change their password.");

			$response = new RedirectResponse($this->router->generate($this->securityRoutes['securty_forced_password_change'], ['id' => $user->getId()]));

			return $response;
		}

		if (! empty($user->getConfirmationToken()))
		{
			$user->setConfirmationToken(null);
			$user->setPasswordRequestedAt(null);
			$this->entityManager->persist($user);
			$this->entityManager->flush();
		}

		if (null !== $user->getLocale())
			$request->setLocale($user->getLocale());

		$this->logger->notice("Log In: User #" . $user->getId() . " (" . $user->getEmail() . ")");

		return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
	}

	/**
	 * Builds the target URL according to the defined options.
	 *
	 * @return string
	 */
	protected function determineTargetUrl(Request $request)
	{
		if ($this->options['always_use_default_target_path']) {
			return $this->options['default_target_path'];
		}

		if ($targetUrl = ParameterBagUtils::getRequestParameterValue($request, $this->options['target_path_parameter'])) {
			return $targetUrl;
		}

		if (null !== $this->providerKey && $targetUrl = $this->getTargetPath($request->getSession(), $this->providerKey)) {
			$this->removeTargetPath($request->getSession(), $this->providerKey);

			return $targetUrl;
		}

		if ($this->options['use_referer'] && $targetUrl = $request->headers->get('Referer')) {
			if (false !== $pos = strpos($targetUrl, '?')) {
				$targetUrl = substr($targetUrl, 0, $pos);
			}
			if ($targetUrl && $targetUrl !== $this->httpUtils->generateUri($request, $this->options['login_path'])) {
				return $targetUrl;
			}
		}

		return $this->options['default_target_path'];
	}

	/**
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		$this->options = $options;
	}

	/**
	 * @param $providerKey
	 */
	public function setProviderKey($providerKey)
	{
		$this->providerKey = $providerKey;
	}
}
