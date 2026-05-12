<?php

use app\models\Author;
use app\models\AuthorBook;
use app\models\Book;
use app\models\Subscriber;
use app\models\User;

class CatalogAccessCest
{
    public function _before(FunctionalTester $I): void
    {
        AuthorBook::deleteAll();
        Subscriber::deleteAll();
        Book::deleteAll();
        Author::deleteAll();
        User::deleteAll();
    }

    public function guestCanViewCatalog(FunctionalTester $I): void
    {
        $this->createBookWithAuthor();

        $I->amOnRoute('book/index');
        $I->see('Книги', 'h1');
        $I->see('Тестовая книга');

        $I->amOnRoute('author/index');
        $I->see('Авторы', 'h1');
        $I->see('Иванов');
    }

    public function guestCannotOpenCreateBookPage(FunctionalTester $I): void
    {
        $I->amOnRoute('book/create');

        $I->see('Login', 'h1');
        $I->dontSee('Создать книгу', 'h1');
    }

    public function authorizedUserCanOpenCreateBookPage(FunctionalTester $I): void
    {
        $I->amLoggedInAs($this->createUser());
        $I->amOnRoute('book/create');

        $I->see('Создать книгу', 'h1');
    }

    public function guestCanSubscribeToAuthor(FunctionalTester $I): void
    {
        $author = $this->createAuthor();

        $I->amOnRoute('author/subscribe', ['id' => $author->author_id]);
        $I->submitForm('form', [
            'Subscriber[phone]' => '+79001234567',
        ]);

        $I->seeRecord(Subscriber::class, [
            'phone' => '+79001234567',
            'author_id' => $author->author_id,
        ]);
    }

    private function createBookWithAuthor(): void
    {
        $author = $this->createAuthor();
        $book = new Book([
            'title' => 'Тестовая книга',
            'year' => 2026,
            'isbn' => '1000000000001',
        ]);
        $book->save(false);

        (new AuthorBook([
            'author_id' => $author->author_id,
            'book_id' => $book->book_id,
        ]))->save(false);
    }

    private function createAuthor(): Author
    {
        $author = new Author([
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
        ]);
        $author->save(false);

        return $author;
    }

    private function createUser(): User
    {
        $user = new User([
            'phone' => '+79007654321',
        ]);
        $user->setPassword('secret123');
        $user->generateAuthKey();
        $user->save(false);

        return $user;
    }
}
