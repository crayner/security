<?php
namespace Hillrange\Security\Exposed;

use Hillrange\Security\Util\ParameterInjector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
	/**
	 * @var ParameterInjector
	 */
	private $parameterInjector;

	/**
	 * @var \Twig_Environment
	 */
	private $twig;

	/**
	 * LogoutSuccessHandler constructor.
	 *
	 * @param ParameterInjector $parameterInjector
	 */
	public function __construct(ParameterInjector $parameterInjector, \Twig_Environment $twig)
	{
		$this->parameterInjector = $parameterInjector;
		$this->twig = $twig;
	}
	/**
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response|void
	 */
	public function onLogoutSuccess(Request $request)
	{
		$session = $request->getSession();
		$flash = $session->getFlashBag()->all();

		$session->clear();
		$session->invalidate();

		$session->getFlashBag()->setAll($flash);

		return new Response($this->twig->render('@HillrangeSecurity/security/logout.html.twig'));
	}
}