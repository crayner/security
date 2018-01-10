<?php
namespace Hillrange\Security\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
	/**
	 * @var array
	 */
	private $groups;

	/**
	 * GroupType constructor.
	 *
	 * @param array $groups
	 */
	public function __construct(array $groups)
	{
		$this->groups = $groups;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix()
	{
		return 'user_group';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'user_group';
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
				'multiple'           => true,
				'expanded'           => true,
				'label'              => 'user.groups.label',
				'required'           => false,
				'attr'               => array(
					'class' => 'user small',
				),
				'translation_domain' => 'security',
				'choices'            => $this->getGroupChoices(),
			)
		);
	}

	/**
	 * get Group Choices
	 *
	 * @version 10th March 2017
	 * @return array
	 */
	private function getGroupChoices()
	{
		$groups = [];
		foreach ($this->groups as $group => $roles)
			$groups[$group] = $group;

		return $groups;
	}
}
