<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniquePseudo extends Constraint
{
    public $message = 'Le pseudo "{{ pseudo }} est utilisé plusieurs fois. Chaque participant doit avoir un pseudo unique.';
}
