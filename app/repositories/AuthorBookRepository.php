<?php

namespace app\repositories;

use app\models\AuthorBook;
use app\models\Book;
use yii\db\Exception;

class AuthorBookRepository
{
    /**
     * @return int[] IDs of newly added authors
     */
    public function syncAuthors(Book $model): array
    {
        $currentAuthorIds = AuthorBook::find()
            ->select('author_id')
            ->where(['book_id' => $model->book_id])
            ->column();

        $newAuthorIds = $model->author_ids;

        $authorsToAdd    = array_diff($newAuthorIds, $currentAuthorIds);
        $authorsToRemove = array_diff($currentAuthorIds, $newAuthorIds);

        if (!empty($authorsToRemove)) {
            AuthorBook::deleteAll([
                'book_id'   => $model->book_id,
                'author_id' => $authorsToRemove,
            ]);
        }

        foreach ($authorsToAdd as $authorId) {
            $authorBook = new AuthorBook();
            $authorBook->book_id = $model->book_id;
            $authorBook->author_id = $authorId;
            if (!$authorBook->save()) {
                throw new Exception(
                    'Ошибка при сохранении связи между книгой и автором: '
                    . implode(', ', $authorBook->getFirstErrors())
                );
            }
        }

        return $authorsToAdd;
    }
}
