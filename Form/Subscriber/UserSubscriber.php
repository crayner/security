<?php
namespace Hillrange\Security\Form\Subscriber;

use Busybee\Core\SecurityBundle\Form\DirectRoleType;
use Busybee\Core\SecurityBundle\Form\GroupType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserSubscriber implements EventSubscriberInterface
{
	/**
	 * @var SessionInterface
	 */
	private $session;

	/**
	 * @var bool
	 */
	private $isSystemAdmin;

	/**
	 * UserSubscriber constructor.
	 *
	 * @param SessionInterface $session
	 * @param bool    $isSystemAdmin
	 */
	public function __construct(SessionInterface $session, $isSystemAdmin = false)
	{
		$this->session       = $session;
		$this->isSystemAdmin = $isSystemAdmin;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			FormEvents::PRE_SUBMIT   => 'preSubmit',
			FormEvents::PRE_SET_DATA => 'preSetData',
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

		$event->setData($data);
	}

	public function preSetData(FormEvent $event)
	{

		if ($this->isSystemAdmin)
		{
			$form = $event->getForm();
			$form
				->add('directroles', DirectRoleType::class)
				->add('groups', GroupType::class);
		}
	}
}