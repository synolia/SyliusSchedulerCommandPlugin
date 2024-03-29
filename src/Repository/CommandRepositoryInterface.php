<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Repository;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

/**
 * @method CommandInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandInterface[]    findAll()
 * @method CommandInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface CommandRepositoryInterface extends RepositoryInterface
{
    public function findEnabledCommand(): iterable;
}
