<?php
namespace Hillrange\Security\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Hillrange\Security\Entity\Login;
use Hillrange\Security\Entity\Password;
use Hillrange\Security\Entity\User;
use Hillrange\Security\Exception\UserException;
use Hillrange\Security\Form\ChangePasswordType;
use Hillrange\Security\Form\FullUserType;
use Hillrange\Security\Form\LoginType;
use Hillrange\Security\Form\NewPasswordType;
use Hillrange\Security\Form\UserType;
use Hillrange\Security\Util\PasswordManager;
use Hillrange\Security\Util\TokenGenerator;
use Hillrange\Security\Util\UserManager;
use Hillrange\Security\Util\UserProvider;
use Hillrange\Security\Validator\CheckPassword;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

class SecurityController extends Controller
{
	/**
	 * @Route("/login/", name="login")
	 */
	public function login(Request $request, AuthenticationUtils $authUtils)
	{
		// get the login error if there is one
		$error = $authUtils->getLastAuthenticationError();

		// last username entered by the user
		$lastUsername = $authUtils->getLastUsername() ?: '';

		$login = new Login();
		$login->setUsername($lastUsername);

		$form = $this->createForm(LoginType::class, $login, ['password_reset_url' => $this->generateUrl('password_request_reset'), 'login_url' => $this->generateUrl('login')]);

		return $this->render('@HillrangeSecurity/security/login.html.twig',
			[
				'last_username' => $lastUsername,
				'error'         => $error,
				'form'          => $form->createView(),
			]
		);
	}

	/**
	 * @Route("/logout/", name="logout")
	 */
	public function logout()
	{
		throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
	}

	/**
	 * @Route("/password/request/reset/", name="password_request_reset")
	 */
	public function requestPasswordReset(Request $request, \Swift_Mailer $mailer, TokenGenerator $tokenGenerator, TranslatorInterface $translator, EntityManagerInterface $entityManager)
	{
		$lastUsername = $request->get('login')['_username'];

		$error   = null;
		$comment = '';

		$user = $entityManager->getRepository(User::class)->loadUserByUsername($lastUsername);

		if (!$user)
			$error = new UserException('security.user.not_found', ['%{username}' => $lastUsername]);
		else
		{
			if ($user->getConfirmationToken() && $user->isPasswordRequestNonExpired($this->getParameter('security.password.reset.token.ttl')))
			{
				$error   = new UserException('security.password.reset.exists', ['%{username}' => $lastUsername]);
				$comment = "<p>" . $translator->trans('security.password.reset.email.copy', [], 'security') . "</p>";
			}
			else
			{
				$user->setConfirmationToken($tokenGenerator->generateToken());
				$entityManager->persist($user);
				$entityManager->flush();
			}
			$confirmationUrl = $this->generateUrl('password_new', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL);

			$message = (new \Swift_Message($translator->trans('security.password.reset.email.subject', [], 'security')))
				->setFrom($this->getParameter('mailer_sender_address'), $this->getParameter('mailer_sender_name'))
				->setTo($user->getEmail(), $user->getUsername())
				->setBody(
					$translator->trans('security.password.reset.email.message',
						[
							'%{confirmationUrl}' => $confirmationUrl,
							'%{username}'        => $user->formatName(),
							'%{siteName}'        => 'Site Security System',
							'%{comment}'         => $comment,
							'%{datetime}'        => date('d M/Y H:i', $user->getPasswordRequestedAt()->getTimestamp() + 86400)
						], 'security'),
					'text/html'
				);

			$mailer->send($message);
		}

		$login = new Login();
		$login->setUsername($lastUsername);

		$form = $this->createForm(LoginType::class, $login, ['password_reset_url' => $this->generateUrl('password_request_reset'), 'login_url' => $this->generateUrl('login')]);
		if (!$error)
			$error = new UserException('security.password.reset.email.sent', ['%{email}' => $user->obfuscateEmail()]);

		return $this->render('@HillrangeSecurity/security/login.html.twig',
			[
				'error'         => $error,
				'form'          => $form->createView(),
				'last_username' => $lastUsername,
			]
		);
	}

	/**
	 * @Route("/password/new/{token}/", name="password_new" )
	 */
	public function newPassword(Request $request, $token = null, TranslatorInterface $translator, PasswordManager $passwordManager)
	{
		$entityManager =$this->getDoctrine()->getManager();

		$userRepository = $entityManager->getRepository(User::class);

		$user = $userRepository->loadUserByToken($token);

		$error = null;

		if (!$user)
		{
			$error = new UserException($translator->trans('security.user.token.not_found', ['%{token}' => $token], 'security'));

			return $this->render('@HillrangeSecurity/security/error.html.twig',
				[
					'error' => $error,
				]
			);

		}

		$password = new Password();

		$form = $this->createForm(NewPasswordType::class, $password, ['invalid_match_message' => $translator->trans('security.password.match.error', [], 'security'), 'constraints' => [new CheckPassword(['user' => $user])]]);

		$form->handleRequest($request);

		$success = false;

		if ($form->isSubmitted() && $form->isValid())
		{
			$passwordManager->saveNewPassword($user, $password);
			$error   = new UserException($translator->trans('security.password.change.success', ['%{token}' => $token], 'security'));
			$success = true;
		}
		if ($form->isSubmitted() && !$form->isValid())
		{
			$error = $form->get('plainPassword')->get('first')->getErrors();

			$error = $error->getChildren();

			$error = new UserException($error->getMessage());
		}

		return $this->render('@HillrangeSecurity/security/newpasswordbytoken.html.twig',
			[
				'error'   => $error,
				'form'    => $form->createView(),
				'manager' => $passwordManager,
				'success' => $success,
			]
		);
	}

	/**
	 * @Route("/user/{id}/edit/", name="security_user_edit")
	 * @Security("is_granted('IS_CURRENT_USER', id) or is_granted('ROLE_REGISTRAR')")
	 */
	public function editUser($id, EntityManagerInterface $entityManager, Request $request, UserProvider $userProvider)
	{
		if ($id === 'Add')
			$entity = $userProvider->newUser();
		elseif ($id === 'Current')
		{
			$entity = $this->getUser();
			$id     = $entity->getId();
		}
		else
			$entity = $userProvider->find($id);

		$fullEdit = $this->isGranted(['ROLE_REGISTRAR']);

		if ($id === 'Add' && ! $fullEdit)
			throw new AccessDeniedException('You do not have the authorisation to create a user.');

		if ($fullEdit)
			$form = $this->createForm(FullUserType::class, $entity);
		else
			$form = $this->createForm(UserType::class, $entity);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{

			$entityManager->persist($entity);
			$entityManager->flush();

			if ($id === 'Add')
				$this->redirectToRoute($request->get('_route'), ['id' => $entity->getId()]);

			if ($fullEdit)
				$form = $this->createForm(FullUserType::class, $entity);
			else
				$form = $this->createForm(UserType::class, $entity);
		}

		return $this->render('@HillrangeSecurity/User/user.html.twig',
			[
				'form'  => $form->createView(),
				'error' => null,
				'fullEdit' => $fullEdit,
			]
		);
}

	/**
	 * @Route("/timeout/", name="hillrange_security_timeout")
	 */
	public function timeoutAction(TokenStorageInterface $token, Session $session, Request $request)
	{
		$session->set('_timeout', false);

		$token->setToken(null);
		$session->set('_timeout', true);

		$lapse = $this->getParameter('idle_timeout', 15);

		$session->getFlashBag()->add(
			'info',
			$this->get('translator')->trans('security.user.timeout', array('%hours%' => '00', '%minutes%' => $lapse), 'security')
		);

		$config = $this->getParameter('security.config');
		$firewalls = $config['firewalls'];

		$name = $request->get('_firewall_context');

		$name = str_replace('security.firewall.map.context.', '', $name);

		$route = empty($firewalls[$name]['logout']['target']) ? $firewalls[$name]['logout']['target'] : 'logout' ;

		return $this->redirectToRoute($route);
	}

	/**
	 * @Route("/user/{id}/change/password/", name="forced_password_change")
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 */
	public function changePassword($id, Request $request, EntityManagerInterface $entityManager, AuthenticationUtils $authUtils, PasswordManager $passwordManager, TranslatorInterface $translator)
	{
		$user = $entityManager->getRepository(User::class)->find($id);

		$success = false;

		$error = $authUtils->getLastAuthenticationError();

		if (empty($user))
			throw new \Symfony\Component\Security\Core\Exception\InvalidArgumentException('The user was not found.');

		$form = $this->createForm(ChangePasswordType::class, $user);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$passwordManager->saveNewPassword($user);
			$error = new UserException($translator->trans('security.password.forced.success', [], 'security'));
			$success = true;

		}
		return $this->render('@HillrangeSecurity/security/forcedChangePassword.html.twig',
			[
				'form'  => $form->createView(),
				'error' => $error,
			]
		);
	}
	/**
	 * @Route("/keep/alive/", name="security_keep_alive")
	 * @IsGranted("IS_AUTHENTICATED_FULLY")
	 */
	public function keepAlive(Request $request)
	{
		$route = $request->get('_route');

		$params = $request->get('_route_params');

		return $this->redirectToRoute($route, $params);
	}
}