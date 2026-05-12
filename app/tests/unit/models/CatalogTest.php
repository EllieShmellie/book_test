<?php

namespace tests\unit\models;

use app\models\Author;
use app\models\AuthorBook;
use app\models\Book;
use app\models\Subscriber;
use app\models\User;
use app\repositories\AuthorRepository;
use Codeception\Test\Unit;

class CatalogTest extends Unit
{
    private int $isbnSequence = 1000000000000;

    protected function _before(): void
    {
        AuthorBook::deleteAll();
        Subscriber::deleteAll();
        Book::deleteAll();
        Author::deleteAll();
        User::deleteAll();
    }

    public function testAuthorBookRelationIsUnique(): void
    {
        $author = $this->createAuthor('Пушкин', 'Александр');
        $book = $this->createBook('Капитанская дочка', 1836);

        $relation = new AuthorBook([
            'author_id' => $author->author_id,
            'book_id' => $book->book_id,
        ]);

        $this->assertTrue($relation->save());

        $duplicate = new AuthorBook([
            'author_id' => $author->author_id,
            'book_id' => $book->book_id,
        ]);

        $this->assertFalse($duplicate->validate());
        $this->assertArrayHasKey('author_id', $duplicate->errors);
    }

    public function testTopAuthorsReportUsesBookPublicationYear(): void
    {
        $firstAuthor = $this->createAuthor('Булгаков', 'Михаил');
        $secondAuthor = $this->createAuthor('Толстой', 'Лев');

        $this->createBookWithAuthors('Мастер и Маргарита', 2024, [$firstAuthor]);
        $this->createBookWithAuthors('Собачье сердце', 2024, [$firstAuthor]);
        $this->createBookWithAuthors('Белая гвардия', 2025, [$firstAuthor]);
        $this->createBookWithAuthors('Анна Каренина', 2024, [$secondAuthor]);

        $authors = (new AuthorRepository())->getTopAuthors(2024);

        $this->assertCount(2, $authors);
        $this->assertSame($firstAuthor->author_id, $authors[0]->author_id);
        $this->assertSame(2, (int) $authors[0]->booksCount);
        $this->assertSame($secondAuthor->author_id, $authors[1]->author_id);
        $this->assertSame(1, (int) $authors[1]->booksCount);
    }

    public function testSubscriberIsUniquePerAuthor(): void
    {
        $author = $this->createAuthor('Достоевский', 'Федор');

        $subscriber = new Subscriber([
            'phone' => '+79001234567',
            'author_id' => $author->author_id,
        ]);

        $this->assertTrue($subscriber->save());

        $duplicate = new Subscriber([
            'phone' => '+79001234567',
            'author_id' => $author->author_id,
        ]);

        $this->assertFalse($duplicate->validate());
        $this->assertArrayHasKey('phone', $duplicate->errors);
    }

    private function createAuthor(string $lastName, string $firstName): Author
    {
        $author = new Author([
            'last_name' => $lastName,
            'first_name' => $firstName,
        ]);

        $this->assertTrue($author->save());

        return $author;
    }

    /**
     * @param Author[] $authors
     */
    private function createBookWithAuthors(string $title, int $year, array $authors): Book
    {
        $book = $this->createBook($title, $year);

        foreach ($authors as $author) {
            $relation = new AuthorBook([
                'author_id' => $author->author_id,
                'book_id' => $book->book_id,
            ]);
            $this->assertTrue($relation->save());
        }

        return $book;
    }

    private function createBook(string $title, int $year): Book
    {
        $book = new Book([
            'title' => $title,
            'year' => $year,
            'isbn' => $this->isbn(),
        ]);

        $this->assertTrue($book->save());

        return $book;
    }

    private function isbn(): string
    {
        return (string) $this->isbnSequence++;
    }
}
