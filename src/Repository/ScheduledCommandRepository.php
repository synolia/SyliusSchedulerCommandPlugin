<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Enum\ScheduledCommandStateEnum;

class ScheduledCommandRepository extends EntityRepository implements ScheduledCommandRepositoryInterface
{
    public function findAllRunnable(): iterable
    {
        /** @var iterable<ScheduledCommandInterface> $command */
        $command = $this->findBy(['state' => ScheduledCommandStateEnum::WAITING]);

        return $command;
    }

    public function findLastCreatedCommand(CommandInterface $command): ?ScheduledCommandInterface
    {
        try {
            return $this->createQueryBuilder('scheduled')
                        ->where('scheduled.owner = :owner')
                        ->setParameter('owner', $command->getId())
                        ->orderBy('scheduled.createdAt', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findAllSinceXDaysWithState(\DateTimeInterface $dateTime, array $states): iterable
    {
        return $this->createQueryBuilder('scheduled')
            ->where('scheduled.state IN (:states)')
            ->andWhere('scheduled.createdAt < :createdAt')
            ->setParameter('states', $states)
            ->setParameter('createdAt', $dateTime->format('Y-m-d 00:00:00'))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllHavingState(array $states): iterable
    {
        return $this->createQueryBuilder('scheduled')
            ->where('scheduled.state IN (:states)')
            ->setParameter('states', $states)
            ->getQuery()
            ->getResult()
        ;
    }
}
