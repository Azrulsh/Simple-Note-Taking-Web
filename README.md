# Simple Note-Taking Web Application

A Laravel 11 web application for managing personal notes and remarks. Users can register, log in, create and manage notes, and add remarks to notes through an AJAX-powered interface. Ownership checks ensure that only the creator of a note or remark can modify or delete it.

## Features

### Authentication
- User registration and login using a username and password.
- Hashed passwords, session-based authentication, and POST logout.
- Login rate limiting to reduce repeated failed login attempts.
- Protected Note and Remark routes using Laravel's `auth` middleware.

### Note Management
- Create, view, update, and delete notes.
- Display a note's creator.
- Validate required fields and enforce a 255-character limit for note fields.
- Restrict note editing and deletion to its creator.

### Remark Management
- Create, view, update, and delete remarks associated with a note.
- Use jQuery AJAX to update the remark interface without a full page reload.
- Use Bootstrap modals for remark forms, deletion confirmation, and permission feedback.
- Restrict remark editing and deletion to its creator, including server-side permission checks.
- Automatically delete associated remarks when a note is deleted through database foreign-key cascading.

## Tech Stack

| Component | Technology |
| --- | --- |
| Backend | PHP 8.2+, Laravel 11 |
| Database | MySQL / MariaDB (tested locally with XAMPP) |
| Frontend | Laravel Blade, HTML, CSS, Bootstrap 5 |
| Dynamic interactions | JavaScript, jQuery AJAX, SweetAlert2 |
| Testing | PHPUnit / Laravel Feature Tests |
| Frontend tooling | Node.js, npm, Vite |

## Database Structure

The application uses custom table names:

| Model | Table | Purpose |
| --- | --- | --- |
| `User` | `login_record` | Registered users |
| `NoteRecord` | `note_record` | Notes and their creators |
| `RemarkRecord` | `remark_record` | Remarks associated with notes and their creators |

Relationships use standard Eloquent **`hasMany`** and **`belongsTo`** associations (not polymorphic relationships):

- One user has many notes; each note belongs to one user.
- One note has many remarks; each remark belongs to one note.
- One user has many remarks; each remark belongs to one user.

Foreign keys are configured to cascade-delete related records where defined in the migrations.

## Local Installation (XAMPP / Windows)

### Requirements

- PHP 8.2 or later with extensions required by Laravel
- Composer
- XAMPP with MySQL or MariaDB enabled
- Node.js and npm (for frontend tooling)

### Setup

1. Clone the repository and enter the project directory:

   ```powershell
   git clone <repository-url>
   cd Simple-Note-Taking-Web
   ```

2. Install PHP dependencies:

   ```powershell
   composer install
   ```

3. Create a local environment file and application key:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

4. Create an empty database named `note` in phpMyAdmin or MySQL/MariaDB, then configure these values in `.env`:

   ```dotenv
   APP_ENV=local
   APP_DEBUG=true

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=note
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Adjust the database username and password to match your local setup. Do not commit `.env`.

5. Run database migrations:

   ```powershell
   php artisan migrate
   ```

6. Optional: seed local demonstration accounts:

   ```powershell
   php artisan db:seed
   ```

   **Development only:** The project's `UserSeeder` supplies sample users. Review or disable it before using a non-development environment; never run demo-user seeding against real user data.

7. Install frontend dependencies and build assets if needed:

   ```powershell
   npm install
   npm run build
   ```

8. Start the application:

   ```powershell
   php artisan serve
   ```

   Open the local URL displayed by Artisan (typically `http://127.0.0.1:8000`).

> Some frontend libraries are loaded from CDNs, so an internet connection may be needed for their styles or scripts to load.

## Automated Testing

The project includes Laravel Feature Tests for authentication, login rate limiting, Note CRUD and authorization, Remark CRUD and authorization, permission-check endpoints, validation, and cascade deletion.

**Important:** Use a separate database for testing. Do not run destructive tests against the development database (`note`).

1. Create an empty MySQL/MariaDB database named `note_testing`.
2. Create a local `.env.testing` based on `.env` and configure `APP_ENV=testing`, `DB_CONNECTION=mysql`, and `DB_DATABASE=note_testing` with the appropriate local database credentials. Keep this file private.
3. Check that `phpunit.xml` targets `note_testing` before running database tests.
4. Verify the test connection:

   ```powershell
   php artisan db:show --env=testing
   ```

5. Run the tests:

   ```powershell
   php artisan test
   ```

At the last locally reported full run, **63 tests passed with 267 assertions**. Test results can change as code and dependencies are updated.

## Relevant Project Files

```text
app/
  Http/Controllers/
    LoginController.php
    NoteController.php
    RemarkController.php
  Models/
    User.php
    NoteRecord.php
    RemarkRecord.php
  Http/Requests/
    CreateNoteRequest.php
    EditNoteRequest.php
resources/views/
  manage-login/
  manage-note/
  manage-remark/
database/
  migrations/
  seeders/
routes/web.php
tests/Feature/
```

## Security and Maintenance Notes

- Only note/remark creators may edit or delete their records; authorization is enforced by the backend.
- Validation rules apply to user-provided content and record identifiers.
- Keep `.env`, `.env.testing`, local logs, and credentials out of version control and shared archives.
- Dependency security advisories have been identified through Composer and npm audits. Review and remediate them before any public or production deployment; passing application tests does **not** mean dependencies are vulnerability-free.

## License

See the repository's license information, if provided.
