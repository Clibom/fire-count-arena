<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Exception\FireCountNotFoundException;
use App\Infrastructure\Form\FireCountType;
use App\UseCases\CreateFireCount\CreateFireCountCommand;
use App\UseCases\CreateFireCount\CreateFireCountHandler;
use App\UseCases\ViewFireCount\ViewFireCountQuery;
use App\UseCases\ViewFireCount\ViewFireCountHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for fire count public form and summary display.
 */
class FireCountController extends AbstractController
{
    public function __construct(
        private readonly CreateFireCountHandler $createFireCountHandler,
        private readonly ViewFireCountHandler $viewFireCountHandler,
    ) {
    }

    #[Route('/fire-count', name: 'fire_count_form', methods: ['GET', 'POST'])]
    public function form(Request $request): Response
    {
        $form = $this->createForm(FireCountType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $command = new CreateFireCountCommand(
                email: $data['email'],
                adultsCount: $data['adultsCount'],
                childrenCount: $data['childrenCount'],
                startTime: $data['startTime'],
                endTime: $data['endTime'],
            );

            $this->createFireCountHandler->handle($command);

            $this->addFlash('success', 'fire_count.form.success');

            return $this->redirectToRoute('fire_count_form');
        }

        return $this->render('fire_count/form.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/fire-count/{id}', name: 'fire_count_summary', methods: ['GET'])]
    public function summary(string $id): Response
    {
        try {
            $query = new ViewFireCountQuery($id);
            $fireCount = $this->viewFireCountHandler->handle($query);
        } catch (FireCountNotFoundException) {
            throw $this->createNotFoundException('fire_count.summary.not_found');
        }

        return $this->render('fire_count/summary.html.twig', [
            'fireCount' => $fireCount,
        ]);
    }
}
