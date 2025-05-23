<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Users
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email())
                ->setPseudo(mt_rand(0, 1) === 1 ? $faker->firstName() : null)
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword('password');

            $manager->persist($user);
            $this->addReference('user_' . $i, $user);
        }

        // Utilisateur admin
        $admin = new User();
        $admin->setEmail('admin@admin.com')
            ->setPseudo('admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setPlainPassword('admin');
        $manager->persist($admin);
        $this->addReference('user_admin', $admin);

        $manager->flush();
    }
} 