<?php
namespace Hillrange\Security\Form;

use Hillrange\Security\Util\ParameterInjector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DirectRoleType extends AbstractType
{
	/**
	 * @var array
	 */
	private $roleHierarchy;

	/**
	 * DirectRoleType constructor.
	 *
	 */
	public function __construct(ParameterInjector $parameterInjector)
	{
		$this->roleHierarchy = $parameterInjector->getParameter('security.role_hierarchy.roles');

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
					'class' => 'user small',
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
		foreach ($this->roleHierarchy as $role => $subRoles)
			$roles[$role] = $role;

		return $roles;
	}
}