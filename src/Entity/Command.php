<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepository")
 * @ORM\Table("synolia_commands")
 */
class Command implements CommandInterface
{
    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string") */
    private string $name = '';

    /** @ORM\Column(type="string") */
    private string $command = '';

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $arguments = null;

    /**
     * @see https://abunchofutils.com/u/computing/cron-format-helper/
     *
     * @ORM\Column(type="string")
     */
    private string $cronExpression = '* * * * *';

    /**
     * Log's file name prefix (without path), followed by a time stamp of the execution
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $logFilePrefix = null;

    /** @ORM\Column(type="integer") */
    private int $priority = 0;

    /**
     * If true, command will be execute next time regardless cron expression
     *
     * @ORM\Column(type="boolean")
     */
    private bool $executeImmediately = false;

    /** @ORM\Column(type="boolean") */
    private bool $enabled = true;

    /** @ORM\Column(type="integer", nullable=true) */
    private ?int $timeout = null;

    /** @ORM\Column(type="integer", nullable=true) */
    private ?int $idleTimeout = null;

    /** @ORM\OneToMany(targetEntity="Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface", mappedBy="owner") */
    private Collection $scheduledCommands;

    public function __construct()
    {
        $this->scheduledCommands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function setArguments(?string $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getCronExpression(): string
    {
        return $this->cronExpression;
    }

    public function setCronExpression(string $cronExpression): self
    {
        $this->cronExpression = $cronExpression;

        return $this;
    }

    public function getLogFilePrefix(): ?string
    {
        return $this->logFilePrefix;
    }

    public function setLogFilePrefix(?string $logFilePrefix): self
    {
        $this->logFilePrefix = $logFilePrefix;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function isExecuteImmediately(): bool
    {
        return $this->executeImmediately;
    }

    public function setExecuteImmediately(bool $executeImmediately): self
    {
        $this->executeImmediately = $executeImmediately;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Collection<array-key, \Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface>|\Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface[]
     */
    public function getScheduledCommands(): Collection
    {
        return $this->scheduledCommands;
    }

    public function addScheduledCommand(ScheduledCommandInterface $scheduledCommand): self
    {
        if ($this->scheduledCommands->contains($scheduledCommand)) {
            return $this;
        }

        $this->scheduledCommands->add($scheduledCommand);

        return $this;
    }

    public function removeScheduledCommand(ScheduledCommandInterface $scheduledCommand): self
    {
        if (!$this->scheduledCommands->contains($scheduledCommand)) {
            return $this;
        }

        $this->scheduledCommands->removeElement($scheduledCommand);
        // needed to update the owning side of the relationship!
        $scheduledCommand->setOwner(null);

        return $this;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(?int $timeout): CommandInterface
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getIdleTimeout(): ?int
    {
        return $this->idleTimeout;
    }

    public function setIdleTimeout(?int $idleTimeout): CommandInterface
    {
        $this->idleTimeout = $idleTimeout;

        return $this;
    }
}
