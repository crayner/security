<?php
namespace Hillrange\Security\Form\Subscriber;

use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class LoginSubscriber implements EventSubscriberInterface
{
	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents()
	{
		return [
			FormEvents::PRE_SET_DATA => 'preSetData',
		];
	}

	/**
	 * @var FirewallConfig
	 */
	private $firewallMap;

	/**
	 * LoginType constructor.
	 *
	 * @param FirewallConfig $firewallMap
	 */
	public function __construct(FirewallConfig $firewallMap)
	{
		$this->firewallMap = $firewallMap;
	}

	/**
	 * @param FormEvent $event
	 */
	public function preSetData(FormEvent $event)
	{
		if (! in_array('remember_me', $this->firewallMap->getListeners()))
		{
			$form = $event->getForm();
			$form->remove('_remember_me');
		}
	}
}