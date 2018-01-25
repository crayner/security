<?php
namespace Hillrange\Security\Exposed;

use Hillrange\Security\Util\ParameterInjector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
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
	private $router;

	/**
	 * LogoutSuccessHandler constructor.
	 *
	 * @param ParameterInjector $parameterInjector
	 */
	public function __construct(ParameterInjector $parameterInjector, RouterInterface $router)
	{
		$this->parameterInjector = $parameterInjector;
		$this->router = $router;
	}
	/**
	 * @param Request $request
	 *
	 * @return RedirectResponse
	 */
	public function onLogoutSuccess(Request $request)
	{
		$session = $request->getSession();
		$flash = $session->getFlashBag()->all();

		$session->clear();
		$session->invalidate();

		$session->getFlashBag()->setAll($flash);

		$request->setLocale($this->parameterInjector->getParameter('locale'));

		return new RedirectResponse($this->router->generate($this->parameterInjector->getParameter('security.routes.security_home')));
	}
}