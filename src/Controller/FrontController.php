<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Media;
use App\Entity\User;
use ContainerIjsU8sJ\getTrickRepositoryService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TrickRepository;

class FrontController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/front", name="front")
     */
    public function index(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(Request $request)
    {
        //Liste des tricks
        $tricks = $this->entityManager->getRepository(Trick::class)->findAll();

        //Liste des medias à afficher
        $media = $this->entityManager->getRepository(Media::class);
        $medias = [];
        foreach ($tricks as &$trick)
        {
            $medias[] = $media->findOneBy(['type' => 'image', 'trick' => $trick]);
        }



        /*
        $trick1 = $trick->find(25);
        $trick1->getUser()->getUsername();
        $med = $trick1->getMedia()->toArray();
        dump($med);
        dump($trick1);

        $tricktest = $trick->find(25)->getMedia()->getValues();
        dump($tricktest);

        $user1 = $this->entityManager->getRepository(User::class)->find(6);
        $user2 = $this->entityManager->getRepository(User::class)->find(5);
        $media1 = $this->entityManager->getRepository(Media::class)->find(6);
        $this->entityManager->flush();
        $user1->getUsername();
        dump($user1);
        dump($user2);
        dump($media1);
        exit;*/

        //Nombre total de tricks
        $tricksNumber = count($tricks);

        return $this->render('front/home.html.twig', [
            'tricks' => $tricks,
            'tricksNumber' => $tricksNumber,
            'medias' => $medias
        ]);
    }
}
