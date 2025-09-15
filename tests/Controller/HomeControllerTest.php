<?php
namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    use FixturesTrait;
    public function testHomepageShowsMovies(): void
    {
        $client = static::createClient();
        $this->loadFixtures([AppFixtures::class]);

        $crawler = $client->request('GET', '/');
        dd($crawler);
        //$this->assertResponseIsSuccessful();
        //$this->assertEquals(200, $movies->getTotalItemCount); 
    }

    /*public function testUserCanLoginAndSeeDashboard(): void
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
    }*/
}