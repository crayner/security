<?php
namespace Hillrange\Security\Form;

use Hillrange\Security\Entity\User;
use Hillrange\Security\Form\Subscriber\UserSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('username', TextType::class, array(
					'label'    => 'user.username.label',
					'attr'     => array(
						'class' => 'user',
					),
					'required' => false,
				)
			)
			->add('usernameCanonical', HiddenType::class,
				array(
					'attr' => array(
						'class' => 'user',
					),
				)
			)
			->add('email', TextType::class, array(
					'attr'  => array(
						'class' => 'user',
					),
					'label' => 'user.email.label',
				)
			)
			->add('emailCanonical', HiddenType::class, array(
					'attr' => array(
						'class' => 'user',
					),
				)
			)
			->add('enabled', HiddenType::class, [
					'attr' => array(
						'class' => 'user',
					),
				]
			)
			->add('locale', LocaleType::class,
				array(
					'label'    => 'user.locale.label',
					'attr'     => array(
						'class' => 'user',
					),
					'required' => false,
				)
			)
			->add('password', HiddenType::class,
				array(
					'attr' => array(
						'class' => 'user',
					)
				)
			)
			->add('expired', HiddenType::class, [
					'attr' => array(
						'class' => 'user',
					),
				]
			)
			->add('expiresAt', DateType::class,
				[
					'label'       => 'user.expires_at.label',
					'attr'        => [
						'help'  => 'user.expiresAt.help',
						'class' => 'user',
						'readonly'  => true,
					],
					'required'    => false,
					'widget' => 'single_text',
					'format' => 'yyyy-MM-dd',
				]
			)
			->add('credentials_expired', HiddenType::class, [
					'attr' => array(
						'class' => 'user',
					),
				]
			)
			->add('credentialsExpireAt', DateType::class,
				array(
					'label'       => 'user.credentials_expire_at.label',
					'attr'        => [
						'class' => 'user',
						'readonly'  => true,
					],
					'required'    => false,
					'widget' => 'single_text',
					'format' => 'yyyy-MM-dd',
				)
			)
		;

		$builder->addEventSubscriber(new UserSubscriber());
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
				'data_class'         => User::class,
				'translation_domain' => 'security',
			)
		);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'user';
	}
}
