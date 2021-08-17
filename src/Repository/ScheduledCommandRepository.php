<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Synolia\SyliusSchedulerCommandPlugin\Enum\ScheduledCommandStateEnum;

class ScheduledCommandRepository extends EntityRepository implements ScheduledCommandRepositoryInterface
{
    public function findAllRunnable(): iterable
    {
        return $this->findBy(['state' => ScheduledCommandStateEnum::WAITING]);
    }
}
