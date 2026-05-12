<?php

namespace app\services;

use Yii;
use app\models\Book;
use app\repositories\BookRepository;
use yii\db\Exception;
use app\models\AuthorBook;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class BookService
{
    public function __construct(private BookRepository $repository, private SubscribeService $subscribeService)
    {
    }

    public function create(Book $model): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        $authorsToNotify = [];
        $uploadedCover = null;

        try {
            $uploadedCover = $this->uploadCoverFile($model);

            if (!$model->save(false)) {
                throw new Exception('Ошибка при создании книги: ' . implode(', ', $model->getFirstErrors()));
            }

            $authorsToNotify = $this->saveAuthorBooks($model);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->deleteCoverFile($uploadedCover);
            throw $e;
        }

        $this->notifySubscribers($authorsToNotify, $model);
    }

    public function update(Book $model): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        $oldCover = $model->cover;
        $uploadedCover = null;

        try {
            $uploadedCover = $this->uploadCoverFile($model);

            if (!$model->save(false)) {
                throw new Exception('Ошибка при обновлении книги: ' . implode(', ', $model->getFirstErrors()));
            }

            $this->saveAuthorBooks($model);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->deleteCoverFile($uploadedCover);
            throw $e;
        }

        if ($uploadedCover !== null && $oldCover !== $uploadedCover) {
            $this->deleteCoverFile($oldCover);
        }
    }

    public function delete($id): void
    {
        $model = $this->repository->findById($id);
        $cover = $model->cover;
        if (!$model->delete()) {
            throw new Exception('Ошибка при удалении книги.');
        }

        $this->deleteCoverFile($cover);
    }

    public function findModel($id): Book
    {
        return $this->repository->findById($id, true);
    }

    /**
     * @param Book $model
     * @throws Exception
     */
    private function uploadCoverFile(Book $model): ?string
    {
        $model->cover_file = UploadedFile::getInstance($model, 'cover_file');
        if ($model->cover_file) {
            $uploadError = (int) $model->cover_file->error;
            if ($uploadError !== UPLOAD_ERR_OK) {
                throw new Exception($this->uploadErrorMessage($uploadError));
            }

            $fileName = uniqid('cover_') . '.' . $model->cover_file->extension;
            $dir = Yii::getAlias('@covers');
            $filePath = $dir . '/' . $fileName;
            if (!FileHelper::createDirectory($dir) || !is_writable($dir)) {
                throw new Exception('Директория для обложек недоступна для записи.');
            }
            if (!$model->cover_file->saveAs($filePath, false)) {
                throw new Exception('Ошибка при загрузке обложки.');
            }
            $model->cover = $fileName;

            return $fileName;
        }

        return null;
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => 'Размер обложки превышает допустимый лимит.',
            UPLOAD_ERR_PARTIAL => 'Файл обложки был загружен не полностью.',
            UPLOAD_ERR_NO_TMP_DIR => 'Временная директория для загрузки файлов недоступна.',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл обложки на диск.',
            UPLOAD_ERR_EXTENSION => 'Загрузка обложки остановлена расширением PHP.',
            default => 'Ошибка при загрузке обложки.',
        };
    }

    private function deleteCoverFile(?string $fileName): void
    {
        if ($fileName === null || $fileName === '') {
            return;
        }

        $path = Yii::getAlias('@covers') . '/' . basename($fileName);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    protected function saveAuthorBooks(Book $model): array
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

        return $authorsToAdd;
    }

    private function notifySubscribers(array $authorIds, Book $book): void
    {
        try {
            $this->subscribeService->notify($authorIds, $book);
        } catch (\Throwable $e) {
            Yii::warning($e->getMessage(), __METHOD__);
        }
    }
}
