<?php

namespace App\Validator\Constraints;

use App\Entity\UserEvent;
use Aws\CostandUsageReportService\Exception\CostandUsageReportServiceException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class SameMailAddressValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof SameMailAddress) {
            throw new UnexpectedTypeException($constraint, SameMailAddress::class);
        }

        if (count($value) < 2) {
            /*
             * constraint do not apply
             */
            return;
        }

        $counter = [];
        /** @var UserEvent $userEvent */
        foreach ($value as $userEvent) {
            $email = $userEvent->getUser()->getMail();
            if (array_key_exists($email, $counter)) {
                $counter[$email]++;
            } else {
                $counter[$email] = 1;
            }
        }

        foreach ($counter as $email => $count) {
            if ($count > 1) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ email }}', $email)
                    ->addViolation();
            }
        }
    }
}
