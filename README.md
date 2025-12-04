# Candidate Assessment - Laravel Project

A Laravel application for the Royal Apps candidate assessment. This application integrates with the Candidate Testing API to manage authors and books.

## Features

- **Authentication:** Login with API credentials, token stored in session with auto-refresh
- **Authors Management:**
  - View all authors in a paginated table
  - View single author with their books
  - Delete authors (only if they have no books)
- **Books Management:**
  - Add new books with author selection dropdown
  - Delete books from author detail page
- **User Profile:** Display logged-in user's name and logout functionality

## API Documentation

- **Swagger Documentation:** https://candidate-testing.com/docs
- **API Base URL:** https://candidate-testing.com
- **Test Credentials:**
  - Email: `ahsoka.tano@royal-apps.io`
  - Password: `Kryze4President`

## Local Setup

### Requirements

- PHP 8.2+
- Composer

### Installation

1. **Clone the repository:**
   ```bash
   git clone [your-repo-url]
   cd candidate-assessment
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Environment configuration:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run the application:**
   ```bash
   php artisan serve
   ```

5. **Access the application:**
   Open `http://localhost:8000` in your browser

## Heroku Deployment

### Deploy Steps

1. **Set buildpack:**
   ```bash
   heroku buildpacks:set heroku/php
   ```

2. **Set environment variables:**
   ```bash
   heroku config:set APP_KEY=$(php artisan key:generate --show)
   heroku config:set APP_ENV=production
   heroku config:set APP_DEBUG=false
   heroku config:set LOG_CHANNEL=errorlog
   heroku config:set SESSION_DRIVER=cookie
   heroku config:set CANDIDATE_API_BASE_URL=https://candidate-testing.com
   ```

3. **Deploy:**
   ```bash
   git push heroku main
   ```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php      # Login/Logout
│   │   ├── AuthorController.php    # Authors CRUD
│   │   └── BookController.php      # Books CRUD
│   └── Middleware/
│       └── EnsureApiToken.php      # Auth middleware
├── Services/
│   └── CandidateApiClient.php      # API client service
```

## Tech Stack

- **Framework:** Laravel 12
- **PHP:** 8.2+
- **Styling:** Tailwind CSS (CDN)
- **Session:** Cookie-based
