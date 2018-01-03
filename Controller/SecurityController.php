<?php
namespace Hillrange\Security\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Hillrange\Security\Entity\Login;
use Hillrange\Security\Entity\User;
use Hillrange\Security\Exception\UserException;
use Hillrange\Security\Form\LoginType;
use Hillrange\Security\Form\NewPasswordType;
use Hillrange\Security\Repository\UserRepository;
use Hillrange\Security\Util\PasswordManager;
use Hillrange\Security\Util\TokenGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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

		return $this->render('@hillrange_security/security/login.html.twig',
			[
				'last_username' => $lastUsername,
				'error'         => $error,
				'form'          => $form->createView(),
			]
		);
	}


	public function checkAction()
	{
		throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
	}

	/**
	 * @Route("/logout/", name="logout")
	 */
	public function logoutAction()
	{
		throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
	}
	/**
	 * @Route("/password/request/reset/", name="password_request_reset")
	 * @Method({"POST"})
	 */
	public function requestPasswordReset(Request $request, \Swift_Mailer $mailer, TokenGenerator $tokenGenerator, TranslatorInterface $translator, EntityManagerInterface $entityManager)
	{

		$lastUsername = $request->get('login')['_username'];

		$error = null;
		$comment = '';

		$user = $entityManager->getRepository(User::class)->loadUserByUsername($lastUsername);

		if (! $user)
			$error = new UserException('security.user.not_found', ['%{username}' => $lastUsername]);
		else {
			if ($user->getConfirmationToken() && $user->isPasswordRequestNonExpired($this->getParameter('security_password_reset_token_ttl')))
			{
				$error = new UserException('security.password.reset.exists', ['%{username}' => $lastUsername]);
				$comment = "<p>".$translator->trans('security.password.reset.email.copy', [], 'security')."</p>";
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
							'%{username}' => $user->formatName(),
							'%{siteName}' => 'Site Security System',
							'%{comment}' => $comment,
							'%{datetime}' => date('d M/Y H:i', $user->getPasswordRequestedAt()->getTimestamp() + 86400)
						], 'security'),
					'text/html'
				)
			;

			$mailer->send($message);
		}

		$login = new Login();
		$login->setUsername($lastUsername);

		$form = $this->createForm(LoginType::class, $login, ['password_reset_url' => $this->generateUrl('password_request_reset'), 'login_url' => $this->generateUrl('login')]);
		if (! $error)
			$error = new UserException('security.password.reset.email.sent', ['%{email}' => $user->obfuscateEmail()]);

		return $this->render('security/login.html.twig',
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
	public function newPassword(Request $request, $token = null, UserRepository $userRepository, TranslatorInterface $translator, PasswordManager $passwordManager, EntityManagerInterface $entityManager)
	{
		$user = $userRepository->loadUserByToken($token);

		$error = null;

		if (! $user)
		{
			$error = new UserException($translator->trans('security.user.token.not_found', ['%{token}' => $token], 'security'));
			return $this->render('security/error.html.twig',
				[
					'error'         => $error,
				]
			);

		}
		$form = $this->createForm(NewPasswordType::class, $user, ['invalid_match_message' => $translator->trans('security.password.match.error', [], 'security')]);

		$form->handleRequest($request);

		$success = false;

		if ($form->isSubmitted() && $form->isValid())
		{
			$password = $form->get('plainPassword')->get('first')->getData();
			$user->setPassword($passwordManager->encodePassword($user, $password));
			$user->setConfirmationToken(null);
			$entityManager->persist($user);
			$entityManager->flush();
			$error = new UserException($translator->trans('security.password.change.success', ['%{token}' => $token], 'security'));
			$success = true;
		}
		if ($form->isSubmitted() && ! $form->isValid())
		{
			$error = $form->get('plainPassword')->get('first')->getErrors();
			$error = $error->getChildren();
			$error = new UserException($error->getMessage());
			$form = $this->createForm(NewPasswordType::class, $user, ['invalid_match_message' => $translator->trans('security.password.match.error', [], 'security')]);
		}

		return $this->render('security/newpasswordbytoken.html.twig',
			[
				'error'         => $error,
				'form'          => $form->createView(),
				'manager'       => $passwordManager,
				'success'       => $success,
			]
		);
	}

	/**
	 * @Route("/user/{id}/edit/", name="hillrange_security_user_edit")
	 */
	public function editUser($id)
	{

	}

	/**
	 * @Route("/timeout/", name="hillrange_security_timeout")
	 */
	public function timeoutAction(TokenStorageInterface $token)
	{
		$session = new Session();

		$session->set('_timeout', false);

		$token->setToken(null);
		$session->set('_timeout', true);

		$lapse = $this->getParameter('idleTimeout', 15);

		$session->getFlashBag()->add(
			'info',
			$this->get('translator')->trans('security.user.timeout', array('%hours%' => '00', '%minutes%' => $lapse), 'security')
		);

		return $this->redirect('/');
	}

	/**
	 * @param string $name
	 * @param null   $default
	 *
	 * @return mixed
	 */
	protected function getParameter(string $name, $default = null)
	{
		if (is_null($default))
			return $this->container->getParameter($name);

		$x = null;
		try {
			$x = $this->container->getParameter($name);
		} catch (InvalidArgumentException $e) {
			return $default;
		}

		return $x;
	}
}