<?php
namespace Hillrange\Security\Form\Subscriber;

use Hillrange\Security\Form\DirectRoleType;
use Hillrange\Security\Form\GroupType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserSubscriber implements EventSubscriberInterface
{
	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			FormEvents::PRE_SUBMIT   => 'preSubmit',
		);
	}

	/**
	 * @param FormEvent $event
	 */
	public function preSubmit(FormEvent $event)
	{
		$data = $event->getData();

		if (empty($data['username']) && !empty($data['email']))
			$data['username'] = $data['email'];

		$data['usernameCanonical'] = $data['username'];
		$data['emailCanonical']    = $data['email'];

		$data['enabled'] = $data['enabled'] === '1' ? true : false;
		$data['expired'] = $data['expired'] === '1' ? true : false;
		$data['credentials_expired'] = $data['credentials_expired'] === '1' ? true : false;
dump($data);
		$event->setData($data);
	}
}