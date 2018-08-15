<?php
namespace Hillrange\Security\Validator;

use Hillrange\Security\Validator\Constraints\PasswordValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Class Password
 * @package Hillrange\Security\Validator
 */
class Password extends Constraint
{
	public $message = 'security.password.error.message';

	public $transDomain = 'security';

	public $path = '';

    /**
     * validatedBy
     *
     * @return string
     */
	public function validatedBy()
	{
		return PasswordValidator::class;
	}
}