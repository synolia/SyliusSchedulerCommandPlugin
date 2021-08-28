<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\ResourceInterface;

interface CommandInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getName(): string;

    public function setName(string $name): self;

    public function getCommand(): string;

    public function setCommand(string $command): self;

    public function getArguments(): ?string;

    public function setArguments(?string $arguments): self;

    public function getCronExpression(): string;

    public function setCronExpression(string $cronExpression): self;

    public function getLogFilePrefix(): ?string;

    public function setLogFilePrefix(?string $logFile): self;

    public function getPriority(): int;

    public function setPriority(int $priority): self;

    public function isExecuteImmediately(): bool;

    public function setExecuteImmediately(bool $executeImmediately): self;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): self;

    /**
     * @return Collection<array-key, \Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface>|\Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface[]
     */
    public function getScheduledCommands(): Collection;

    public function addScheduledCommand(ScheduledCommandInterface $scheduledCommand): self;

    public function removeScheduledCommand(ScheduledCommandInterface $scheduledCommand): self;
}
