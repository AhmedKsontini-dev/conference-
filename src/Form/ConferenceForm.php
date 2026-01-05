<?php

namespace App\Form;

use App\Entity\Conference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ConferenceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un titre',
                    ]),
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un lieu',
                    ]),
                ],
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une date',
                    ]),
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'La date doit être dans le futur',
                    ]),
                ],
            ])
            ->add('maxCapacity', IntegerType::class, [
                'label' => 'Capacité maximale',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une capacité maximale',
                    ]),
                    new Positive([
                        'message' => 'La capacité doit être un nombre positif',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conference::class,
        ]);
    }
}
