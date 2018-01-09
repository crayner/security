<?php
namespace Hillrange\Security\Form;

use Hillrange\Security\Util\UserManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
	/**
	 * @var UserManager
	 */
	private $userManager;

	/**
	 * GroupType constructor.
	 *
	 * @param array $groups
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
					'class' => 'user',
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
		foreach ($this->userManager->getGroups() as $group => $roles)
			$groups[$group] = $group;

		return $groups;
	}
}
