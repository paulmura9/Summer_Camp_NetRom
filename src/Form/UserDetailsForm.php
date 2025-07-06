<?php
namespace App\Form;

use App\Entity\UserDetails;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints as Assert;

class UserDetailsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('age', IntegerType::class, [
                'attr' => [
                    'min' => 18,
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please enter your age.',
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 18,
                        'message' => 'You must be at least 18 years old to register.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserDetails::class,
        ]);
    }
}

