<?php
namespace Hillrange\Security\Form\Subscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

		$data['usernameCanonical'] = trim($data['username']);
		$data['emailCanonical']    = trim(strtolower($data['email']));

        if (empty($data['enabled']))
            $data['enabled'] = false;
        if (empty($data['expired']))
            $data['expired'] = false;
        if (empty($data['credentials_expired']))
            $data['credentials_expired'] = false;

		$data['enabled'] = $data['enabled'] === '1' ? true : false;
		$data['expired'] = $data['expired'] === '1' ? true : false;
		$data['credentials_expired'] = $data['credentials_expired'] === '1' ? true : false;

		$event->setData($data);
	}
}