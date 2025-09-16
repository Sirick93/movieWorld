<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Entity\User;
use App\Entity\Movie;
use App\Repository\UserRepository;
use App\Repository\MovieRepository;

class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->movieRepository = $container->get(MovieRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
        $em = $container->get('doctrine.orm.entity_manager');

        $movie = $this->movieRepository->findOneBy(['title' => 'Votable Movie']);
        if ($movie) {
            $em->remove($movie);
            $em->flush();
        }

        $user = $this->userRepository->createQueryBuilder('s')
            ->where("s.email = 'test@test.com'")
            ->orWhere("s.email = 'test1@test.com'")
            ->getQuery()
            ->getResult();
        if ($user) {
            foreach($user as $u) {
                $em->remove($u);
                $em->flush();
            }  
        }
    }

    public function testUserCanLoginAndSeeDashboard(): void
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
        
        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'test@test.com',
            '_password' => 'password'
        ]);
        $client->submit($form);

        $client->followRedirect();

        $this->assertSelectorTextContains('.user-links', 'Hello');
    }

    public function testUserCanVoteOtherMovie(): void
    {
        $client = $this->client;
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        // Create a test user
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setName('Voter');
        $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $em->persist($user);

        $author = new User();
        $author->setEmail('test1@test.com');
        $author->setName('Author');
        $author->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $em->persist($author);

        $em->flush();

        // Create a test movie
        $movie = new Movie();
        $movie->setTitle('Votable Movie');
        $movie->setDescription('A test movie');
        $movie->setUser($author);
        $em->persist($movie);

        $em->flush();

        $client->loginUser($user);

        $client->request(
            'POST',
            '/vote/new',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With' => 'XMLHttpRequest'],
            json_encode([
                'movie' => $movie->getId(),  // must match your controller!
                'value' => 1
            ])
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame('ok', $data['status']);
        $this->assertSame('created', $data['action']);
        $this->assertSame(1, $data['likes'] + $data['hates']); // sanity check
    }

    public function testUserCanNotVoteHisMovie(): void
    {
        $client = $this->client;
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        // Create a test user
        $author = new User();
        $author->setEmail('test@test.com');
        $author->setName('Author-Voter');
        $author->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $em->persist($author);

        $em->flush();

        // Create a test movie
        $movie = new Movie();
        $movie->setTitle('Votable Movie');
        $movie->setDescription('A test movie');
        $movie->setUser($author);
        $em->persist($movie);

        $em->flush();

        $client->loginUser($author);

        $client->request(
            'POST',
            '/vote/new',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-Requested-With' => 'XMLHttpRequest'],
            json_encode([
                'movie' => $movie->getId(),  // must match your controller!
                'value' => 1
            ])
        );

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $this->assertResponseStatusCodeSame(403);
    }
}