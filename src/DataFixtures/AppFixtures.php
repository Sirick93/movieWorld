<?php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Movie;
use App\Entity\Vote;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        // Create users
       $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail("user{$i}@example.com");
            $user->setName($faker->firstName());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'password')
            );
            $manager->persist($user);
            $users[] = $user;
        }

        $manager->flush();

        // Create movies
        $movies = [];
        for ($i = 0; $i < 200; $i++) {
            $movie = new Movie();
            $movie->setTitle($faker->sentence(3));
            $movie->setDescription($faker->paragraph());
            $movie->setUser($faker->randomElement($users));
            $manager->persist($movie);
            $movies[] = $movie;
        }

        $manager->flush();

        // Create votes
        foreach ($movies as $movie) {
            foreach ($faker->randomElements($users, rand(3, 8)) as $user) {
                if ($user === $movie->getUser()) {
                    continue;
                }
                $value = $faker->randomElement([1, -1]);
                $vote = new Vote($user, $movie, $value);
                $manager->persist($vote);
            }
        }

        $manager->flush();
    }
}