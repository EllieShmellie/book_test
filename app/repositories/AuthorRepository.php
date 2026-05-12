<?php

namespace app\repositories;

use app\models\Author;
use yii\web\NotFoundHttpException;

class AuthorRepository
{
    public function findById($id): Author
    {
        if (($model = Author::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException("Автор не найден.");
    }

    public function findAll(): array
    {
        return Author::find()->all();
    }

    /**
     * @param int $year
     * @param int $limit
     * @return Author[]
     */
    public function getTopAuthors(int $year, int $limit = 10): array
    {
        return Author::find()
            ->alias('a')
            ->select(['a.*', 'COUNT(b.book_id) AS booksCount'])
            ->joinWith(['books b'])
            ->andWhere(['YEAR(b.created_at)' => $year])
            ->groupBy('a.author_id')
            ->orderBy(['booksCount' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * @return Author[]
     */
    public function getAuthors(): array
    {
        return Author::find()->all();
    }
    
}
