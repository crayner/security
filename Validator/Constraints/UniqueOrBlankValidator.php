<?php
/**
 * Created by PhpStorm.
 *
 * This file is part of the Busybee Project.
 *
 * (c) Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 27/08/2018
 * Time: 19:08
 */
namespace Hillrange\Security\Validator\Constraints;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueOrBlankValidator
 * @package Hillrange\Security\Validator\Constraints
 */
class UniqueOrBlankValidator extends UniqueEntityValidator
{

    public function validate($entity, Constraint $constraint)
    {
        $blank = true;
        foreach($constraint->fields as $field)
        {
            if (method_exists($entity, 'get' . ucfirst($field)))
            {
                $method = 'get' . ucfirst($field);
                if (!empty($entity->$method()))
                {
                    $blank = false;
                    break;
                }
            }
        }

        if (! $blank)
            return parent::validate($entity, $constraint);
    }
}