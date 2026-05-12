<?php

namespace app\services;

use Yii;
use app\models\Book;
use app\repositories\BookRepository;
use yii\db\Exception;
use app\models\AuthorBook;
use yii\web\UploadedFile;

class BookService
{

    public function __construct(private BookRepository $repository, private SubscribeService $subscribeService)
    {

    }

    public function create(Book $model): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->uploadCoverFile($model);

            if (!$model->save(false)) {
                throw new Exception('Ошибка при создании книги: ' . implode(', ', $model->getFirstErrors()));
            }
            
            $this->saveAuthorBooks($model);
            
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function update(Book $model): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->uploadCoverFile($model);

            if (!$model->save(false)) {
                throw new Exception('Ошибка при обновлении книги: ' . implode(', ', $model->getFirstErrors()));
            }
            
            $this->saveAuthorBooks($model);
            
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    }

    public function delete($id): void
    {
        $model = $this->repository->findById($id);
        if (!$model->delete()) {
            throw new Exception('Ошибка при удалении книги.');
        }
    }

    public function findModel($id): Book
    {
        return $this->repository->findById($id, true);
    }

    /**
     * @param Book $model
     * @throws Exception
     */
    private function uploadCoverFile(Book $model): void
    {
        $model->cover_file = UploadedFile::getInstance($model, 'cover_file');
        if ($model->cover_file) {
            $fileName = uniqid('cover_') . '.' . $model->cover_file->extension;
            $dir = Yii::getAlias('@covers');
            $filePath = $dir . '/' . $fileName;
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            if (!$model->cover_file->saveAs($filePath)) {
                throw new Exception('Ошибка при загрузке обложки.');
            }
            $model->cover = $fileName;
        }
    }


    protected function saveAuthorBooks(Book $model): void
    {
        $currentAuthorIds = AuthorBook::find()
            ->select('author_id')
            ->where(['book_id' => $model->book_id])
            ->column();

        $newAuthorIds = is_array($model->author_ids) ? $model->author_ids : [];

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
                throw new Exception('Ошибка при сохранении связи между книгой и автором: ' . implode(', ', $authorBook->getFirstErrors()));
            }
        }
        $this->subscribeService->notify($authorsToAdd, $model);
    }
}
