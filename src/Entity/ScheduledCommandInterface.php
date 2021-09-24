<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface ScheduledCommandInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getName(): string;

    public function setName(string $name): self;

    public function getCommand(): string;

    public function setCommand(string $command): self;

    public function getArguments(): ?string;

    public function setArguments(?string $arguments): self;

    public function getExecutedAt(): ?\DateTime;

    public function setExecutedAt(?\DateTime $lastExecution): self;

    public function getLastReturnCode(): ?int;

    public function setLastReturnCode(?int $lastReturnCode): self;

    public function getLogFile(): ?string;

    public function setLogFile(?string $logFile): self;

    public function getCommandEndTime(): ?\DateTime;

    public function setCommandEndTime(\DateTime $executeImmediately): self;

    public function setState(string $state): self;

    public function getState(): string;

    public function getOwner(): ?CommandInterface;

    public function setOwner(?CommandInterface $owner): self;

    public function getCreatedAt(): \DateTime;
}
