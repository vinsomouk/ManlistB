<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\ResponseItemRepository")]
class ResponseItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(
        targetEntity: UserResponse::class, 
        inversedBy: 'items',
        cascade: ['persist']
    )]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserResponse $userResponse;

    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Question $question;

    #[ORM\ManyToOne(targetEntity: AnswerOption::class)]
    #[ORM\JoinColumn(nullable: false)]
    private AnswerOption $answer;

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserResponse(): UserResponse
    {
        return $this->userResponse;
    }

    public function setUserResponse(UserResponse $userResponse): self
    {
        $this->userResponse = $userResponse;
        return $this;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getAnswer(): AnswerOption
    {
        return $this->answer;
    }

    public function setAnswer(AnswerOption $answer): self
    {
        $this->answer = $answer;
        return $this;
    }
}