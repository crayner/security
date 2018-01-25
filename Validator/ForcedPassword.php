<?php
namespace Hillrange\Security\Validator;

use Hillrange\Security\Validator\Constraints\ForcedPasswordValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Password
 */
class ForcedPassword extends Constraint
{
	public $transDomain = 'security';

	public $path = '';

	public $user;

	/**
	 * @return string
	 */
	public function validatedBy()
	{
		return ForcedPasswordValidator::class;
	}

	/**
	 * @return array
	 */
	public function getRequiredOptions()
	{
		return ['user'];
	}
}