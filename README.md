# Student Attendance Management System API

A robust REST API system for managing student attendance, built with Laravel. The system handles attendance tracking, automated absence notifications, and comprehensive reporting.

## Features

- **Attendance Management**
  - Mark attendance for individual students
  - Bulk attendance marking
  - Support for multiple subjects
  - Real-time attendance tracking

- **Automated Notifications**
  - Automatic warning emails after 5 absences per month
  - Suspension notices after 10 absences per term
  - Queue-based notification processing

- **Reporting System**
  - Individual student attendance reports
  - Class-wide attendance summaries
  - Cached reports for improved performance
  - Monthly and term-based absence tracking

- **Security & Performance**
  - API authentication using Laravel Sanctum
  - Rate limiting protection
  - Database query optimization
  - Response caching
  - Input validation

## Requirements

- PHP 8.1 or higher
- Laravel 10.x
- MySQL/PostgreSQL
- Redis (optional, for caching)
- Composer

## Installation

### Clone the repository

### Install dependencies

```bash
composer install
```

### Copy the environment file

```bash
cp .env.example .env
```

### Configure your database in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_system
DB_USERNAME=root
DB_PASSWORD=
```

### Generate application key

```bash
php artisan key:generate
```

### Run migrations

```bash
php artisan migrate
```

### Start the queue worker

```bash
php artisan queue:work
```

### Seed the database with initial data

```bash
php artisan db:seed
```

## API Documentation

### Authentication

All API endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:

```json
Authorization: Bearer <your_token>
```

### Endpoints

#### Mark Attendance (Single Student)

```http
POST /api/v1/attendance/{student}

{
    "date": "2024-03-14",
    "status": "present",
    "subject_id": 1
}
```

#### Bulk Attendance Marking

```http
POST /api/v1/attendance/bulk

{
    "attendances": [
        {
            "student_id": 1,
            "date": "2024-03-14",
            "status": "present",
            "subject_id": 1
        }
    ]
}
```

#### Get Student Report

```http
GET /api/v1/attendance/report/{student}
```

#### Get Class Report

```http
GET /api/v1/attendance/class/{class}
```

### Rate Limiting

The API implements rate limiting of 60 requests per minute per user.

## Testing

Run the test suite:

```bash
php artisan test
```

The system includes:

- Feature tests for API endpoints
- Unit tests for notification processing
- Authentication testing
- Rate limiting tests
- Cache testing

## Database Structure

### Students Table

- id (primary key)
- name
- email (unique)
- parent_email
- class_id (foreign key)
- timestamps

### School Classes Table

- id (primary key)
- name
- grade
- section
- academic_year
- timestamps

### Subjects Table

- id (primary key)
- name
- code (unique)
- description
- timestamps

### Attendances Table

- id (primary key)
- student_id (foreign key)
- subject_id (foreign key)
- date
- status (enum: present, absent)
- timestamps

## Performance Optimizations

1. **Database Indexes**
   - Compound index on (student_id, date, status)
   - Index on (date, status) for class queries
   - Index on (student_id, status) for absence counting

2. **Caching**
   - Student attendance reports cached for 1 hour
   - Class attendance reports cached for 1 hour
   - Automatic cache invalidation on attendance updates

3. **Queue Processing**
   - Asynchronous notification processing
   - Background job handling for absence notifications
