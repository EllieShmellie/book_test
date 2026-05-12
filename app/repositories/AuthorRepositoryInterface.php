<?php

namespace app\repositories;

use app\models\Author;

interface AuthorRepositoryInterface
{
    public function findById(int $id): Author;

    /** @return Author[] */
    public function findAll(): array;

    /** @return Author[] */
    public function getTopAuthors(int $year, int $limit = 10): array;

    /** @return Author[] */
    public function getAuthors(): array;
}
