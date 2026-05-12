<?php

namespace app\services;

use Yii;
use app\models\Book;
use app\repositories\BookRepository;
use app\repositories\AuthorBookRepository;
use app\events\BookEvent;
use yii\db\Exception;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class BookService
{
    public function __construct(
        private BookRepository $repository,
        private AuthorBookRepository $authorBookRepository,
    ) {
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

            $authorsToNotify = $this->authorBookRepository->syncAuthors($model);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->deleteCoverFile($uploadedCover);
            throw $e;
        }

        try {
            $model->trigger(Book::EVENT_AFTER_CREATE, new BookEvent($model, $authorsToNotify));
        } catch (\Throwable $e) {
            Yii::warning($e->getMessage(), __METHOD__);
        }
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

            $this->authorBookRepository->syncAuthors($model);

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

    public function delete(int $id): void
    {
        $model = $this->repository->findById($id);
        $cover = $model->cover;
        if (!$model->delete()) {
            throw new Exception('Ошибка при удалении книги.');
        }

        $this->deleteCoverFile($cover);
    }

    public function findModel(int $id): Book
    {
        return $this->repository->findById($id, true);
    }

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
}
