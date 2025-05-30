<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class IngredientFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 50; $i++) {
            $ingredient = new Ingredient();
            $ingredient->setName($faker->word());
            $ingredient->setPrice($faker->randomFloat(2, 0.5, 20));
            $ingredient->setCreatedAt(new \DateTimeImmutable());
            $ingredient->setUpdatedAt(new \DateTimeImmutable());
            $userIndex = $faker->numberBetween(0, 9);
            $ingredient->setUser($this->getReference('user_' . $userIndex, \App\Entity\User::class));
            $manager->persist($ingredient);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
} 