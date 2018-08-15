<?php
namespace Hillrange\Security\Validator\Constraints;

use Hillrange\Security\Entity\Password;
use Hillrange\Security\Util\PasswordManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CheckPasswordValidator extends ConstraintValidator
{
	/**
	 * @var PasswordManager
	 */
	private $passwordManager;

    /**
     * @var TokenStorageInterface
     */
	private $storage;

	/**
	 * PasswordValidator constructor.
	 */
	public function __construct(PasswordManager $passwordManager, TokenStorageInterface $storage)
	{
		$this->passwordManager = $passwordManager;
        $this->storage = $storage;
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

        $token = $this->storage->getToken();

        if (is_null($token))
            return ;

        $user = $token->getUser();
        if (! $user instanceof UserInterface)
            return $this->context->buildViolation('password.user.missing')
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();

		if (! $this->passwordManager->confirmPassword($user, $value))
            return $this->context->buildViolation('password.user.wrong')
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
	}
}