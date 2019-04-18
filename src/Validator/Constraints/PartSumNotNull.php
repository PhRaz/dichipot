<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PartSumNotNull extends Constraint
{
    public $message = 'La somme des parts ne peut pas être nulle.';
}
