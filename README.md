# Notification Service
#### Микросервис для массовой рассылки уведомлений (SMS/Email) с поддержкой приоритетов, дедупликации и гарантией доставки at-least-once.

## Технологический стек

- **Laravel 12**
- **PHP 8.4+**
- **PostgreSQL 17**
- **RabbitMQ 4.3**
- **Redis**
- **Nginx**
- **Docker**


## Запуск сервиса
1. Клонируйте репозиторий

```bash 
git clone https://github.com/David-Gelmut/logistic_test.git
```


2. Поднимите инфраструктуру (Docker)

```docker-compose up -d --build```

3. Установите зависимости и настройте БД

```bash 
    docker exec -it notification_app composer install
```
```bash 
docker exec -it notification_app php artisan migrate
```
```bash
 docker exec -it notification_app php artisan db:seed --class=UserSeeder
 ```

4. Запустите тесты


Сервис доступен локально по http://localhost:8080/

## Дедупликация и Идемпотентность

Реализована защита от повторных запросов:
1. Блокировка дублей на входе (Redis Cache).
2. Составной уникальный индекс (request_id, user_id) в PostgreSQL.

## Приоритезация

Воркер настроен на работу с очередями в порядке значимости:php artisan queue:work --queue=high,low
Транзакционные сообщения (priority = high) обрабатываются раньше маркетинговых рассылок (priority = low).

## Гарантия доставки (Reliability)

1. Retry Mechanism: 3 попытки отправки с экспоненциальной задержкой.
2. Exactly-once: Достигается проверкой статуса сообщения (delivered) в Job перед вызовом провайдера.
3. Внешние шлюзы имитируются классами-заглушками с шансом случайной ошибки (1 к 10) для проверки устойчивости системы.
4. Cистема проверяет статус сообщений failed и продолжает делать запросы к шлюзу (hourly): php artisan schedule:work (retry_count<100)

## API Документация
1. Запуск рассылки: 
``` POST /api/v1/notifications ```

   ``` json
   {
   "request_id": "uuid",
   "channel": "sms",
   "priority": "high",
   "message": "Ваш код: 1234",
   "recipient_ids": [1, 2, 3]
   } 
   ```
2. История подписчика
``` GET /api/v1/notifications/history/{user_id} ```

3. В корне проекта находится файл Notification_Service.postman_collection.json. 
   Импортируйте его в Postman, переменная {{base_url}} по умолчанию localhost:8080

## Дополнительно
1. Для просмотра очередей зайдите в RabbitMQ Management: http://localhost:15672 (guest/guest).
2. Для просмотра кэша дедупликации используйте Redis Insight на порту 6379
