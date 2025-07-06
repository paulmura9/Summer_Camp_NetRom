<?php

namespace App\Form;

use App\Entity\Artist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ArtistForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['placeholder' => 'Enter artist name'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Artist name cannot be empty']),
                ],
            ])
            ->add('musical_genre', TextType::class, [
                'attr' => ['placeholder' => 'Enter musical genre'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Musical genre cannot be empty']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artist::class,
            'csrf_protection' => true,
        ]);
    }
}
