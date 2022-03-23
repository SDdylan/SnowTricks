<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Media;
use App\Entity\Trick;
use App\Form\RegistrationFormType;
use App\Form\TrickFormType;
use DateTime;
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

    /**
     * @Route("/trick/new", name="add_trick")
     * @IsGranted("ROLE_USER")
     */
    public function addTrick(Request $request, EntityManagerInterface $entityManager)
    {
        $trick = new Trick();

        $form = $this->createForm(TrickFormType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $trick = $form->getData();

            $trick->setCreatedAt(new DateTime());
            $trick->setUser($this->getUser());
            $trick->slugify($trick->getTitle());

            $entityManager->persist($trick);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'La figure ' . $trick->getTitle() . ' à été ajoutée avec succès !'
            );

            return $this->redirectToRoute('home');
        }

        return $this->render('admin/add_trick.html.twig', [
            'trickForm' => $form->createView(),
        ]);
    }
}
