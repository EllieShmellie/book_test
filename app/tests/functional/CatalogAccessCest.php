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
        foreach (glob(Yii::getAlias('@covers') . '/cover_*') ?: [] as $coverFile) {
            unlink($coverFile);
        }

        AuthorBook::deleteAll();
        Subscriber::deleteAll();
        Book::deleteAll();
        Author::deleteAll();
        User::deleteAll();
    }

    public function _after(FunctionalTester $I): void
    {
        $coverPath = codecept_data_dir('cover.png');
        if (is_file($coverPath)) {
            unlink($coverPath);
        }
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
        $this->createAuthor();

        $I->amLoggedInAs($this->createUser());
        $I->amOnRoute('book/create');

        $I->see('Создать книгу', 'h1');
        $I->see('Год', 'label');
        $I->seeElement('#book-author_ids-search');
        $I->see('Иванов Иван');
    }

    public function authorizedUserCanCreateBookWithCover(FunctionalTester $I): void
    {
        $author = $this->createAuthor();
        $this->ensureTestCoverFile();

        $I->amLoggedInAs($this->createUser());
        $I->amOnRoute('book/create');
        $I->fillField('Book[title]', 'Книга с обложкой');
        $I->fillField('Book[year]', '2026');
        $I->fillField('Book[description]', 'Описание');
        $I->fillField('Book[isbn]', '9781234567897');
        $I->attachFile('//input[@type="file" and @name="Book[cover_file]"]', 'cover.png');
        $I->checkOption("//input[@name='Book[author_ids][]' and @value='{$author->author_id}']");
        $I->click('Создать');

        $I->see('Книга с обложкой', 'h1');

        $book = Book::findOne(['isbn' => '9781234567897']);
        $I->assertNotNull($book);
        $I->assertNotEmpty($book->cover);
        $I->assertFileExists(Yii::getAlias('@covers') . '/' . $book->cover);
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

    public function guestSeesReportFormWithoutDefaultYear(FunctionalTester $I): void
    {
        $I->amOnRoute('author/report');

        $I->see('Топ-10 авторов по году издания', 'h1');
        $I->see('Выберите год издания, чтобы построить отчет.');
        $I->dontSee('За выбранный год данных нет.');
    }

    public function guestCanBuildReportForPublicationYear(FunctionalTester $I): void
    {
        $this->createBookWithAuthor();

        $I->amOnRoute('author/report', ['year' => 2026]);

        $I->see('Топ-10 авторов за 2026 год', 'h1');
        $I->see('Иванов');
        $I->see('1', 'td');
    }

    public function guestCannotBuildReportWithInvalidYear(FunctionalTester $I): void
    {
        $I->amOnRoute('author/report', ['year' => 'abc']);

        $I->seeResponseCodeIs(400);
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

    private function ensureTestCoverFile(): void
    {
        $coverPath = codecept_data_dir('cover.png');
        if (is_file($coverPath)) {
            return;
        }

        file_put_contents(
            $coverPath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==')
        );
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
