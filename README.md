# Book Test

Тестовое Yii2-приложение: каталог книг и авторов с авторизацией, подписками на авторов и отчетом по авторам.

## Что реализовано

- CRUD книг.
- CRUD авторов.
- Связь книга-автор many-to-many.
- Публичный просмотр каталога книг и авторов.
- Создание, редактирование и удаление доступны только авторизованным пользователям.
- Регистрация и вход по телефону и паролю.
- Подписка гостя на появление новых книг конкретного автора.
- SMS-уведомления через SMSPilot с тестовым ключом `эмулятор`.
- Отчет: топ-10 авторов по количеству книг за указанный год.

## Стек

- PHP 8.0
- Yii2 Basic
- MariaDB / MySQL
- Nginx
- Docker Compose

## Структура

```text
app/                 Yii2-приложение
app/controllers/     Web-контроллеры
app/models/          ActiveRecord-модели и формы
app/migrations/      Миграции БД
app/services/        Бизнес-логика
app/repositories/    Работа с данными
docker/              Dockerfile и Nginx-конфиг
docker-compose.yml   Локальное окружение
```

## Быстрый запуск

Из корня репозитория:

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php yii migrate
```

После запуска приложение доступно по адресу:

```text
http://localhost:8080
```

## Полезные страницы

- Каталог авторов: `http://localhost:8080/author/index`
- Каталог книг: `http://localhost:8080/book/index`
- Отчет по авторам: `http://localhost:8080/author/report?year=2025`
- Регистрация: `http://localhost:8080/site/signup`
- Вход: `http://localhost:8080/site/login`

## Конфигурация

Значения окружения для локального запуска лежат в `app/.env.example` и подключены в `docker-compose.yml`.

Основные параметры:

```dotenv
DB_HOST=db
DB_NAME=testdb
DB_USER=root
DB_PASSWORD=secret
SMS_PILOT_API_KEY=эмулятор
```

Для реального окружения можно заменить `SMS_PILOT_API_KEY` на рабочий ключ SMSPilot.

## Миграции

Накатить миграции:

```bash
docker compose exec app php yii migrate
```

Откатить последнюю миграцию:

```bash
docker compose exec app php yii migrate/down 1
```

## Заметки по реализации

- Обложки книг сохраняются локально в `app/web/images/covers`.
- Уникальность связи книга-автор обеспечена на уровне модели `AuthorBook` и уникального индекса в миграции `author_book`.
- Подписка хранится по паре `phone + author_id`, повторная подписка на того же автора запрещена.
- Директории `vendor`, `runtime` и `web/assets` не должны попадать в репозиторий.
