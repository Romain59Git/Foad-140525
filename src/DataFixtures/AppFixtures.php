<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Les fixtures sont chargées dans l'ordre alphabétique
        // IngredientFixtures sera chargé avant RecetteFixtures
    }
}
