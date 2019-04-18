<?php

namespace App\Validator\Constraints;

use App\Entity\UserEvent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
        foreach ($value as $index => $userEvent) {
            $email = $userEvent->getUser()->getMail();
            if (array_key_exists($email, $counter)) {
                $counter[$email]['count']++;
            } else {
                $counter[$email] = [
                    'count' => 1,
                    'index' => $index
                ];
            }
        }
        foreach ($counter as $email => $item) {
            if ($item['count'] > 1) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ email }}', $email)
                    ->atPath('[' . $item['index'] . '].user.mail')
                    ->addViolation();
            }
        }
    }
}
