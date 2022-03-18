<?php

namespace App\Controller;

use App\Entity\Trick;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin", name="app_admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/trick/delete/{idTrick}", name="delete_trick")
     * @IsGranted("ROLE_USER")
     */
    public function deleteTrick(int $idTrick, Request $request, EntityManagerInterface $entityManager)
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['id' => $idTrick]);


        //Suppression de tout les media du trick
        foreach ($trick->getMedia() as $media)
        {
            $entityManager->remove($media);
        }

        $entityManager->remove($trick);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'La figure ' . $trick->getTitle() . ' à été supprimée avec succès !'
        );

        return $this->redirectToRoute('home');
    }
}
