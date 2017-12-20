<?php
namespace Hillrange\Security\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package Hillrange\Security\Controller
 */
class SecurityController extends Controller
{
	/**
	 * @Route("/security/login/", name="hillrange_security_login")
	 */
	public function login(Request $request, AuthenticationUtils $authUtils = null)
	{
		// get the login error if there is one
		$error = is_null($authUtils) ? null : $authUtils->getLastAuthenticationError();

		// last username entered by the user
		$lastUsername = is_null($authUtils) ? null : $authUtils->getLastUsername();

		return $this->render('@HillrangeSecurity/login_content.html.twig',
			[
				'last_username' => $lastUsername,
				'error'         => $error,
			]
		);
	}

}