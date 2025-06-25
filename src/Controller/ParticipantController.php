<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Conference;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[Route('/participant')]
final class ParticipantController extends AbstractController
{
    #[Route('/conference/{id}/inscription', name: 'app_participant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Conference $conference, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si la conférence a atteint sa capacité maximale
        if (count($conference->getParticipants()) >= $conference->getMaxCapacity()) {
            $this->addFlash('error', 'Désolé, cette conférence a atteint sa capacité maximale.');
            return $this->redirectToRoute('app_conference_show', ['id' => $conference->getId()]);
        }

        $participant = new Participant();
        $participant->setConference($conference);
        
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Votre inscription a été enregistrée avec succès !');
            return $this->redirectToRoute('app_conference_show', ['id' => $conference->getId()]);
        }

        return $this->render('participant/new.html.twig', [
            'participant' => $participant,
            'conference' => $conference,
            'form' => $form,
        ]);
    }

    #[Route('/conference/{id}/participants', name: 'app_participant_list', methods: ['GET'])]
    public function list(Conference $conference): Response
    {
        // Vérifier si l'utilisateur est connecté (admin)
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('participant/list.html.twig', [
            'conference' => $conference,
            'participants' => $conference->getParticipants(),
        ]);
    }

    #[Route('/{id}', name: 'app_participant_delete', methods: ['POST'])]
    public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est connecté (admin)
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->getPayload()->getString('_token'))) {
            $conferenceId = $participant->getConference()->getId();
            $entityManager->remove($participant);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le participant a été supprimé avec succès.');
            return $this->redirectToRoute('app_participant_list', ['id' => $conferenceId]);
        }

        throw new AccessDeniedHttpException('Token CSRF invalide');
    }
} 