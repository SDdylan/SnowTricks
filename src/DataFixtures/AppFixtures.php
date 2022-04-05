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

class AppFixtures extends Fixture
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
        $rotations = new Group();
        $rotations->setDescription("Le principe est d'effectuer une rotation horizontale pendant le saut, puis d'attérir en position switch ou normal.")
            ->setTitle('Rotations');
        $manager->persist($rotations);

        $flips = new Group();
        $flips->setDescription('Un flip est une rotation verticale. On distingue les front flips, rotations en avant, et les back flips, rotations en arrière.')
            ->setTitle('Flips');
        $manager->persist($flips);

        $grabs = new Group();
        $grabs->setDescription('Un grab consiste à attraper la planche avec la main pendant le saut.')
            ->setTitle('Grabs');
        $manager->persist($grabs);

        $slides = new Group();
        $slides->setDescription("Un slide consiste à glisser sur une barre de slide. Le slide se fait soit avec la planche dans l'axe de la barre, soit perpendiculaire, soit plus ou moins désaxé.")
            ->setTitle('Slides');
        $manager->persist($slides);

        //création des données pour les tricks et les médias
        $tricksArrayData = [
            [
                '1080',
                'Rotation horizontale de trois tours complets',
                $rotations,
                'https://www.youtube.com/embed/_3C02T-4Uug',
                'https://media.wired.com/photos/5a6a74eb3766960ab49fc4cd/master/w_2560%2Cc_limit/0218-WI-APCORK-web.jpg'
            ],
            [
                '180',
                'Rotation horizontale d\'un demi tour',
                $rotations,
                'https://www.youtube.com/embed/ATMiAVTLsuc',
                'https://coresites-cdn-adm.imgix.net/whitelines_new/wp-content/uploads/2013/09/Cab_180_Piste_Cudlip_REP-620x413.jpg'
            ],
            [
                '360',
                'Rotation horizontale d\'un tours complet',
                $rotations,
                'https://www.youtube.com/embed/_rS2i4-yb6E',
                'https://www.imperiumsnow.com/upload/friedl-fs-360-0.jpg'
            ],
            [
                'Back Flip',
                'Rotation verticale en arrière',
                $flips,
                'https://www.youtube.com/embed/SlhGVnFPTDE',
                'https://cdn.shopify.com/s/files/1/1442/9482/articles/KBS_niseko-cayley-alger_160307_210-Edit_1024x1024.jpg?v=1473050197'
            ],
            [
                'Front flip',
                'Rotation verticale en avant',
                $flips,
                'https://www.youtube.com/embed/gMfmjr-kuOg',
                'https://coresites-cdn-adm.imgix.net/whitelines_new/wp-content/uploads/2012/12/frontflipknuckle.jpg?fit=crop'
            ],
            [
                'Indy',
                'Saisie de la carre frontside de la planche, entre les deux pieds, avec la main arrière',
                $grabs,
                'https://www.youtube.com/embed/iKkhKekZNQ8',
                'https://images.indianexpress.com/2018/02/snowboarding-759.jpg'
            ],
            [
                'Mute',
                'Saisie de la carre frontside de la planche entre les deux pieds avec la main avant',
                $grabs,
                'https://www.youtube.com/embed/8r_yZfBWCeQ',
                'https://images.freeimages.com/images/large-previews/617/mute-air-on-snowboard-1438217.jpg'
            ],
            [
                'Sad',
                'Saisie de la carre backside de la planche, entre les deux pieds, avec la main avant',
                $grabs,
                'https://www.youtube.com/embed/KEdFwJ4SWq4',
                'https://i0.heartyhosting.com/www.snowboarder.com/wp-content/uploads/2015/06/sad_johnny_brady_side_fenelon.jpg?fit=1024%2C1024&ssl=1&resize=1024%2C1024'
            ],
            [
                'Nose Slide',
                'Glissade avec planche perpendiculaire à la barre de slide avec la barre du coté avant de la planche',
                $slides,
                'https://www.youtube.com/embed/7AB0FZWyrGQ',
                'https://miro.medium.com/max/1200/1*j5r2noouXBiy3l-wkMZIAQ.jpeg'
            ],
            [
                'Tail Slide',
                'Glissade avec planche perpendiculaire à la barre de slide avec la barre du coté arrière de la planche',
                $slides,
                'https://www.youtube.com/embed/HRNXjMBakwM',
                'https://cdn.shopify.com/s/files/1/0230/2239/files/4_a07f3e1e-b8ba-4fa9-8e2d-6ea458d66fb2_large.jpg?v=1531937219'
            ]
        ];

        //attribution random de user dans les tricks
        $randomUser = rand(1, 2);

        $nbTricks = count($tricksArrayData);
        //Creation de tricks
        for($i=0; $i<$nbTricks; $i++) {
            $trickData = $tricksArrayData[$i];
            $user = $randomUser === 1 ? $simpleUser : $adminUser;

            $trick = new Trick();

            $trick->setTitle($trickData[0])
                ->setDescription($trickData[1])
                ->setCreatedAt(new \DateTime())
                ->setGroup($trickData[2])
                ->setUser($user)
                ->setSlug($slugify->slugify($trickData[0]));

            $manager->persist($trick);

            //creation d'une image et d'une video pour le trick
            //Video
            $mediaVideo = new Media();
            $mediaVideo->setTrick($trick)
                ->setUrl($trickData[3])
                ->setType(Media::getVideoType());
            $manager->persist($mediaVideo);

            //Image
            $mediaImage = new Media();
            $mediaImage->setTrick($trick)
                ->setUrl($trickData[4])
                ->setType(Media::getImageType());
            $manager->persist($mediaImage);
        }
        $manager->flush();

    }
}
