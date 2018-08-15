<?php
namespace Hillrange\Security\Validator\Constraints;

use Hillrange\Security\Util\PasswordManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordValidator extends ConstraintValidator
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
     * validate
     *
     * @param mixed $value
     * @param Constraint $constraint
     */
	public function validate($value, Constraint $constraint)
	{
		if (empty($value))
			return ;

		if (! $this->passwordManager->isPasswordValid($value))
		{
			$this->context->buildViolation($constraint->message)
				->setParameter('%numbers%', $this->passwordManager->getPasswordSetting('numbers') ? 'Yes' : 'No')
				->setParameter('%mixedCase%', $this->passwordManager->getPasswordSetting('mixed_case') ? 'Yes' : 'No')
				->setParameter('%specials%', $this->passwordManager->getPasswordSetting('specials') ? 'Yes' : 'No')
				->setParameter('%minLength%', $this->passwordManager->getPasswordSetting('min_length'))
				->setParameter('%{password}', $value)
				->setTranslationDomain($constraint->transDomain)
				->addViolation();
		}

        $token = $this->storage->getToken();

        if (is_null($token))
            return ;

        $user = $token->getUser();

        if (! $user instanceof UserInterface)
            return $this->context->buildViolation('password.user.missing')
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();


        $y = $this->passwordManager->validatePasswordChange($user, $value);

        if ($y === true) return ;

        $v = $this->context->buildViolation($y[0]);
        foreach($y[1] as $name=>$val)
            $v->setParameter($name, $val);
        $v->atPath($y[2])
            ->setTranslationDomain($constraint->transDomain)
            ->addViolation();
	}
}