<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function countCommentByUser(User $user)
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->andWhere('c.user = :val')
            ->setParameter('val', $user)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function countCommentByTrick(Trick $trick)
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.trick = :val')
            ->setParameter('val', $trick)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public static function getNbPagesComments($nbComments) : int
    {
        //$nbTricks = self::countAllTricks(); NE FONCTIONNE PAS $this pose problème dans createQueryBuilder de countAllTricks()
        $nbPages = floatval($nbComments/10);
        return ceil($nbPages);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCommentsByUserPages(int $nbPages = 1, int $nbComments, int $idUser): array
    {
        $conn = $this->getEntityManager()->getConnection();
        if ($nbComments > $nbPages*10) {
            if ($nbPages === 1) {
                $sql = "SELECT * FROM comment WHERE user_id = '" . $idUser . "' ORDER BY created_at DESC LIMIT 10 ";
            } elseif ($nbPages > 1) {
                $sql = "SELECT * FROM comment WHERE user_id = '" . $idUser . "' ORDER BY created_at DESC LIMIT 10 OFFSET " . ($nbPages - 1) * 10;
            }
        } else {
                $sql = "SELECT * FROM comment WHERE user_id = '" . $idUser . "' ORDER BY created_at DESC LIMIT 10 OFFSET " . ($nbPages-1)*10 ;
            }
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCommentsByTrickPages(int $nbPages = 1, int $nbComments, int $idTrick): array
    {
        $conn = $this->getEntityManager()->getConnection();
        if ($nbComments > $nbPages*10) {
            if ($nbPages === 1) {
                $sql = "SELECT * FROM comment WHERE trick_id = '" . $idTrick . "' ORDER BY created_at DESC LIMIT 10 ";
            } elseif ($nbPages > 1) {
                $sql = "SELECT * FROM comment WHERE trick_id = '" . $idTrick . "' ORDER BY created_at DESC LIMIT 10 OFFSET " . ($nbPages - 1) * 10;
            }
        } else {
            $sql = "SELECT * FROM comment WHERE trick_id = '" . $idTrick . "' ORDER BY created_at DESC LIMIT 10 OFFSET " . ($nbPages-1)*10 ;
        }
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }
}
