<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Employee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    /**
     * @return int
     *
     * @throws NonUniqueResultException
     */
    public function getMaxId()
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->select('MAX(e.id)')
            ->from(Employee::class, 'e')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param int $fromId
     * @param int $toId
     *
     * @return Employee[]|IterableResult
     */
    public function findByIdRange(int $fromId, int $toId): IterableResult
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('e')
            ->from(Employee::class, 'e')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte('e.id', $fromId),
                    $queryBuilder->expr()->lte('e.id', $toId)
                )
            );

        return $queryBuilder->getQuery()->iterate();
    }

    /**
     * @param int $skillId
     *
     * @return int[]
     */
    public function findEmployeeIdListBySkillId(int $skillId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('employee_id', 'employee_id');
        $nativeQuery = $this->getEntityManager()->createNativeQuery('SELECT employee_id FROM employees_skills WHERE skill_id = :skillId', $rsm);
        $nativeQuery->setParameter('skillId', $skillId);

        return $nativeQuery->getResult('COLUMN_HYDRATOR_INT');
    }
}
