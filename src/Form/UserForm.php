<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'Enter email'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(['message' => 'Invalid email']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'attr' => ['placeholder' => 'Enter password'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 6]),
                ],
            ])
            ->add('role', TextType::class, [
                'attr' => ['placeholder' => 'Enter user role'],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('token', TextType::class, [
                'attr' => ['placeholder' => 'Enter token'],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
        ]);
    }
}
