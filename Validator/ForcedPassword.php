<?php
namespace Hillrange\Security\Validator;

use Hillrange\Security\Validator\Constraints\ForcedPasswordValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Password
 */
class ForcedPassword extends Constraint
{
	public $message = 'security.password.error_message';

	public $transDomain = 'security';

	public $path = '';

	/**
	 * @return string
	 */
	public function validatedBy()
	{
		return ForcedPasswordValidator::class;
	}
}