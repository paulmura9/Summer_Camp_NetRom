<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use App\Entity\UserDetails;

class UserEditForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(['message' => 'Invalid email.']),
                ],
                'attr' => ['class' => 'form-control'],
                'label' => 'Email'
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'attr' => ['class' => 'form-select'],
                'label' => 'Role'
            ])
            ->add('name', TextType::class, [
                'mapped' => false,
                'constraints' => [new Assert\NotBlank()],
                'attr' => ['class' => 'form-control'],
                'label' => 'Name'
            ])
            ->add('age', IntegerType::class, [
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThanOrEqual([
                        'value' => 18,
                        'message' => 'Must be 18 or older.',
                    ])
                ],
                'attr' => ['min' => 18, 'class' => 'form-control'],
                'label' => 'Age'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
