<?php
namespace Hillrange\Security\Form;

use Hillrange\Security\Entity\Password;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewPasswordType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('plainPassword', RepeatedType::class, [
					'type'            => PasswordType::class,
					'first_options'   => [
						'label' => 'security.login.password.label',
					],
					'second_options'  => [
						'label' => 'security.login.password_confirmation.label'
					],
					'invalid_message' => $options['invalid_match_message'],
					'constraints'       =>
						[
							new \Hillrange\Security\Validator\Password(),
						]
				]
			)
			->add('save', SubmitType::class,
				[
					'label' => 'security.password.save.label',
				]
			)
		;
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(
			[
				'data_class'         => Password::class,
				'translation_domain' => 'security',
				'invalid_match_message' => 'security.password.match.error',
				'error_bubbling'    => true,
			]
		);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'new_password';
	}
}
