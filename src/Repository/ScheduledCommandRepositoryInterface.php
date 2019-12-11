<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Repository;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;

/**
 * @method ScheduledCommand|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduledCommand|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduledCommand[]    findAll()
 * @method ScheduledCommand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface ScheduledCommandRepositoryInterface extends RepositoryInterface
{
    public function findEnabledCommand(): iterable;
}
