<?php

namespace App\DataFixtures;

use App\Entity\Recette;
use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RecetteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupérer tous les ingrédients en base
        $ingredients = $manager->getRepository(Ingredient::class)->findAll();

        if (empty($ingredients)) {
            throw new \Exception('Aucun ingrédient trouvé. Veuillez d\'abord charger les fixtures d\'ingrédients.');
        }

        for ($i = 0; $i < 25; $i++) {
            $recette = new Recette();
            $recette->setName($faker->words(3, true));
            $recette->setTime($faker->numberBetween(10, 180));
            $recette->setNbPeople($faker->numberBetween(1, 10));
            $recette->setDifficulty($faker->numberBetween(1, 5));
            $recette->setDescription($faker->paragraph(3));
            $recette->setPrice($faker->randomFloat(2, 5, 100));
            $recette->setIsFavorite($faker->boolean(20)); // 20% de chance d'être favori
            $recette->setCreatedAt(new \DateTimeImmutable());
            $recette->setUpdatedAt(new \DateTimeImmutable());

            // Ajout d'ingrédients aléatoires (entre 3 et 8 ingrédients)
            $nbIngredients = $faker->numberBetween(3, min(8, count($ingredients)));
            $randomIngredients = $faker->randomElements($ingredients, $nbIngredients);
            
            foreach ($randomIngredients as $ingredient) {
                $recette->addIngredient($ingredient);
            }

            $manager->persist($recette);
        }

        $manager->flush();
    }
}