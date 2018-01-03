<?php
namespace Hillrange\Security\Form;

use Hillrange\Security\Entity\User;
use Hillrange\Security\Validator\Password;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewPasswordType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('plainPassword', RepeatedType::class, [
					'type'            => PasswordType::class,
					'first_options'   => array(
						'label' => 'security.login.password.label',
						'constraints' => [
							new Password(),
						],
					),
					'second_options'  => array(
						'label' => 'security.login.password_confirmation.label'
					),
					'invalid_message' => $options['invalid_match_message'],
				]
			)
			->add('save', SubmitType::class,
				[
					'label' => 'security.password.save.label',
				]
			)
		;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class'         => User::class,
			'translation_domain' => 'security',
			'invalid_match_message' => 'security.password.match.error',
			'error_bubbling'    => true,
		));
	}


	public function getName()
	{
		return 'new_password';
	}
}
