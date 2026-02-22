<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'createdBy', orphanRemoval: true)]
    private Collection $createdTasks;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'assignedTo')]
    private Collection $assignedTasks;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    public function __construct()
    {
        $this->createdTasks = new ArrayCollection();
        $this->assignedTasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getCreatedTasks(): Collection
    {
        return $this->createdTasks;
    }

    public function addCreatedTask(Task $createdTask): static
    {
        if (!$this->createdTasks->contains($createdTask)) {
            $this->createdTasks->add($createdTask);
            $createdTask->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedTask(Task $createdTask): static
    {
        if ($this->createdTasks->removeElement($createdTask)) {
            // set the owning side to null (unless already changed)
            if ($createdTask->getCreatedBy() === $this) {
                $createdTask->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getAssignedTasks(): Collection
    {
        return $this->assignedTasks;
    }

    public function addAssignedTask(Task $assignedTask): static
    {
        if (!$this->assignedTasks->contains($assignedTask)) {
            $this->assignedTasks->add($assignedTask);
            $assignedTask->setAssignedTo($this);
        }

        return $this;
    }

    public function removeAssignedTask(Task $assignedTask): static
    {
        if ($this->assignedTasks->removeElement($assignedTask)) {
            // set the owning side to null (unless already changed)
            if ($assignedTask->getAssignedTo() === $this) {
                $assignedTask->setAssignedTo(null);
            }
        }

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }
}
