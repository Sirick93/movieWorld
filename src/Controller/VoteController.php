<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Vote;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\VoteRepository;
use App\Repository\MovieRepository;

final class VoteController extends AbstractController
{
    #[Route('/vote', name: 'app_vote')]
    public function index(): Response
    {
        return $this->render('vote/index.html.twig', [
            'controller_name' => 'VoteController',
        ]);
    }

    #[Route('/vote/new', name: 'add_vote', methods:["POST"])]
    public function create(
        Request $request,
        MovieRepository $movieRepository,
        VoteRepository $voteRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($request->getContentTypeFormat() == 'json') {

            $user = $this->getUser();
            if (!$this->isGranted('IS_AUTHENTICATED_FULLY') || !$user) {
                return new JsonResponse(['error' => 'Unauthorized'], 401);
            }

            $data = $request->toArray();
            $movieId = $data['movie'] ?? null;
            $value = isset($data['value']) ? (int)$data['value'] : null;

            if (!in_array($value, [1, -1], true)) {
                return new JsonResponse(['error' => 'Invalid vote value'], 400);
            }

            $movie = $movieRepository->find($movieId);
            if (!$movie) {
                return new JsonResponse(['error' => 'Movie not found'], 404);
            }

            // Prevent voting your own movie
            if ($movie->getUser()->getId() === $user->getId()) {
                return new JsonResponse(['error' => 'Cannot vote your own movie'], 403);
            }
            
            // use your repository method (expects objects)
            $existingVote = $voteRepository->getUserMovieVote($movie, $user);

            if (!empty($existingVote)) {
                if ($existingVote->getValue() === $value) {
                    // retract vote
                    $em->remove($existingVote);
                    $em->flush();
                    $action = 'retracted';
                } else {
                    // change vote
                    $existingVote->setValue($value);
                    $em->persist($existingVote);
                    $em->flush();
                    $action = 'changed';
                }
            } else {
                // create new vote
                $vote = new Vote($user, $movie, $value);
                $em->persist($vote);
                $em->flush();
                $action = 'created';
            }

            // return fresh counts
            $counts = $voteRepository->countLikesHatesByMovie($movie);

            return new JsonResponse([
                'status' => 'ok',
                'action' => $action,
                'likes' => $counts['likes'],
                'hates' => $counts['hates'],
            ]);
        }

        $response = new Response('Page not found.', Response::HTTP_NOT_FOUND);

        $response->send();
    }
}
