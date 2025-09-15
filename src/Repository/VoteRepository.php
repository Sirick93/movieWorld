<?php

namespace App\Repository;

use App\Entity\Vote;
use App\Entity\Movie;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vote>
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function getUserMovieVote(Movie $movie, User $user): ?Vote
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.movie = :movie')
            ->andWhere('v.user = :user')
            ->setParameter('movie', $movie)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countLikesHatesByMovie(Movie $movie): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select(
                "COALESCE(SUM(CASE WHEN v.value = 1 THEN 1 ELSE 0 END), 0) AS likes",
                "COALESCE(SUM(CASE WHEN v.value = -1 THEN 1 ELSE 0 END), 0) AS hates"
            )
            ->andWhere('v.movie = :movie')
            ->setParameter('movie', $movie)
            ->getQuery()
            ->getSingleResult();

        return [
            'likes' => (int) $qb['likes'],
            'hates' => (int) $qb['hates'],
        ];
    }

}
