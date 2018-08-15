<?php
namespace Hillrange\Security\Validator;

use Hillrange\Security\Validator\Constraints\CheckPasswordValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Class CheckPassword
 * @package Hillrange\Security\Validator
 */
class CheckPassword extends Constraint
{
	public $transDomain = 'security';

	public $path = '';

	/**
	 * @return string
	 */
	public function validatedBy()
	{
		return CheckPasswordValidator::class;
	}
}