# Бронирование услуг
![PHP](https://img.shields.io/badge/PHP-8.1-purple)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![Vue](https://img.shields.io/badge/Vue.js-3.x-green)
![MySQL](https://img.shields.io/badge/MySQL-8.x-blue)

## Особенности
 Интуитивный интерфейс - пошаговый процесс бронирования с визуальным календарем
 Real-time проверка доступности - мгновенное отображение свободных слотов
 Защита от двойного бронирования - система блокировок для предотвращения race condition (lockForUpdate() - блокирует таблицу для других запросов)
 Адаптивный дизайн - корректное отображение на всех устройствах
 Гибкая настройка услуг - поддержка услуг любой длительности
 Автоматический расчет времени - учет длительности услуги + 30 минут на подготовку
 Поддержка временных зон - корректная работа с московским временем

## Технологии

Backend:
Laravel
PHP 
MySQL 
Carbon для работы с датами

Frontend:
Vue.js
Composition API
Inertia.js
Bootstrap 5

Безопасность:
CSRF 
Валидация на стороне сервера
SQL injection protection
XSS 

## Быстрый старт
Предварительные требования
PHP 8.1+
Composer
MySQL 8.x
Node.js 16+

Установка
Клонирование репозитория
```bash
git clone https://github.com/your-username/booking-system.git
cd booking-system
```
Установка зависимостей
```bash
composer install
npm install
```
Настройка окружения
```bash
cp .env.example .env
php artisan key:generate
```
Настройка базы данных
```bash
# В файле .env = настройки БД
DB_DATABASE=booking_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```
Запуск миграций и сидеров
```bash
php artisan migrate
php artisan db:seed
```
Запуск приложения
```bash
php artisan serve
```
# Frontend сборка
npm run dev
Приложение будет доступно по адресу: http://localhost:8000

## Cтруктура базы данных
Система бронирования использует реляционную базу данных со следующими таблицами и связями.

# Схема базы данных
Таблица: services (Услуги)
Хранит информацию о доступных для бронирования услугах.

Поле	Тип	Атрибуты	Описание
id	bigint	PRIMARY KEY, AUTO_INCREMENT	Уникальный идентификатор услуги
name	varchar(255)	NOT NULL	Название услуги (например, "Поездка на квадроцикле")
duration	int	NOT NULL	Длительность услуги в минутах (например, 30, 60, 120)
created_at	timestamp	NULLABLE	Метка времени создания записи
updated_at	timestamp	NULLABLE	Метка времени последнего обновления
Пример данных:

id	name	duration	created_at	updated_at
1	Поездка на квадроцикле (30 мин)	30	2025-10-01 10:00:00	2025-10-01 11:00:00
2	Тур на эндуро (120 мин)	120	2025-11-11 10:00:00	2025-11-11 11:00:00
Таблица: bookings (Бронирования)
Хранит информацию о всех совершённых бронированиях.

Поле	Тип	Атрибуты	Описание
id	bigint	PRIMARY KEY, AUTO_INCREMENT	Уникальный идентификатор бронирования
service_id	bigint	FOREIGN KEY, NOT NULL	Ссылка на идентификатор услуги из таблицы services
customer_name	varchar(255)	NOT NULL	Имя клиента
customer_phone	varchar(20)	NOT NULL	Телефон клиента
start_time	datetime	NOT NULL	Дата и время начала бронирования (ключевое поле для проверки доступности)
end_time	datetime	NOT NULL	Дата и время окончания бронирования. Рассчитывается как start_time + длительность услуги + 30 минут на подготовку
created_at	timestamp	NULLABLE	Метка времени создания записи
updated_at	timestamp	NULLABLE	Метка времени последнего обновления

# Связи между таблицами (Relationships)
Service (1) → (hasMany) → (N) Booking
Одна Услуга может иметь много Бронирований.
Реализовано через метод bookings() в модели Service.php.
Booking (N) → (belongsTo) → (1) Service
Каждое Бронирование принадлежит одной Услуге.
Реализовано через метод service() в модели Booking.php.
Внешний ключ service_id в таблице bookings обеспечивает целостность данных (ON DELETE CASCADE).

##  Автор

- GitHub: [@yourusername](https://github.com/Fotonchik)
- Portfolio: [yourportfolio.com](https://hh.ru/resume/4bde0dbeff0b000ce70039ed1f696b666c5642)

 # Результат
<img width="925" height="758" alt="image" src="https://github.com/user-attachments/assets/50294718-30eb-4c56-8747-6b286a060d7b" />
<img width="873" height="756" alt="image" src="https://github.com/user-attachments/assets/c79e0159-b721-4008-bb89-8fbb25b4bc72" />
<img width="931" height="862" alt="image" src="https://github.com/user-attachments/assets/c78967c5-b3c0-44ee-9c78-3c0d250cccc9" />
<img width="866" height="799" alt="image" src="https://github.com/user-attachments/assets/fbf955b3-a224-413d-a268-cb51235d4643" />
<img width="924" height="812" alt="image" src="https://github.com/user-attachments/assets/5259a513-a6fc-4eb5-bbb9-9f1071bf7d45" />
<img width="853" height="811" alt="image" src="https://github.com/user-attachments/assets/525227ef-b415-4ff3-b707-720e814dadb9" />
<img width="939" height="835" alt="image" src="https://github.com/user-attachments/assets/dcd65e11-a55f-4d65-8c40-2b17ad3d230a" />
