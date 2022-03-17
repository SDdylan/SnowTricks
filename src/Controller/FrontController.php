<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\RegistrationFormType;
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
     * @Route ("/trick/{slugTrick}", name="trickDetails")
     */
    public function showTrick(string $slugTrick, Request $request, EntityManagerInterface $entityManager)
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['slug' => $slugTrick]);
        dump($trick);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setIsVerified(0);
            $comment->setUser($this->getUser());
            $comment->setTrick($trick);
            $comment->setCreatedAt(new \DateTime);

            $entityManager->persist($comment);
            $entityManager->flush();

            unset($comment);


            $this->addFlash(
                'notice',
                'Votre commentaire à été envoyé, il sera traité dans les plus brefs délais.'
            );

            return $this->redirect('/trick/' . $slugTrick);

        }
        return $this->render('front/trick.html.twig', [
            'trick' => $trick,
            'commentForm' => $form->createView(),
        ]);
    }
}
