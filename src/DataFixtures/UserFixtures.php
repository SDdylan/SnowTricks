<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Media;
use App\Entity\Trick;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function buildUser(string $email, string $password, array $role): User
    {
        $faker = Factory::create('fr_FR');
        //Creation des utilisateurs
        $user = new User();

        $user->setEmail($email);
        $user->setFirstName($faker->firstName());
        $user->setLastName($faker->lastName());
        $user->setUsername($faker->userName());
        $user->setImageUrl($faker->imageUrl(150, 150, 'animals', true));
        $user->setRoles($role);
        $user->encodePassword($password);
        $user->setIsVerified(1);

        return $user;
    }


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $slugify = new Slugify();

        //creation users
        $simpleUser = $this->buildUser('dylan.sardi@hotmail.fr', '123456789@u', [User::getSimpleUser()]);
        $manager->persist($simpleUser);
        $adminUser = $this->buildUser('dylagia@gmail.com', '123456789@a', [User::getAdminUser(),User::getSimpleUser()]);
        $manager->persist($adminUser);

        //creation des groupes
        $group1 = new Group();
        $group1->setDescription($faker->sentence(2))
              ->setTitle($faker->word());
        $manager->persist($group1);

        $group2 = new Group();
        $group2->setDescription($faker->sentence(2))
            ->setTitle($faker->word());
        $manager->persist($group2);

        //attribution random de group ou user dans les tricks
        $randomGroup = rand(1, 2);
        $randomUser = rand(1, 2);

        //Creation de tricks
        for($j=1; $j<=20; $j++) {
            $trick = new Trick();

            $group = ${"group".$randomGroup};
            $user = $randomUser === 1 ? $simpleUser : $adminUser;

            $title = $faker->sentence(4);

            $trick->setTitle($title)
                ->setDescription($faker->paragraph(4))
                ->setCreatedAt($faker->dateTime())
                ->setGroup($group)
                ->setUser($user)
                ->setSlug($slugify->slugify($title));

            $manager->persist($trick);

            //creation d'une image et d'une video pour le trick
            //Video
            $mediaVideo = new Media();
            $mediaVideo->setTrick($trick)
                ->setUrl('https://youtu.be/HgzGwKwLmgM')
                ->setType(Media::getVideoType());
            $manager->persist($mediaVideo);

            //Image
            $mediaImage = new Media();
            $mediaImage->setTrick($trick)
                ->setUrl($faker->imageUrl(250, 250))
                ->setType(Media::getImageType());
            $manager->persist($mediaImage);
        }
        $manager->flush();

    }
}
