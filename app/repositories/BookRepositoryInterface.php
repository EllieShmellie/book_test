<?php

namespace app\repositories;

use app\models\Book;

interface BookRepositoryInterface
{
    public function findById(int $id, bool $withAuthors = false): Book;

    /** @return Book[] */
    public function findAll(): array;
}
