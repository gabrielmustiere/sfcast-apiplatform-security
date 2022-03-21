<?php

namespace App\Doctrine;

use App\Entity\CheeseListing;
use Symfony\Component\Security\Core\Security;

class CheeseListingSetOwnerListener
{
    public function __construct(private Security $security)
    {
    }

    public function prePersist(CheeseListing $cheeseListing)
    {
        if ($cheeseListing->getOwner()) {
            return;
        }

        if ($this->security->getUser()) {
            $cheeseListing->setOwner($this->security->getUser());
        }
    }
}
