<?php

namespace App\Tests\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BookControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $bookRepository;
    private string $path = '/book/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->bookRepository = $this->manager->getRepository(Book::class);

        foreach ($this->bookRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Book index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'book[title]' => 'Testing',
            'book[description]' => 'Testing',
            'book[publication_year]' => 'Testing',
            'book[cover_image]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->bookRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Book();
        $fixture->setTitle('My Title');
        $fixture->setDescription('My Title');
        $fixture->setPublication_year('My Title');
        $fixture->setCover_image('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Book');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Book();
        $fixture->setTitle('Value');
        $fixture->setDescription('Value');
        $fixture->setPublication_year('Value');
        $fixture->setCover_image('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'book[title]' => 'Something New',
            'book[description]' => 'Something New',
            'book[publication_year]' => 'Something New',
            'book[cover_image]' => 'Something New',
        ]);

        self::assertResponseRedirects('/book/');

        $fixture = $this->bookRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getPublication_year());
        self::assertSame('Something New', $fixture[0]->getCover_image());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Book();
        $fixture->setTitle('Value');
        $fixture->setDescription('Value');
        $fixture->setPublication_year('Value');
        $fixture->setCover_image('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/book/');
        self::assertSame(0, $this->bookRepository->count([]));
    }
}
