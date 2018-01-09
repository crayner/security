<?php
namespace Hillrange\Security\Form;

use Hillrange\Security\Entity\User;
use Hillrange\Security\Util\UserManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DirectRoleType extends AbstractType
{
	/**
	 * @var UserManager
	 */
	private $userManager;

	/**
	 * DirectRoleType constructor.
	 *
	 * @param $roles
	 */
	public function __construct(UserManager $userManager)
	{
		$this->userManager = $userManager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix()
	{
		return 'user_directrole';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'user_directrole';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParent()
	{
		return ChoiceType::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(
			array(
				'label'              => 'user.direct_roles.label',
				'multiple'           => true,
				'expanded'           => true,
				'required'           => false,
				'attr'               => array(
					'class' => 'user',
				),
				'translation_domain' => 'security',
				'choices'            => $this->getRoleChoices(),
			)
		);
	}

	/**
	 * get Role Choices
	 *
	 * @version 11th March 2017
	 * @return array
	 */
	private function getRoleChoices()
	{
		$roles = [];
		foreach ($this->userManager->getRoles() as $role => $subRoles)
			$roles[$role] = $role;

		return $roles;
	}
}