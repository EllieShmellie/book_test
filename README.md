# Book Test

Тестовое Yii2-приложение: каталог книг и авторов с авторизацией, подписками на авторов и отчетом по авторам.

## Стек

- PHP 8.2
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
