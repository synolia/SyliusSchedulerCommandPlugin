<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Repository;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;

/**
 * @method ScheduledCommandInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduledCommandInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduledCommandInterface[]    findAll()
 * @method ScheduledCommandInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface ScheduledCommandRepositoryInterface extends RepositoryInterface
{
    /**
     * @return ScheduledCommandInterface[]
     */
    public function findAllRunnable(): iterable;

    public function findLastCreatedCommand(CommandInterface $command): ?ScheduledCommandInterface;
}
