<?php
namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MovieControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testUserCanCreateMovie(): void
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'user12@example.com',
            'password' => 'password'
        ]);
        $client->submit($form);

        $client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Welcome');
    }
}