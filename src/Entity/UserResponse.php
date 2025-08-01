<?php

namespace App\Entity;

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

    #[ORM\Column(type: 'json')]
    #[Groups(['user_response'])]
    private array $answers = [];

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['user_response'])]
    private \DateTimeImmutable $completedAt;

    public function __construct()
    {
        $this->completedAt = new \DateTimeImmutable();
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
}