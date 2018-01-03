<?php
namespace Hillrange\Security\Validator\Constraints;

use Hillrange\Security\Util\PasswordManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordValidator extends ConstraintValidator
{
	/**
	 * @var PasswordManager
	 */
	private $passwordManager;

	/**
	 * PasswordValidator constructor.
	 */
	public function __construct(PasswordManager $passwordManager)
	{
		$this->passwordManager = $passwordManager;
	}

	/**
	 * Validate
	 *
	 * @param mixed      $value
	 * @param Constraint $constraint
	 */
	public function validate($value, Constraint $constraint)
	{
		if (empty($value))
			return;

		if (! $this->passwordManager->isPasswordValid($value))
		{
			$this->context->buildViolation($constraint->message)
				->setParameter('%numbers%', $this->passwordManager->getPasswordSetting('numbers') ? 'Yes' : 'No')
				->setParameter('%mixedCase%', $this->passwordManager->getPasswordSetting('mixed_case') ? 'Yes' : 'No')
				->setParameter('%specials%', $this->passwordManager->getPasswordSetting('specials') ? 'Yes' : 'No')
				->setParameter('%minLength%', $this->passwordManager->getPasswordSetting('min_length'))
				->setTranslationDomain($constraint->transDomain)
				->addViolation();
		}
	}
}