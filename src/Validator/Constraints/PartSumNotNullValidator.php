<?php

namespace App\Validator\Constraints;

use App\Entity\Expense;
use App\Entity\UserEvent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PartSumNotNullValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PartSumNotNull) {
            throw new UnexpectedTypeException($constraint, PartSumNotNull::class);
        }

        if (count($value) < 2) {
            /*
             * constraint do not apply
             */
            return;
        }

        $sum = 0;
        /**
         * @var  integer $index
         * @var  Expense $expense
         */
        foreach ($value as $index => $expense) {
            $sum += $expense->getPayment();
        }

        if ($sum == 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('[0].payment')
                ->addViolation();
        }
    }
}
