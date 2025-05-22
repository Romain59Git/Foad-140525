<?php

namespace App\Form;

use App\Entity\Recette;
use App\Entity\Ingredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class RecetteTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('time', null, [
                'label' => 'Time (minutes)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('nbPeople', null, [
                'label' => 'Number of people',
                'attr' => ['class' => 'form-control']
            ])
            ->add('difficulty', null, [
                'label' => 'Difficulty',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', null, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control']
            ])
            ->add('price', null, [
                'label' => 'Price',
                'attr' => ['class' => 'form-control']
            ])
            ->add('isFavorite', null, [
                'label' => 'Favorite',
                'required' => false
            ])
            ->add('ingredients', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Ingredients',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer la recette',
                'attr' => ['class' => 'btn btn-primary mt-4']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}