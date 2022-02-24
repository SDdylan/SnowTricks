<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Media;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $randRole = ['["ROLE_ADMIN"]', '["ROLE_USER"]'];
        $typeMedia = ['image','video'];



        for($i = 1; $i <= 4; $i++)
        {
            //Creation des utilisateurs
            $user = new User();
            $password = $this->hasher->hashPassword($user, $faker->password());

            $user->setEmail($faker->email())
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPseudo($faker->userName())
                ->setImageUrl($faker->imageUrl(150, 150, 'animals', true))
                ->setRole($randRole[$faker->numberBetween(0,1)])
                ->setPassword($password);

            $manager->persist($user);

            //creation des groupes
            $group = new Group();

            $group->setDescription($faker->sentence(10))
                  ->setTitle($faker->sentence(4));

            $manager->persist($group);

            //creation de trick
            for($j=1; $j<=3; $j++)
            {
                $trick = new Trick();

                $trick->setTitle($faker->sentence(4))
                      ->setDescription($faker->paragraph(4))
                      ->setCreatedAt($faker->dateTime())
                      ->setGrp($group)
                      ->setUsers($user);
                      //->setModifiedAt();

                $manager->persist($trick);

                //creation des commentaires et des media
                for($k=1; $k<=2; $k++) {
                    $comment = new Comment();

                    $comment->setCreatedAt($faker->dateTime())
                        ->setContent($faker->paragraph(3))
                        ->setUser($user)
                        ->setTrick($trick);

                    $manager->persist($comment);

                    //media
                    $media = new Media();
                    $type = $k - 1;

                    $media->setType($typeMedia[$type])
                          ->setTrick($trick);

                    //url en fonction du type de media
                    if ($media->getType() === 'video') {
                        $media->setUrl($faker->imageUrl(250, 250));
                    } elseif ($media->getType() === 'image') {
                        $media->setUrl('https://youtu.be/HgzGwKwLmgM');
                    }

                    $manager->persist($media);
                }
            }
        }

        $manager->flush();
    }
}
