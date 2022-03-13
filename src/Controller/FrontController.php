<?php

namespace App\Controller;

use App\Entity\Trick;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
}
