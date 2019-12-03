<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity(repositoryClass="Synolia\SchedulerCommandPlugin\Repository\ScheduledCommandRepository")
 * @ORM\Table("scheduled_command")
 */
class ScheduledCommand implements ResourceInterface
{
    /**
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
     * @see http://www.abunchofutils.com/utils/developer/cron-expression-helper/
     * @var string
     * @ORM\Column(type="string")
     */
    private $cronExpression = '* * * * *';

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
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
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $priority = 0;

    /**
     * If true, command will be execute next time regardless cron expression
     *
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $executeImmediately = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $disabled = false;

    /** @psalm-suppress MissingReturnType */
    public function getId()
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

    public function getLastExecution(): ?\DateTime
    {
        return $this->lastExecution;
    }

    public function setLastExecution(?\DateTime $lastExecution): self
    {
        $this->lastExecution = $lastExecution;

        return $this;
    }

    public function getLastReturnCode(): ?int
    {
        return $this->lastReturnCode;
    }

    public function setLastReturnCode(?int $lastReturnCode): self
    {
        $this->lastReturnCode = $lastReturnCode;

        return $this;
    }

    public function getLogFile(): ?string
    {
        return $this->logFile;
    }

    public function setLogFile(?string $logFile): self
    {
        $this->logFile = $logFile;

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

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }
}
