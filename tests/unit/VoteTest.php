<?php
namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Entity\Vote;
use App\Entity\User;
use App\Entity\Movie;

class VoteTest extends TestCase
{
    public function testVoteValueAllowed()
    {
        $user = $this->createMock(User::class);
        $movie = $this->createMock(Movie::class);

        $vote = new Vote($user, $movie, 1);
        $this->assertEquals(1, $vote->getValue());

        $vote->setValue(-1);
        $this->assertEquals(-1, $vote->getValue());
    }
}
