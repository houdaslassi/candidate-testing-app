# Candidate Assessment - Laravel Project

This is a Laravel application for the Royal Apps candidate assessment.

## API Documentation

- **Swagger Documentation:** https://candidate-testing.api.royal-apps.io/docs
- **API Base URL:** https://candidate-testing.api.royal-apps.io
- **Credentials:**
  - Email: `ahsoka.tano@royal-apps.io`
  - Password: `Kryze4President`

## Setup Instructions

1. **Install Dependencies:**
   ```bash
   composer install
   ```

2. **Environment Configuration:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup:**
   ```bash
   php artisan migrate
   ```

4. **Run the Development Server:**
   ```bash
   php artisan serve
   ```

   The application will be available at `http://localhost:8000`

## Project Structure

This project will be developed step by step with incremental commits:

1. **Step 1:** API Client Setup & Authentication
2. **Step 2:** Login Page & Token Storage
3. **Step 3:** Authors Management (List, View, Delete)
4. **Step 4:** Books Management (Add, Delete)
5. **Step 5:** User Profile Display & Logout

## Requirements

- PHP 8.0+
- Composer
- SQLite (default) or MySQL
