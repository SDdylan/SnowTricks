<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Media;
use App\Entity\Trick;
use App\Form\MediaFormType;
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
            $trick->removeMedium($media);
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

            //On s'assure qu'il y ait bien au moins une image et une vidéo
            $videos = 0;
            $images = 0;
            foreach ($trick->getMedia() as $media) {
                if ($media->getType() == 'video') {
                    $videos++;
                } else {
                    $images++;
                }
                if($images > 0 && $videos > 0) {
                    break;
                }
            }

            if ($images==0 || $videos==0) {
                $this->addFlash(
                    'warning',
                    'Erreur : Vous devez ajouter au moins une image et une vidéo à la figure.'
                );
            } else {

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
        }

        return $this->render('admin/add_trick.html.twig', [
            'trickForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/trick/{idTrick}-{slugTrick}/edit", name="edit_trick")
     * @IsGranted("ROLE_USER")
     */
    public function editTrick(string $slugTrick, int $idTrick, Request $request, EntityManagerInterface $entityManager)
    {
        $trick = $this->entityManager->getRepository(Trick::class)->findOneBy(['id' => $idTrick]);

        $form = $this->createForm(TrickFormType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $form->getData();
            //Update du slug et de la date de modification
            $trick->slugify($trick->getTitle());
            $trick->setModifiedAt(new DateTime());

            $entityManager->persist($trick);

            $entityManager->flush();

            //addFlash & redirect
            $this->addFlash(
                'success',
                'La figure ' . $trick->getTitle() . ' à été modifiée avec succès !'
            );

            return $this->redirect('/trick/' . $trick->getId() . '-' . $trick->getSlug());
        }

        return $this->render('admin/edit_trick.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/media/delete/{idMedia}", name="delete_media")
     * @IsGranted("ROLE_USER")
     */
    public function deleteMedia(int $idMedia, EntityManagerInterface $entityManager)
    {
        //AJOUTER TRY CATCH
        $media = $this->entityManager->getRepository(Media::class)->find($idMedia);
        $trick = $media->getTrick();

        dump($media);
        dump($trick);

        $trick->removeMedium($media);
        $entityManager->remove($media);

        $entityManager->flush();

        $this->addFlash(
            'success',
            'La média à été supprimé avec succès !'
        );

        return $this->redirect('/trick/' . $trick->getId() . '-' . $trick->getSlug() . '/edit');
    }

    /**
     * @Route("/media/{idMedia}/edit", name="edit_media")
     * @IsGranted("ROLE_USER")
     */
    public function editMedia(int $idMedia, Request $request, EntityManagerInterface $entityManager)
    {
        $media = $this->entityManager->getRepository(Media::class)->find($idMedia);

        dump($media);
        $form = $this->createForm(MediaFormType::class, $media);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('admin/edit_media.html.twig', [
            'media' => $media,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/trick/{idTrick}-{slugTrick}/media/add", name="add_media")
     * @IsGranted("ROLE_USER")
     */
    public function addMedia(int $idTrick, string $slugTrick, Request $request, EntityManagerInterface $entityManager)
    {
        $media = new Media();
        $trick = $this->entityManager->getRepository(Trick::class)->find($idTrick);

        $form = $this->createForm(MediaFormType::class, $media);

        $form->handleRequest($request);

         if ($form->isSubmitted() && $form->isValid()) {

         }

        return $this->render('admin/add_media.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
        ]);
    }
}
