<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\MovieRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;

final class HomeController extends AbstractController
{
    #[Route('/', name: '')]
    public function index(Request $request, PaginatorInterface $paginator, MovieRepository $movieRepository, AuthenticationUtils $authenticationUtils): Response
    {
        $sortField = $request->query->get('sort', 'createdAt'); 
        $sortOrder = $request->query->get('order', 'DESC');
        $userId = $request->query->get('userId');

        $query = $movieRepository->getMoviesWithVotesQuery($sortField, $sortOrder, $userId);

        $movies = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = new User();
        $registrationForm = $this->createForm(RegistrationFormType::class,
            $user,
            [
                'action' => $this->generateUrl('app_register'),
                'method' => 'POST',
        ]);
        
        return $this->render('home/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'movies' => $movies,
            'registrationForm' => $registrationForm
        ]);
    }
}
