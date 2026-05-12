<?php

namespace app\services;

use app\models\Author;
use app\repositories\AuthorRepositoryInterface;
use yii\db\Exception;

class AuthorService
{
    public function __construct(private AuthorRepositoryInterface $repository)
    {
    }

    public function save(Author $model): void
    {
        if (!$model->save()) {
            throw new Exception('Ошибка при сохранении автора: ' . implode(', ', $model->getFirstErrors()));
        }
    }

    public function create(Author $model): void
    {
        $this->save($model);
    }

    public function update(Author $model): void
    {
        $this->save($model);
    }

    public function delete(int $id): void
    {
        $model = $this->repository->findById($id);
        if (!$model->delete()) {
            throw new Exception('Ошибка при удалении автора.');
        }
    }

    /**
     * @param int $id
     * @return Author
     */
    public function findModel(int $id): Author
    {
        return $this->repository->findById($id);
    }

    /**
     * @param int $year
     * @param int $limit
     * @return array
     */
    public function getTopAuthors(int $year, int $limit = 10): array
    {
        return $this->repository->getTopAuthors($year, $limit);
    }

    public function getAuthors(): array
    {
        return $this->repository->getAuthors();
    }
}
