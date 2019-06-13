<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=32)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserEvent", mappedBy="event")
     * @ORM\OrderBy({"pseudo" = "ASC"})
     * @CustomAssert\UniqueMailAddress
     * @CustomAssert\UniquePseudo
     */
    private $userEvents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Operation", mappedBy="event", orphanRemoval=true)
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $operations;

    public function __construct()
    {
        $this->userEvents = new ArrayCollection();
        $this->operations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return Collection|UserEvent[]
     */
    public function getUserEvents(): Collection
    {
        return $this->userEvents;
    }

    public function addUserEvent(UserEvent $userEvent): self
    {
        if (!$this->userEvents->contains($userEvent)) {
            $this->userEvents[] = $userEvent;
            $userEvent->setEvent($this);
        }

        return $this;
    }

    public function removeUserEvent(UserEvent $userEvent): self
    {
        if ($this->userEvents->contains($userEvent)) {
            $this->userEvents->removeElement($userEvent);
            // set the owning side to null (unless already changed)
            if ($userEvent->getEvent() === $this) {
                $userEvent->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Operation[]
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    public function addOperation(Operation $operation): self
    {
        if (!$this->operations->contains($operation)) {
            $this->operations[] = $operation;
            $operation->setEvent($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        if ($this->operations->contains($operation)) {
            $this->operations->removeElement($operation);
            // set the owning side to null (unless already changed)
            if ($operation->getEvent() === $this) {
                $operation->setEvent(null);
            }
        }

        return $this;
    }

    public function isUserParticipant(User $user): bool
    {
        /** @var UserEvent $userEvent */
        foreach ($this->getUserEvents() as $userEvent) {
            if ($userEvent->getUser() === $user) {
                return true;
            }
        }
        return false;
    }

    public function isUserAdmin(User $user): bool
    {
        /** @var UserEvent $userEvent */
        foreach ($this->getUserEvents() as $userEvent) {
            if ($userEvent->getUser() === $user &&
                $userEvent->getAdministrator() === true) {
                return true;
            }
        }
        return false;
    }
}
