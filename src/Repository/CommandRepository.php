<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Synolia\SyliusSchedulerCommandPlugin\Entity\Command;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

/**
 * @method Command|null find($id, $lockMode = null, $lockVersion = null)
 * @method Command|null findOneBy(array $criteria, array $orderBy = null)
 * @method Command[]    findAll()
 * @method Command[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandRepository extends EntityRepository implements CommandRepositoryInterface
{
    /**
     * @return CommandInterface[]
     */
    public function findEnabledCommand(): iterable
    {
        return $this->findBy(['enabled' => true], ['priority' => 'DESC']);
    }
}
