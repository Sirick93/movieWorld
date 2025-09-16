<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\CreateMovieType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Movie;

final class MovieController extends AbstractController
{
    /*#[Route('/movie', name: 'app_movie')]
    public function index(): Response
    {
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
        ]);
    }*/

    #[Route('/movie/new', name: 'add_movie')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        
        $movie = new Movie;

        $form = $this->createForm(CreateMovieType::class, $movie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $movie->setUser($this->getUser());
            
            $manager->persist($movie);

            $manager->flush();

            $this->addFlash(
                'notice',
                'Your movie was added!'
            );

            return $this->redirect($request->getUri());
        }

        return $this->render('movie/create.html.twig', [
            'form' => $form,
        ]);
    }
}
