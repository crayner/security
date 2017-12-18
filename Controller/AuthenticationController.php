<?php
namespace Hillrange\Security\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthenticationController extends AbstractController
{
	/**
	 * @return RedirectResponse
	 */
	public function timeoutAction(Request $request)
	{
		$session = $request->getSession();

		$session->set('_timeout', false);

		$token = $this->get('security.token_storage');
		$token->setToken(null);
		$session->set('_timeout', true);

		$lapse = $this->get('busybee_core_system.setting.setting_manager')->get('idleTimeout', 15);

		$session->getFlashBag()->add(
			'info',
			$this->get('translator')->trans('security.session.timeout', array('%hours%' => '00', '%minutes%' => $lapse), 'BusybeeSecurityBundle')
		);

		$url = $this->generateUrl('home_page');

		return new RedirectResponse($url);
	}

}