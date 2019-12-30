<?php

namespace Synolia\SchedulerCommandPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface ScheduledCommandInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getName(): string;

    public function setName(string $name): ScheduledCommandInterface;

    public function getCommand(): string;

    public function setCommand(string $command): ScheduledCommandInterface;

    public function getArguments(): ?string;

    public function setArguments(?string $arguments): ScheduledCommandInterface;

    public function getCronExpression(): string;

    public function setCronExpression(string $cronExpression): ScheduledCommandInterface;

    public function getLastExecution(): ?\DateTime;

    public function setLastExecution(?\DateTime $lastExecution): ScheduledCommandInterface;

    public function getLastReturnCode(): ?int;

    public function setLastReturnCode(?int $lastReturnCode): ScheduledCommandInterface;

    public function getLogFile(): ?string;

    public function setLogFile(?string $logFile): ScheduledCommandInterface;

    public function getPriority(): int;

    public function setPriority(int $priority): ScheduledCommandInterface;

    public function isExecuteImmediately(): bool;

    public function setExecuteImmediately(bool $executeImmediately): ScheduledCommandInterface;

    public function isDisabled(): bool;

    public function setDisabled(bool $disabled): ScheduledCommandInterface;
}
