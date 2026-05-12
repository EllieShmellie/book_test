<?php

namespace tests\unit\models;

use app\components\SmsSenderInterface;
use app\events\BookEvent;
use app\models\Author;
use app\models\AuthorBook;
use app\models\Book;
use app\models\Subscriber;
use app\models\User;
use app\repositories\AuthorBookRepository;
use app\repositories\AuthorRepository;
use app\repositories\BookRepository;
use app\repositories\SubscribeRepository;
use app\services\BookService;
use app\services\SubscribeService;
use Codeception\Test\Unit;
use Yii;
use yii\base\Event;

class CatalogTest extends Unit
{
    private int $isbnSequence = 1000000000000;

    protected function _before(): void
    {
        Event::off(Book::class, Book::EVENT_AFTER_CREATE);

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

    public function testBookIsbnMustBeUniqueAndNumeric(): void
    {
        $book = new Book([
            'title' => 'Первая книга',
            'year' => 2024,
            'isbn' => '1234567890',
        ]);

        $this->assertTrue($book->save());

        $duplicate = new Book([
            'title' => 'Вторая книга',
            'year' => 2025,
            'isbn' => '1234567890',
        ]);

        $this->assertFalse($duplicate->validate());
        $this->assertArrayHasKey('isbn', $duplicate->errors);

        $invalid = new Book([
            'title' => 'Третья книга',
            'year' => 2026,
            'isbn' => 'ISBN-123',
        ]);

        $this->assertFalse($invalid->validate());
        $this->assertArrayHasKey('isbn', $invalid->errors);
    }

    public function testSubscribeServiceRejectsDuplicateSubscription(): void
    {
        $author = $this->createAuthor('Лем', 'Станислав');
        $service = new SubscribeService(new SubscribeRepository(), $this->recordingSmsSender());

        $service->subscribe($author->author_id, '+79001234567');

        $this->expectException(\DomainException::class);

        $service->subscribe($author->author_id, '+79001234567');
    }

    public function testSubscribeServiceSendsMessageThroughInjectedSender(): void
    {
        $author = $this->createAuthor('Стругацкий', 'Аркадий');
        $book = $this->createBookWithAuthors('Понедельник начинается в субботу', 1965, [$author]);
        $sender = $this->recordingSmsSender();
        $service = new SubscribeService(new SubscribeRepository(), $sender);

        $subscriber = new Subscriber([
            'phone' => '+79001234567',
            'author_id' => $author->author_id,
        ]);

        $this->assertTrue($subscriber->save());

        $service->notify([$author->author_id], $book);

        $this->assertCount(1, $sender->messages);
        $this->assertSame('79001234567', $sender->messages[0]['to']);
        $this->assertStringContainsString($book->title, $sender->messages[0]['text']);
    }

    public function testBookServiceCreatesRelationsAndKeepsBookWhenNotificationFails(): void
    {
        $author = $this->createAuthor('Гоголь', 'Николай');
        $eventCalls = 0;
        $eventAuthorIds = [];

        $this->attachFailingEventHandler($eventCalls, $eventAuthorIds);

        $service = new BookService(new BookRepository(), new AuthorBookRepository());

        $book = new Book([
            'title' => 'Мертвые души',
            'year' => 1842,
            'isbn' => $this->isbn(),
            'author_ids' => [$author->author_id],
        ]);

        $service->create($book);

        $this->assertNotNull($book->book_id);
        $this->assertSame(1, $eventCalls);
        $this->assertSame([$author->author_id], $eventAuthorIds);
        $this->assertTrue(Book::find()->where(['book_id' => $book->book_id])->exists());
        $this->assertTrue(AuthorBook::find()->where([
            'book_id' => $book->book_id,
            'author_id' => $author->author_id,
        ])->exists());
    }

    public function testBookServiceDoesNotNotifyOnUpdate(): void
    {
        $author = $this->createAuthor('Чехов', 'Антон');
        $book = $this->createBookWithAuthors('Рассказы', 1899, [$author]);
        $eventCalls = 0;
        $eventAuthorIds = [];

        $this->attachFailingEventHandler($eventCalls, $eventAuthorIds);

        $service = new BookService(new BookRepository(), new AuthorBookRepository());

        $book->title = 'Избранные рассказы';
        $book->author_ids = [$author->author_id];

        $service->update($book);

        $this->assertSame(0, $eventCalls);
        $this->assertSame([], $eventAuthorIds);
        $this->assertSame('Избранные рассказы', Book::findOne($book->book_id)->title);
    }

    public function testBookServiceRemovesCoverWhenBookIsDeleted(): void
    {
        $author = $this->createAuthor('Брэдбери', 'Рэй');
        $book = $this->createBookWithAuthors('451 градус по Фаренгейту', 1953, [$author]);
        $book->cover = 'cover_delete_test.png';
        $this->assertTrue($book->save(false));

        $coverPath = Yii::getAlias('@covers') . '/' . $book->cover;
        $this->writeCoverFile($coverPath);

        $service = new BookService(new BookRepository(), new AuthorBookRepository());
        $service->delete($book->book_id);

        $this->assertFalse(Book::find()->where(['book_id' => $book->book_id])->exists());
        $this->assertFileDoesNotExist($coverPath);
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

    private function attachFailingEventHandler(int &$eventCalls, array &$eventAuthorIds): void
    {
        Event::on(
            Book::class,
            Book::EVENT_AFTER_CREATE,
            function (BookEvent $event) use (&$eventCalls, &$eventAuthorIds) {
                $eventCalls++;
                $eventAuthorIds = $event->authorIds;

                throw new \RuntimeException('SMS provider is unavailable.');
            },
        );
    }

    private function recordingSmsSender(): SmsSenderInterface
    {
        return new class implements SmsSenderInterface {
            public array $messages = [];

            public function sendBatch(array $messages, array $additionalParams = []): ?array
            {
                $this->messages = $messages;

                return ['send' => $messages];
            }
        };
    }

    private function isbn(): string
    {
        return (string) $this->isbnSequence++;
    }

    private function writeCoverFile(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==')
        );
    }
}
