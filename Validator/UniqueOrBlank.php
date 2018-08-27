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
 * Time: 19:07
 */
namespace Hillrange\Security\Validator;

use Hillrange\Security\Validator\Constraints\UniqueOrBlankValidator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class UniqueOrBlank
 * @package Hillrange\Security\Validator
 */
class UniqueOrBlank extends UniqueEntity
{
    /**
     * validatedBy
     *
     * @return string
     */
    public function validatedBy()
    {
        return UniqueOrBlankValidator::class;
    }
}