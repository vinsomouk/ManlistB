<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: "App\Repository\UserResponseRepository")]
class UserResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user_response'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_response'])]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Questionnaire::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_response'])]
    private Questionnaire $questionnaire;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['user_response'])]
    private \DateTimeImmutable $completedAt;

    #[ORM\OneToMany(
        targetEntity: ResponseItem::class, 
        mappedBy: 'userResponse',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $items;

    public function __construct()
    {
        $this->completedAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getQuestionnaire(): Questionnaire
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire(Questionnaire $questionnaire): self
    {
        $this->questionnaire = $questionnaire;
        return $this;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): self
    {
        $this->answers = $answers;
        return $this;
    }

    public function getCompletedAt(): \DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(\DateTimeImmutable $completedAt): self
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(ResponseItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setUserResponse($this);
        }
        return $this;
    }

    public function removeItem(ResponseItem $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getUserResponse() === $this) {
                $item->setUserResponse(null);
            }
        }
        return $this;
    }
}