<?php

namespace App\Form;

use App\Entity\Artist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;

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
            ])
            ->add('image', FileType::class, [
            'label' => 'Artist Image',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => ['image/*'],
                    'mimeTypesMessage' => 'Please upload a valid image file',
                ]),
            ],
        ])
            ->add('biography', TextareaType::class, [
            'attr' => ['placeholder' => 'Write a short biography...', 'rows' => 5],
            'required' => false,
        ])
            ->add('spotifyProfile', TextType::class, [
                'attr' => ['placeholder' => 'Spotify artist profile'],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artist::class,
            'csrf_protection' => false,
        ]);
    }
}
