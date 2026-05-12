<?php

namespace app\repositories;

use app\models\Book;
use yii\web\NotFoundHttpException;
class BookRepository implements BookRepositoryInterface
{

    /**
     * Summary of findById
     * @param int $id
     * @param bool $withAuthors
     * @throws NotFoundHttpException
     * @return Book
     */
    public function findById(int $id, bool $withAuthors = false): Book
    {
        $query = Book::find();
        if ($withAuthors) {
            $query->with('authors');
        }

        if (($model = $query->where(['book_id' => $id])->one()) !== null) {
            return $model;
        }
        throw new NotFoundHttpException("Книга не найдена.");
    }
    
    /**
     * Summary of findAll
     * @return Book[]
     */
    public function findAll(): array
    {
        return Book::find()->all();
    }
    
}
