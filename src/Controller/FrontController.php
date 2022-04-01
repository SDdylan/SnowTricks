<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentFormType;
use App\Form\RegistrationFormType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="home")
     */
    public function home(Request $request): Response
    {
        //Liste des tricks
        $tricks = $this->entityManager->getRepository(Trick::class)->findAll();

        //Nombre total de tricks
        $tricksNumber = count($tricks);

        return $this->render('front/home.html.twig', [
            'tricks' => $tricks,
            'tricksNumber' => $tricksNumber,
        ]);
    }

    /**
     * @Route ("/trick/{idTrick}-{slugTrick}", name="trick_details")
     */
    public function showTrick(string $slugTrick, int $idTrick, Request $request, EntityManagerInterface $entityManager,CommentRepository $commentRepository)
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['id' => $idTrick]);

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        //Comments & paging
        $nbComments = $commentRepository->countCommentsByTrick($trick, true);
        $nbPages = $commentRepository->getNbPagesComments($nbComments);
        $page = $_GET['page'] ?? 1;
        $comments = $commentRepository->getCommentsByTrickPages($page, $nbPages, $idTrick, true);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setIsVerified(0);
            $comment->setUser($this->getUser());
            $comment->setTrick($trick);
            $comment->setCreatedAt(new \DateTime);

            $entityManager->persist($comment);
            $entityManager->flush();

            unset($comment);

            $this->addFlash(
                'success',
                'Votre commentaire à été envoyé, il sera traité dans les plus brefs délais.'
            );

            return $this->redirect('/trick/'. $idTrick . '-' . $slugTrick);

        }
        return $this->render('front/trick.html.twig', [
            'trick' => $trick,
            'commentForm' => $form->createView(),
            'nbPages' => $nbPages,
            'currentPage' => $page,
            'comments' => $comments
        ]);
    }
}
