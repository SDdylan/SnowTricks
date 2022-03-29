<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    // /**
    //  * @return Trick[] Returns an array of Trick objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Trick
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function countAllTricks(): int
    {
        return $this->createQueryBuilder('t')
            ->select('count(t.id)')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public static function getNbPagesTricks($nbTricks) : int
    {
        //$nbTricks = self::countAllTricks(); NE FONCTIONNE PAS $this pose problème dans createQueryBuilder de countAllTricks()
        $nbPages = floatval($nbTricks/10);
        return ceil($nbPages);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTricksPages(int $nbPages = 1, int $nbTricks): array
    {
        $conn = $this->getEntityManager()->getConnection();
        if ($nbTricks > $nbPages*10) {
            if ($nbPages === 1) {
                $sql = "SELECT * FROM trick ORDER BY modified_at DESC LIMIT 10 ";
            } elseif ($nbPages > 1) {
                $sql = "SELECT * FROM trick ORDER BY modified_at DESC LIMIT 10 OFFSET " . ($nbPages-1)*10 ;
            }
        } else {
            $sql = "SELECT * FROM trick ORDER BY modified_at DESC LIMIT 10 OFFSET " . ($nbPages-1)*10 ;
        }
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }
}
