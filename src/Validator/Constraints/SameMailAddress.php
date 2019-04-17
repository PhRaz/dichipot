<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SameMailAddress extends Constraint
{
    public $message = 'L\'adresse mail "{{ email }}" est utilisée plusieurs fois. Chaque participant doit avoir une adresse mail différente.';
}
