<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepository")
 * @ORM\Table("synolia_scheduled_commands")
 */
class ScheduledCommand implements ScheduledCommandInterface
{
    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name = '';

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $command = '';

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $arguments;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastExecution;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lastReturnCode;

    /**
     * Log's file name (without path)
     *
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $logFile;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $commandEndTime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ScheduledCommandInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): ScheduledCommandInterface
    {
        $this->command = $command;

        return $this;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function setArguments(?string $arguments): ScheduledCommandInterface
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getLastExecution(): ?\DateTime
    {
        return $this->lastExecution;
    }

    public function setLastExecution(?\DateTime $lastExecution): ScheduledCommandInterface
    {
        $this->lastExecution = $lastExecution;

        return $this;
    }

    public function getLastReturnCode(): ?int
    {
        return $this->lastReturnCode;
    }

    public function setLastReturnCode(?int $lastReturnCode): ScheduledCommandInterface
    {
        $this->lastReturnCode = $lastReturnCode;

        return $this;
    }

    public function getLogFile(): ?string
    {
        return $this->logFile;
    }

    public function setLogFile(?string $logFile): ScheduledCommandInterface
    {
        $this->logFile = $logFile;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): ScheduledCommandInterface
    {
        $this->priority = $priority;

        return $this;
    }

    public function getCommandEndTime(): ?\DateTime
    {
        return $this->commandEndTime;
    }

    public function setCommandEndTime(?\DateTime $commandEndTime): ScheduledCommandInterface
    {
        $this->commandEndTime = $commandEndTime;

        return $this;
    }
}
