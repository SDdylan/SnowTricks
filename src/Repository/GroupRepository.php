<?php

namespace App\Repository;

use App\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    // /**
    //  * @return Group[] Returns an array of Group objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Group
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function countAllGroups(): int
    {
        return $this->createQueryBuilder('g')
            ->select('count(g.id)')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public static function getNbPagesGroups($nbGroupes) : int
    {
        //$nbTricks = self::countAllTricks(); NE FONCTIONNE PAS $this pose problème dans createQueryBuilder de countAllTricks()
        $nbPages = floatval($nbGroupes/10);
        return ceil($nbPages);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getGroupsPages(int $nbPages = 1, int $nbGroupes): array
    {
        $conn = $this->getEntityManager()->getConnection();
        if ($nbGroupes > $nbPages*10) {
            if ($nbPages === 1) {
                $sql = "SELECT * FROM `group` ORDER BY id DESC LIMIT 10 ";
            } elseif ($nbPages > 1) {
                $sql = "SELECT * FROM `group` ORDER BY id DESC LIMIT 10 OFFSET " . ($nbPages-1)*10 ;
            }
        } else {
            $sql = "SELECT * FROM `group` ORDER BY id DESC LIMIT 10 OFFSET " . ($nbPages-1)*10 ;
        }
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

}
