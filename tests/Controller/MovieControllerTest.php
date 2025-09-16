<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class MovieControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->movieRepository = $container->get(MovieRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
        $em = $container->get('doctrine.orm.entity_manager');

        $movie = $this->movieRepository->findOneBy(['title' => 'Test Movie']);
        if ($movie) {
            $em->remove($movie);
            $em->flush();
        }

        $user = $this->userRepository->findOneBy(['email' => 'test@test.com']);
        if ($user) {
            $em->remove($user);
            $em->flush();
        }
    }

    public function testAuthorizedUserCanAddMovie()
    {
        $client = $this->client;
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setName('Tester');
        $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $em->persist($user);
        $em->flush();

        $client->loginUser($user);

        $crawler = $client->request('GET', '/movie/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form([
            'create_movie[title]' => 'Test Movie',
            'create_movie[description]' => 'Description here'
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/movie/new');

        // follow and assert movie exists
        $client->followRedirect();
        $this->assertStringContainsString('Your movie was added!', $client->getResponse()->getContent());
    }
}
