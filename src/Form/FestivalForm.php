<?php

namespace App\Form;

use App\Entity\Artist;
use App\Entity\Festival;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class FestivalForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('name', null, [
                'attr' => ['placeholder' => 'Enter festival name']
            ])
            ->add('location', null, [
                'attr' => ['placeholder' => 'Enter location']
            ])
            ->add('start_date', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d'),
                    'placeholder' => 'Select start date',
                ],
            ])
            ->add('end_date', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d'),
                    'placeholder' => 'Select end date',
                ],
            ])
            ->add('price', NumberType::class, [
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'step' => '50',
                    'placeholder' => 'Enter ticket price'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Negative price']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter festival description',
                    'rows' => 4
                ],
            ])

            ->add('website', UrlType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://example.com'
                ],
            ])

            ->add('image', FileType::class, [
                'label' => 'Festival image (JPG/PNG)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/*'
                ],
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Total Tickets',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'step' => 1, //50
                ],
            ]);

        if ($options['include_artist'] ?? false) {
            $builder->add('artist', EntityType::class, [
                'class' => Artist::class,
                'choices' => $options['linked_artists'],
                'choice_label' => 'name',
                'placeholder' => 'Select an artist',
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Festival::class,
            'csrf_protection' => false,
            'include_artist' => false,
            'linked_artists' => [],
        ]);
    }
}
