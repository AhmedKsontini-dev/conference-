<?php

namespace App\Form;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantType extends AbstractType
{
    private $participantRepository;

    public function __construct(ParticipantRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom complet',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre nom',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre email',
                    ]),
                    new Email([
                        'message' => 'Veuillez entrer une adresse email valide',
                    ]),
                ],
            ])
        ;

        // Ajouter un écouteur d'événement pour la validation de l'email unique
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $participant = $event->getData();
            $form = $event->getForm();

            // Vérifier si l'email existe déjà pour cette conférence
            $existingParticipant = $this->participantRepository->findOneBy([
                'email' => $participant->getEmail(),
                'conference' => $participant->getConference()
            ]);

            if ($existingParticipant && (!$participant->getId() || $existingParticipant->getId() !== $participant->getId())) {
                $form->get('email')->addError(new FormError(
                    'Cette adresse email est déjà utilisée pour cette conférence'
                ));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
} 