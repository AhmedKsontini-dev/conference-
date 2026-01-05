<?php

namespace App\Form;

use App\Entity\Conference;
use App\Repository\ParticipantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ConferenceType extends AbstractType
{
    private $participantRepository;

    public function __construct(ParticipantRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le titre est obligatoire',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'La description est obligatoire',
                    ]),
                ],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date est obligatoire',
                    ]),
                    new GreaterThan([
                        'value' => 'today',
                        'message' => 'La date doit être dans le futur',
                    ]),
                ],
            ])
            ->add('capacite', IntegerType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'La capacité est obligatoire',
                    ]),
                    new Type([
                        'type' => 'integer',
                        'message' => 'La capacité doit être un nombre entier',
                    ]),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'La capacité doit être supérieure à 0',
                    ]),
                ],
            ])
        ;

        // Ajouter un écouteur d'événement pour la validation personnalisée
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $conference = $event->getData();
            $form = $event->getForm();

            if ($conference->getId()) {
                // Pour une modification de conférence existante
                $nombreParticipants = $this->participantRepository->countByConference($conference);
                
                if ($conference->getCapacite() < $nombreParticipants) {
                    $form->get('capacite')->addError(new FormError(
                        "Impossible de réduire la capacité en dessous du nombre de participants actuels ($nombreParticipants)"
                    ));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conference::class,
        ]);
    }
} 