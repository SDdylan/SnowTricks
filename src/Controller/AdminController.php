<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Media;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\GroupFormType;
use App\Form\MediaFormType;
use App\Form\RegistrationFormType;
use App\Form\TrickFormType;
use App\Repository\CommentRepository;
use App\Repository\GroupRepository;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\DBAL\Exception;
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
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas l'autorisation d'accèder à cette page")
     * @Route("/admin", name="app_admin")
     */
    public function index(Request $request, TrickRepository $trickRepository): Response
    {
        $nbTricks = $trickRepository->countAllTricks();
        $nbPages = $trickRepository->getNbPagesTricks($nbTricks);
        $page = $request->query->get('page') ?? 1;
        $tricks = $trickRepository->getTricksPages($page, $nbPages);

        return $this->render('admin/index.html.twig', [
            'tricks' => $tricks,
            'nbPages' => $nbPages,
            'currentPage' => $page
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas l'autorisation d'accèder à cette page")
     * @Route("/admin/users", name="admin_users")
     */
    public function usersAdmin(Request $request, UserRepository $userRepository): Response
    {
        $nbUsers = $userRepository->countAllUsers();
        $nbPages = $userRepository->getNbPagesUsers($nbUsers);
        $page = $request->query->get('page') ?? 1;
        $users = $userRepository->getUsersPages($page, $nbPages);

        return $this->render('admin/admin_users.html.twig', [
            'users' => $users,
            'nbPages' => $nbPages,
            'currentPage' => $page
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas l'autorisation d'accèder à cette page")
     * @Route("/admin/users/{idUser}/comments", name="admin_user_comments")
     */
    public function userComments(int $idUser, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($idUser);
        $nbComments = $commentRepository->countCommentsByUser($user);
        $nbPages = $commentRepository->getNbPagesComments($nbComments);
        $page = $request->query->get('page') ?? 1;
        $comments = $commentRepository->getCommentsByUserPages($page, $nbPages, $idUser);

        return $this->render('admin/admin_user_comments.html.twig', [
            'user' => $user,
            'nbPages' => $nbPages,
            'currentPage' => $page,
            'comments' => $comments
       ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas l'autorisation d'accèder à cette page")
     * @Route("/admin/trick/{idTrick}/comments", name="admin_trick_comments")
     */
    public function tricksComments(int $idTrick, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        $trick = $this->entityManager->getRepository(Trick::class)->find($idTrick);
        $nbComments = $commentRepository->countCommentsByTrick($trick);
        $nbPages = $commentRepository->getNbPagesComments($nbComments);
        $page = $request->query->get('page') ?? 1;
        $comments = $commentRepository->getCommentsByTrickPages($page, $nbPages, $idTrick);

        return $this->render('admin/admin_trick_comments.html.twig', [
            'trick' => $trick,
            'nbPages' => $nbPages,
            'currentPage' => $page,
            'comments' => $comments
        ]);
    }

    /**
     *  @IsGranted("ROLE_ADMIN", message="Vous n'avez pas l'autorisation d'accèder à cette page")
     * @Route("/admin/comment/{idComment}/change", name="admin_comments_change")
     */
    public function changeCommentStatus(Request $request, int $idComment, EntityManagerInterface $entityManager)
    {
        $page = '';
        if ($request->get('page') !== null) {
            $page = '?page=' . $request->get('page');
        }
        $comment = $this->entityManager->getRepository(Comment::class)->find($idComment);

        if ($comment->getIsVerified() === true) {
            $comment->setIsVerified(false);
        } else {
            $comment->setIsVerified(true);
        }

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'La vérification du commentaire à été modifié avec succès !'
        );

        if ($request->get('trick') !== null) {
            $url = '/admin/trick/' . $request->get('trick') . '/comments' . $page;
        } elseif ($request->get('user') !== null) {
            $url = '/admin/users/' . $request->get('user') . '/comments' . $page;
        }

        return $this->redirect($url);
    }

    /**
     *  @IsGranted("ROLE_ADMIN", message="Vous n'avez pas l'autorisation d'accèder à cette page")
     * @Route("/admin/users/{idUser}/change", name="admin_user_change")
     */
    public function changeUserStatus(int $idUser, Request $request, EntityManagerInterface $entityManager)
    {
        $page = '';
        if ($request->get('page') !== null) {
            $page = '?page=' . $request->get('page');
        }
        $user = $this->entityManager->getRepository(User::class)->find($idUser);

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $user->setRoles([$user->getSimpleUser()]);
        } else {
            $user->setRoles([$user->getSimpleUser(),$user->getAdminUser()]);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash(
            'success',
            "Le rôle de l'utilisateur " . $user->getUsername() . " à été modifié avec succès !"
        );

        return $this->redirect('/admin/users' . $page);
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
     * @Route("/trick/add", name="add_trick")
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
                $date = new DateTime();
                $trick->setCreatedAt($date);
                $trick->setModifiedAt($date);
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
        $media = $this->entityManager->getRepository(Media::class)->find($idMedia);
        $trick = $media->getTrick();
        $typeMedia = $media->getType();
        //On s'assure qu'il y ait bien au moins une image et une vidéo
        $mediasSameType = 0;
        foreach ($trick->getMedia() as $media) {
            if ($media->getType() == $typeMedia) {
                $mediasSameType++;
            }
            if($mediasSameType > 1 ) {
                break;
            }
        }

        if ($mediasSameType==1) {
            $this->addFlash(
                'warning',
                "Erreur : Vous ne pouvez pas supprimer ce media car il s'agit de la seule " . $media->getType()
            );
        } else {
            $trick->removeMedium($media);
            $entityManager->remove($media);

            $entityManager->flush();

            $this->addFlash(
                'success',
                'La média à été supprimé avec succès !'
            );
        }
        return $this->redirect('/trick/' . $trick->getId() . '-' . $trick->getSlug() . '/edit');
    }

    /**
     * @Route("/media/edit/{idMedia}", name="edit_media")
     * @IsGranted("ROLE_USER")
     */
    public function editMedia(int $idMedia, Request $request, EntityManagerInterface $entityManager)
    {
        $media = $this->entityManager->getRepository(Media::class)->find($idMedia);

        $form = $this->createForm(MediaFormType::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $media = $form->getData();
            $entityManager->persist($media);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La média à été modifié avec succès !'
            );

            return $this->redirect('/trick/' . $media->getTrick()->getId() . '-' . $media->getTrick()->getSlug() . '/edit');
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
             $media = $form->getData();
             $media->setTrick($trick);

             $entityManager->persist($media);
             $entityManager->flush();
             $this->addFlash(
                 'success',
                 'La média à été ajouté avec succès !'
             );

             return $this->redirect('/trick/' . $media->getTrick()->getId() . '-' . $media->getTrick()->getSlug() . '/edit');
         }

        return $this->render('admin/add_media.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
        ]);
    }

    /**
     * @Route("/group/add", name="add_group")
     * @IsGranted("ROLE_USER")
     */
    public function addGroup(Request $request, EntityManagerInterface $entityManager)
    {
        $group = new Group();

        if ($request->query->get('url') !== null) {
            $url = "/trick/" . $request->query->get('url') . "/edit";
        } else {
            $url = '/trick/add';
        }

        $form = $this->createForm(GroupFormType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $group = $form->getData();

            $entityManager->persist($group);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La groupe à été ajouté avec succès !'
            );
            return $this->redirect($url);
        }

        return $this->render('admin/add_group.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
        ]);
    }

    /**
     * @Route("/admin/groups/", name="admin_group")
     * @IsGranted("ROLE_ADMIN")
     */
    public function displayGroup(Request $request, GroupRepository $groupRepository): Response
    {
        $nbTricks = $groupRepository->countAllGroups();
        $nbPages = $groupRepository->getNbPagesGroups($nbTricks);
        $page = $request->query->get('page') ?? 1;
        $groups = $groupRepository->getGroupsPages($page, $nbPages);

        return $this->render('admin/group.html.twig', [
            'groups' => $groups,
            'nbPages' => $nbPages,
            'currentPage' => $page
        ]);
    }

    /**
     * @Route("/admin/group/{idGroup}/edit", name="edit_group")
     * @IsGranted("ROLE_ADMIN")
     */
    public function editGroup(int $idGroup,Request $request, EntityManagerInterface $entityManager)
    {
        $group = $this->entityManager->getRepository(Group::class)->find($idGroup);

        $form = $this->createForm(GroupFormType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $group = $form->getData();

            $entityManager->persist($group);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'La groupe "' . $group->getTitle() . '" à été modifié avec succès !'
            );
            return $this->redirectToRoute('admin_group');
        }

        return $this->render('admin/edit_group.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
        ]);
    }

    /**
     * @Route("/admin/group/{idGroup}/delete", name="delete_group")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteGroup(int $idGroup, Request $request, EntityManagerInterface $entityManager)
    {
        $group = $this->entityManager->getRepository(Group::class)->find($idGroup);

        if ($group->getTricks()->isEmpty()) {
            $entityManager->remove($group);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Le groupe ' . $group->getTitle() . ' à été supprimé avec succès !'
            );
        } else {

            $this->addFlash(
                'danger',
                'Le groupe "' . $group->getTitle() . '" est associé à des figures et ne peut pas être supprimé.'
            );
        }
        return $this->redirectToRoute('admin_group');
    }


}
