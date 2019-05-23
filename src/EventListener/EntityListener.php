<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class EntityListener
{
    /**
     * Update the user name, based on mail address.
     * This action update the user name when user mail address is changed in event configuration.
     *
     * @param PreUpdateEventArgs $eventArgs Event args
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        if ($eventArgs->getEntity() instanceof User) {
            if ($eventArgs->hasChangedField('mail')) {
                $newName = explode('@', $eventArgs->getNewValue('mail'))[0];
                /** @var User $user */
                $user = $eventArgs->getEntity();
                $user->setName($newName);
            }
        }
    }
}
