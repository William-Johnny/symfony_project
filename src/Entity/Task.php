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

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subtask')]
    private ?self $childTask = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'childTask')]
    private Collection $subtask;

    public function __construct()
    {
        $this->subtask = new ArrayCollection();
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

    public function getChildTask(): ?self
    {
        return $this->childTask;
    }

    public function setChildTask(?self $childTask): static
    {
        $this->childTask = $childTask;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSubtask(): Collection
    {
        return $this->subtask;
    }

    public function addSubtask(self $subtask): static
    {
        if (!$this->subtask->contains($subtask)) {
            $this->subtask->add($subtask);
            $subtask->setChildTask($this);
        }

        return $this;
    }

    public function removeSubtask(self $subtask): static
    {
        if ($this->subtask->removeElement($subtask)) {
            // set the owning side to null (unless already changed)
            if ($subtask->getChildTask() === $this) {
                $subtask->setChildTask(null);
            }
        }

        return $this;
    }
}
