<?php
namespace Hillrange\Security\Form;

use Hillrange\Security\Entity\User;
use Hillrange\Security\Validator\CheckPassword;
use Hillrange\Security\Validator\Password;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('_username', HiddenType::class,
				[
					'label' => 'security.login.username.label',
				]
			)
			->add('currentPassword', PasswordType::class,
				[
					'label'    => 'security.login.current_password.label',
                    'constraints' => [
                        new CheckPassword(),
                        new NotBlank(),
                    ],
				]
			)
			->add('plainPassword', RepeatedType::class, [
					'type'            => PasswordType::class,
					'first_options'   => [
                        'label'       => 'security.login.password_new.label',
                        'help'       => 'security.login.password_new.help',
						'constraints' => [
							new Password(),
                            new NotBlank(),
						],
					],
					'second_options'  => [
						'label' => 'security.login.password_confirmation.label'
					],
					'invalid_message' => $options['invalid_match_message'],
				]
			)
			->add('change', SubmitType::class,
				[
					'label' => 'button.change.label',
				]
			)
		;

	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class'         => User::class,
			'translation_domain' => 'security',
			'invalid_match_message' => 'security.password.match.error',
			'error_bubbling'    => true,
		));
	}

}