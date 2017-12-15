<?php
namespace HillRange\Security\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SecurityController
 * @package HillRange\Security\Controller
 */
class SecurityController extends AbstractController
{
	public function loginAction(Request $request)
	{
		return new Response('Security stuff here!');
	}
}