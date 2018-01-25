<?php
namespace Hillrange\Security\Validator;

use Hillrange\Security\Validator\Constraints\PasswordValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Password
 */
class Password extends Constraint
{
	public $message = 'security.password.error.message';

	public $transDomain = 'security';

	public $path = '';

	public function validatedBy()
	{
		return PasswordValidator::class;
	}
}