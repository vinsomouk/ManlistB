<?php

namespace App\Entity;

use App\Repository\AnimeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnimeRepository::class)]
class Anime
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    private int $id; // ID de l'API externe (AniList, MyAnimeList, etc.)

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $episodeCount = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $rawData = null; // Données brutes de l'API

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $lastSyncedAt;


    #[ORM\ManyToMany(targetEntity: Genre::class)]
    #[ORM\JoinTable(name: 'anime_genres')]
    private Collection $genres;

    public function __construct(int $id)
    {
        $this->id = $id;
        $this->lastSyncedAt = new \DateTimeImmutable();
        $this->title = '';
        $this->genres = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getEpisodeCount(): ?int
    {
        return $this->episodeCount;
    }

    public function setEpisodeCount(?int $episodeCount): self
    {
        $this->episodeCount = $episodeCount;
        return $this;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    public function setRawData(?array $rawData): self
    {
        $this->rawData = $rawData;
        return $this;
    }

    public function getLastSyncedAt(): \DateTimeImmutable
    {
        return $this->lastSyncedAt;
    }

    public function setLastSyncedAt(\DateTimeImmutable $lastSyncedAt): self
    {
        $this->lastSyncedAt = $lastSyncedAt;
        return $this;
    }

    public function isStale(): bool
    {
        return $this->lastSyncedAt->diff(new \DateTime())->days > 30;
    }

    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): self
    {
        if (!$this->genres->contains($genre)) {
            $this->genres[] = $genre;
        }
        return $this;
    }

    public function removeGenre(Genre $genre): self
    {
        $this->genres->removeElement($genre);
        return $this;
    }
}