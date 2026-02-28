<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $state = null;

    #[ORM\ManyToOne(inversedBy: 'createdTasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(inversedBy: 'assignedTasks')]
    private ?User $assignedTo = null;

    #[ORM\Column(length: 255)]
    private ?string $level = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childTask')]
    private ?self $parentTask = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentTask')]
    private Collection $childTask;

    public function __construct()
    {
        $this->childTask = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): static
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getParentTask(): ?self
    {
        return $this->parentTask;
    }

    public function setParentTask(?self $parentTask): static
    {
        $this->parentTask = $parentTask;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildTask(): Collection
    {
        return $this->childTask;
    }

    public function addChildTask(self $childTask): static
    {
        if (!$this->childTask->contains($childTask)) {
            $this->childTask->add($childTask);
            $childTask->setParentTask($this);
        }

        return $this;
    }

    public function removeChildTask(self $childTask): static
    {
        if ($this->childTask->removeElement($childTask)) {
            // set the owning side to null (unless already changed)
            if ($childTask->getparentTask() === $this) {
                $childTask->setparentTask(null);
            }
        }

        return $this;
    }
}
