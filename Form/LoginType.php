<?php
namespace Hillrange\Security\Form;

use Hillrange\Security\Entity\Login;
use Hillrange\Security\Exposed\LoginSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
	/**
	 * @var LoginSubscriber
	 */
	private $loginSubscriber;

	/**
	 * LoginType constructor.
	 *
	 * @param LoginSubscriber $loginSubscriber
	 */
	public function __construct(LoginSubscriber $loginSubscriber)
	{
		$this->loginSubscriber = $loginSubscriber;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('_username', TextType::class,
				[
					'label' => 'security.login.username.label',
				]
			)
			->add('_password', PasswordType::class,
				[
					'label' => 'security.login.password.label',
					'required' => false,
				]
			)
			->add('_remember_me', CheckboxType::class,
				[
					'label' => 'security.login.remember_me.label',
					'required' => false,
				]
			)
			->add('login', SubmitType::class,
				[
					'label' => 'security.login.login.label',
				]
			)
			->add('password_reset', SubmitType::class,
				[
					'label' => 'security.login.reset_password.label',
					'attr'  => [
						'onclick' => "return this.form.action='".$options['password_reset_url']."'",
					],
				]
			)
			->setAction($options['login_url'])
		;
		$builder->addEventSubscriber($this->loginSubscriber);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(
			[
				'data_class'            => Login::class,
				'translation_domain'    => 'security',
				'csrf_token_id'         => 'authenticate',
				'password_reset_url'    => '',
				'login_url'             => '',
			]
		);
	}

}