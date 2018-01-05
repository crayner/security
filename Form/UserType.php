<?php
namespace Hillrange\Security\Form;

use App\Core\Type\TextType;
use App\Core\Type\ToggleType;
use Hillrange\Security\Entity\User;
use Hillrange\Security\Form\Subscriber\UserSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\DateFormatter\IntlDateFormatter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$years = [];
		$year  = intval(date('Y', strtotime('now')));
		for ($y = 0; $y < 5; $y++)
			$years[] = strval($year + $y);
		if (!is_null($options['data']->getCredentialsExpireAt()))
		{
			$years[] = $options['data']->getCredentialsExpireAt()->format('Y');
			$years   = array_unique($years);
			asort($years);
		}
		if (!is_null($options['data']->getExpiresAt()))
		{
			$years[] = $options['data']->getExpiresAt()->format('Y');
			$years   = array_unique($years);
			asort($years);
		}
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
			->add('enabled', CheckboxType::class,
				array(
					'label' => 'user.enabled.label',
					'attr'  => array(
						'class'     => 'user',
					),
				)
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
			->add('locked', ToggleType::class,
				array(
					'label' => 'user.locked.label',
					'attr'  => array(
						'class'     => 'user',
						'data-size' => 'mini',
					),
				)
			)
			->add('expired', ToggleType::class,
				array(
					'label' => 'user.expired.label',
					'attr'  => array(
						'class'     => 'user',
						'data-size' => 'mini',
					),
				)
			)
			->add('expiresAt', DateType::class,
				[
					'label'       => 'user.expires_at.label',
					'attr'        => [
						'help'  => 'user.expiresAt.help',
						'class' => 'user',
					],
					'years'       => $years,
					'placeholder' => [
						'year' => 'Y', 'month' => 'M', 'day' => 'D'
					],
					'format'      =>  'dMy',
					'required'    => false,
				]
			)
			->add('credentials_expired', ToggleType::class,
				array(
					'label' => 'user.credentials_expired.label',
					'attr'  => array(
						'help'      => 'user.help.credentials_expired',
						'class'     => 'user',
						'data-size' => 'mini',
					),
				)
			)
			->add('credentialsExpireAt', DateType::class,
				array(
					'label'       => 'user.credentials_expire_at.label',
					'attr'        => [
						'class' => 'user',
					],
					'years'       => $years,
					'placeholder' => [
						'year' => 'Y', 'month' => 'M', 'day' => 'D'
					],
					'required'    => false,
					'format'      =>  'dMy',
				)
			)
			->add('save', SubmitType::class,
				[
					'label' => 'button.save.label',
				]
			)
		;

		$builder->addEventSubscriber(new UserSubscriber($options['session'], $options['isSystemAdmin']));
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
		$resolver->setRequired(
			[
				'session',
				'isSystemAdmin',
			]
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
