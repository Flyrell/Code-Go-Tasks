<?php

namespace App\Repository;

use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Skill|null find($id, $lockMode = null, $lockVersion = null)
 * @method Skill|null findOneBy(array $criteria, array $orderBy = null)
 * @method Skill[]    findAll()
 * @method Skill[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SkillRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    /**
     * @return Skill[]
     */
    public function findAllOrdered()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select('s')
            ->from(Skill::class, 's')
            ->addOrderBy('s.name', 'asc')
            ->addOrderBy('s.level', 'desc')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return string[]
     */
    public function getSkillNames(): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select('s.name')
            ->distinct('s.name')
            ->from(Skill::class, 's')
            ->addOrderBy('s.name', 'asc')
        ;

        return $queryBuilder->getQuery()->getResult('COLUMN_HYDRATOR');
    }

    /**
     * @return int[]
     */
    public function getAllIds(): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select('s.id')
            ->from(Skill::class, 's')
        ;

        return $queryBuilder->getQuery()->getResult('COLUMN_HYDRATOR_INT');
    }

    /**
     * @param string $search
     *
     * @return Skill[]|array
     */
    public function autocomplete(string $search): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select('s')
            ->from(Skill::class, 's')
            ->where($queryBuilder->expr()->like(
                's.name',
                $queryBuilder->expr()->literal('%'.$search.'%')
            ))
            ->addOrderBy('s.name')
            ->addOrderBy('s.level', 'desc')
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
