<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Entity\Participant;
use App\Form\ConferenceForm;
use App\Form\ParticipantType;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/conference')]
final class ConferenceController extends AbstractController
{
    #[Route('/', name: 'app_conference_index', methods: ['GET'])]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        return $this->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_conference_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $conference = new Conference();
        $form = $this->createForm(ConferenceForm::class, $conference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($conference);
            $entityManager->flush();

            $this->addFlash('success', 'La conférence a été créée avec succès.');
            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('conference/new.html.twig', [
            'conference' => $conference,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conference_show', methods: ['GET'])]
    public function show(Conference $conference, Request $request): Response
    {
        $participant = new Participant();
        $participant->setConference($conference);
        $form = $this->createForm(ParticipantType::class, $participant);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_conference_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Conference $conference, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConferenceForm::class, $conference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La conférence a été modifiée avec succès.');
            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('conference/edit.html.twig', [
            'conference' => $conference,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conference_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Conference $conference, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conference->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($conference);
            $entityManager->flush();
            $this->addFlash('success', 'La conférence a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
    }
}
