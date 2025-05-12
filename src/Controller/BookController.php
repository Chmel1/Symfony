<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/book')]
final class BookController extends AbstractController
{
    #[Route(name: 'app_book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->isValid()) {
                // Вывести ошибки
                dd($form->getErrors(true));
            }
            $coverImage = $form->get('coverImage')->getData();
            
            if ($coverImage) {
                $originalFilename = pathinfo($coverImage->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $coverImage->guessExtension() ?? 'bin';
                $newFilename = uniqid().'.'.$extension;

                try {
                    $coverImage->move(
                        $this->getParameter('cover_image_directory'),
                        $newFilename
                    );
                    $book->setCoverImage($newFilename);
                } catch (FileException $e) {
                    // Обработка ошибки
                    $this-> addFlash('danger', 'Ошибка при загрузке файла: '.$e->getMessage());
                }
            }
            $entityManager->persist($book);
            $entityManager->flush();

            $this-> addFlash('success', 'Книга добавлена');
            return $this->redirectToRoute('app_book_index', ['id' => $book->getId()]);

            
        }

        return $this->render('book/new.html.twig', [
            'form' => $form->createView(),
        ], new Response('', $form->isSubmitted() ? 422 : 200));
    }

    #[Route('/{id}', name: 'app_book_show', methods: ['GET'])]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->isValid()) {
                // Вывести ошибки
                dd($form->getErrors(true));
            }
            $coverImage = $form->get('coverImage')->getData();

            if ($coverImage) {
                $originalFilename = pathinfo($coverImage->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $extension = $coverImage->guessExtension() ?? 'bin';
                $newFilename = uniqid().'.'.$extension;
                $this->addFlash('info', 'Загружен файл: ' . $newFilename);

                try {
                    $coverImage->move(
                        $this->getParameter('cover_image_directory'),
                        $newFilename
                    );
                    $book->setCoverImage($newFilename);
                } catch (FileException $e) {
                    // Обработка ошибки
                    $this->addFlash('danger', 'Ошибка при загрузке файла: '.$e->getMessage());
                }
            }
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('app_book_index');

            
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }
}
