<?php

namespace App\Validator\Constraints;

use App\Entity\UserEvent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniquePseudoValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniquePseudo) {
            throw new UnexpectedTypeException($constraint, UniquePseudo::class);
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
            $pseudo = $userEvent->getPseudo();
            if (array_key_exists($pseudo, $counter)) {
                $counter[$pseudo]['count']++;
            } else {
                $counter[$pseudo] = [
                    'count' => 1,
                    'index' => $index
                ];
            }
        }
        foreach ($counter as $pseudo => $item) {
            if ($item['count'] > 1) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ pseudo }}', $pseudo)
                    ->atPath('[' . $item['index'] . '].pseudo')
                    ->addViolation();
            }
        }
    }
}
