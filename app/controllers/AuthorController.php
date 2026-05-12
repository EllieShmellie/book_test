<?php

namespace app\controllers;

use app\services\SubscribeService;
use Yii;
use app\models\Author;
use app\models\Subscriber;
use app\services\AuthorService;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class AuthorController extends Controller
{
    private const REPORT_LIMIT = 10;

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['index', 'view', 'subscribe', 'create', 'update', 'delete', 'report'],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'subscribe', 'report'],
                        'allow'   => true,
                        'roles'   => ['?', '@'],
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function __construct(
        $id,
        $module,
        private AuthorService $service,
        private SubscribeService $subscribeService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): string
    {
        $searchModel = new \app\models\AuthorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView(int $id): string
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate(): Response|string
    {
        $model = new Author();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $this->service->create($model);
                return $this->redirect(['view', 'id' => $model->author_id]);
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Произошла ошибка при сохранении автора.');
            }
        }
        
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $this->service->update($model);
                return $this->redirect(['view', 'id' => $model->author_id]);
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Произошла ошибка при обновлении автора.');
            }
        }
        
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete(int $id): Response
    {
        try {
            $this->service->delete($id);
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Произошла ошибка при удалении автора.');
        }
        
        return $this->redirect(['index']);
    }

    protected function findModel(int $id): Author
    {
        return $this->service->findModel($id);
    }

    public function actionSubscribe(int $id)
    {
        $author = $this->findModel($id);

        if (!Yii::$app->user->isGuest) {
            try {
                $this->subscribeService->subscribe($author->author_id);
                Yii::$app->session->setFlash('success', 'Вы успешно подписались на обновления автора ' . $author->last_name);
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('info', $e->getMessage());
            }
            return $this->redirect(['view', 'id' => $author->author_id]);
        }

        $model = new Subscriber();
        $model->author_id = $author->author_id;

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $this->subscribeService->subscribe($author->author_id, $model->phone);
                Yii::$app->session->setFlash('success', 'Вы успешно подписались на обновления автора ' . $author->last_name);
                return $this->redirect(['view', 'id' => $author->author_id]);
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Произошла ошибка при создании подписки.');
            }
        }

        return $this->render('subscribe', [
            'model'  => $model,
            'author' => $author,
        ]);
    }

    public function actionReport($year = null): string
    {
        $year = $this->normalizeReportYear($year);
        $authors = $year === null
            ? []
            : $this->service->getTopAuthors($year, self::REPORT_LIMIT);

        return $this->render('report', [
            'authors' => $authors,
            'year'    => $year,
        ]);
    }

    private function normalizeReportYear($year): ?int
    {
        if ($year === null || $year === '') {
            return null;
        }

        if (!ctype_digit((string) $year)) {
            throw new BadRequestHttpException('Год издания должен быть положительным числом.');
        }

        $year = (int) $year;
        if ($year < 1 || $year > 9999) {
            throw new BadRequestHttpException('Год издания должен быть в диапазоне от 1 до 9999.');
        }

        return $year;
    }
}
