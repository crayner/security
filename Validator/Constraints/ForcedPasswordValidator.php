<?php
namespace Hillrange\Security\Validator\Constraints;

use Hillrange\Security\Util\PasswordManager;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ForcedPasswordValidator extends ConstraintValidator
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

		$y = $this->passwordManager->isForcedPasswordValid($value);

		if (is_array($y))
		{
			$v = $this->context->buildViolation($y[0]);
			foreach($y[1] as $name=>$value)
				$v->setParameter($name, $value);
			$v->atPath($y[2]);
			$v->setTranslationDomain($constraint->transDomain)
				->addViolation();
			return ;
		}
	}
}