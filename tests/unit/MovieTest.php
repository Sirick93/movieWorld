<?php
namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Entity\Movie;
use App\Entity\User;

class MovieTest extends TestCase
{
    public function testMovieConstructsWithCreatedAt()
    {
        $user = $this->createMock(User::class);
        $movie = new Movie();
        $movie->setTitle('Test Movie');
        $movie->setDescription('...');

        // set user
        $movie->setUser($user);

        $this->assertInstanceOf(\DateTimeImmutable::class, $movie->getCreatedAt());
        $this->assertEquals('Test Movie', $movie->getTitle());
    }
}
