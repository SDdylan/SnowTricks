<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $randRole = ['["ROLE_ADMIN"]', '["ROLE_USER"]'];

        for($i = 1; $i <= 4; $i++)
        {
            $user = new User();
            $user->setEmail($faker->email())
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPseudo($faker->userName())
                ->setImageUrl($faker->imageUrl(150, 150, 'animals', true))
                ->setRole($randRole[$faker->numberBetween(0,1)])
                ->setPassword($faker->password());

                $manager->persist($user);
        }

        $manager->flush();
    }
}
