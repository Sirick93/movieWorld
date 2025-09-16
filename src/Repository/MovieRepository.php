<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    /**
     * Returns a Query for all movies, optionally filtered by user and sorted
     */
    public function getMoviesWithVotesQuery(
        string $sortField = 'createdAt', 
        string $sortOrder = 'DESC',  
        ?int $userId = null,
        ?int $currentUser = null
    )
    {   
        $qb = $this->createQueryBuilder('m')
            //->select('m', 'u')
            ->leftJoin('m.user', 'u')
            ->leftJoin('m.votes', 'v')
            ->addSelect('SUM(CASE WHEN v.value = 1 THEN 1 ELSE 0 END) AS likes')
            ->addSelect('SUM(CASE WHEN v.value = -1 THEN 1 ELSE 0 END) AS hates')
            ->groupBy('m.id');

        if ($userId) {
            $qb->andWhere('m.user = :user')
            ->setParameter('user', $userId);
        }
        if ($currentUser) {
            // join with votes of the current user
            $qb->leftJoin(
                'm.votes',
                'uv',
                'WITH',
                'uv.user = :currentUser'
            )
            ->addSelect('uv.value AS userVote')
            ->setParameter('currentUser', $currentUser);
        }

        // Validate sorting field
        $allowedFields = ['createdAt', 'likes', 'hates'];
        if (!in_array($sortField, $allowedFields)) {
            $sortField = 'createdAt';
        }

        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        if ($sortField === 'likes' || $sortField === 'hates') {
            $qb->orderBy($sortField, $sortOrder);
        } else {
            $qb->orderBy('m.' . $sortField, $sortOrder);
        }

        return $qb;
    }
}
