<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\This;

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

    public function countCommentsByUser(User $user)
    {
        try {
            return $this->createQueryBuilder('c')
                ->select('count(c.id)')
                ->andWhere('c.user = :val')
                ->setParameter('val', $user)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
        }
    }

    public function countCommentsByTrick(Trick $trick, bool $isVerified = false)
    {
        $query = $this->createQueryBuilder('c')
                        ->select('count(c.id)')
                        ->where('c.trick = :val')
                        ->setParameter('val', $trick);
        if ($isVerified === true) {
            $query = $query->andWhere('c.isVerified = true');
        }
        try {
            return $query->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {

        }
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
        $entityManager = $this->getEntityManager();
        if ($nbComments > $nbPages*10) {
            if ($nbPages === 1) {
                $query = $entityManager->createQuery("SELECT c FROM App\Entity\Comment c WHERE c.user = :u_id ORDER BY c.createdAt DESC")
                                        ->setParameter('u_id', $idUser)
                                        ->setMaxResults(10);
            } elseif ($nbPages > 1) {
                $query = $entityManager->createQuery("SELECT c FROM App\Entity\Comment c WHERE c.user = :u_id ORDER BY c.createdAt DESC")
                                        ->setParameter('u_id', $idUser)
                                        ->setFirstResult(($nbPages-1)*10)
                                        ->setMaxResults(10);
            }
        } else {
            $query = $entityManager->createQuery("SELECT c FROM App\Entity\Comment c WHERE c.user = :u_id ORDER BY c.createdAt DESC")
                ->setParameter('u_id', $idUser)
                ->setFirstResult(($nbPages-1)*10)
                ->setMaxResults(10);
            }
        return $query->getResult();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCommentsByTrickPages(int $nbPages = 1, int $nbComments, int $idTrick, bool $isVerified = false): array
    {
        if ($nbComments > $nbPages*10) {
            if ($nbPages === 1) {
                $query = $this->createQueryBuilder('c')
                                ->select('c')
                                ->where('c.trick = :t_id')
                                ->setParameter('t_id', $idTrick)
                                ->orderBy('c.createdAt', 'DESC')
                                ->setMaxResults(10);
                if ($isVerified === true) {
                    $query = $query->andWhere('c.isVerified = true');
                }
            } elseif ($nbPages > 1) {
                $query = $this->createQueryBuilder('c')
                    ->select('c')
                    ->where('c.trick = :t_id')
                    ->setParameter('t_id', $idTrick)
                    ->orderBy('c.createdAt', 'DESC')
                    ->setFirstResult(($nbPages-1)*10)
                    ->setMaxResults(10);
                if ($isVerified === true) {
                    $query = $query->andWhere('c.isVerified = true');
                }
            }
        } else {
            $query = $this->createQueryBuilder('c')
                ->select('c')
                ->where('c.trick = :t_id')
                ->setParameter('t_id', $idTrick)
                ->orderBy('c.createdAt', 'DESC')
                ->setFirstResult(($nbPages-1)*10)
                ->setMaxResults(10);
            if ($isVerified === true) {
                $query = $query->andWhere('c.isVerified = true');
            }
        }
        return $query->getQuery()->getResult();
    }
}
