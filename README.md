##### Missão Roberto - API

#### Run through Local Environment:

## 1. Prerequisites
  Ensure the following are installed on your operational system:

# PHP
- **Check if PHP is installed:**
  ```bash
  php -v
  ```
- **Install PHP (if not installed):**
  - **Windows:** Download from [php.net](https://www.php.net/downloads) or use XAMPP/WAMP.
  - **macOS (Homebrew):**
    ```bash
    brew install php
    ```
  - **Linux (Ubuntu/Debian):**
    ```bash
    sudo apt update
    sudo apt install php php-mysql
    ```

# Composer
- **Install Composer (if not installed):**
  ```bash
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
  ```
- **Check if Composer is installed:**
  ```bash
  composer --version
  ```
- **Windows:**
  https://getcomposer.org/download/
  

# MySQL
Ensure MySQL is installed and running:
- **Check MySQL status (Linux/macOS):**
  ```bash
  sudo service mysql status
  ```
- **Start MySQL (if needed):**
  ```bash
  sudo service mysql start
  ```
- **Login to MySQL to verify credentials:**
  ```bash
  mysql -u root -p
  ```

---

## 2. Setup Project Dependencies

  Navigate to your project root where `composer.json` is located and install dependencies:

  ```bash
  composer install
  ```

---

## 3. Configure Environment Variables

  Ensure there is a `.env` file in the project root.

  **Example `.env` content:**
  ```ini
  APP_ENV=development
  PORT=8000

  DB_HOST=localhost
  DB_PORT=3306
  DB_NAME=test
  DB_USER=root
  DB_PASSWORD=root
  ```

---

## 4. Run the PHP Built-in Server

  ### Run the PHP server:
  ```bash
  php -S localhost:8000 -t src/Infrastructure/Api
  ```

  You can now access the application from a client at localhost:8000, where you should see the message "Application is Running." from a json payload.
---

## 5. (Alternative) Run the Script Directly

  You can also run the `index.php` directly from the command line:

  ```bash
  php src/Infrastructure/Api/index.php
  ```

---

## 6. Enable Extensions
  This system uses `mbstring`, `pdo_mysql`. 
  Ensure the all of them are enabled in your `php.ini`. 

  ```ini
    extension=mbstring
    extension=pdo_mysql
  ```

  Optional: You can find where it's installed by running the following command:
  ```bash
    php --ini
  ```
  And then check out what value (or path) belongs to "Loaded Configuration File:" prefix.


## Tests

# All Tests
  To run all tests at once, u should open the API's server:
  ```bash
  php -S localhost:8000 -t src/Infrastructure/Api
  ```
  And then, run the test trough phpunit library, in another terminal:
  
  ```bash
  php vendor/bin/phpunit
  ```

## End-to-End (e2e) Tests
  - **Location:** `src/Infrastructure/Api/_test/ApiE2eTest.php`
  - **Purpose:** Validates full system interactions.
  - **Dependencies:** 
   - Requires API to be running.
   - Requires to change `APP_ENV` to `test` in `.env`. Reason: API has a dbconfig routine that ran before e2e test.
  - **Run Commands (each in a separated terminal):**
    ```sh
    php -S localhost:8000 -t src/Infrastructure/Api
    ```

    ```sh
    php vendor/bin/phpunit --filter ApiE2eTest
    ```

## Unit Tests

### Customer Service Unit Test
  - **Location:** `src/Application/Customer/_tests/CustomerServiceUnitTest.php`
  - **Purpose:** Tests `CustomerService` in isolation, mocking dependencies.
  - **Run Command:**
    ```bash
    php vendor/bin/phpunit src/Application/Customer/_tests/CustomerServiceUnitTest.php
    ```

### Address Service Unit Test
  - **Location:** `src/Application/Address/_tests/AddressSerivceUnitTest.php`
  - **Purpose:** Tests `AddressService` logic independently.
  - **Run Command:**
    ```bash
    php vendor/bin/phpunit src/Application/Address/_tests/AddressSerivceUnitTest.php
    ```

---------

#### Run through Docker Environment

- Follow this documentation to install docker and docker compose into your SO: 
  - [https://docker.com/](https://docs.docker.com/get-started/),
  - [https://docs.docker.com/compose/install/](https://docs.docker.com/compose/install/)
  and then:

### 1. Configure your `.env` file

  - Create an `.env` file to set up the database credentials.

  ```bash
  cp .env.example .env
  ```

  Edit `.env` file with the appropriate database values:

  ```ini
  APP_ENV=development
  PORT=8000

  DB_HOST=localhost
  DB_PORT=3306
  DB_NAME=test
  DB_USER=root
  DB_PASSWORD=root
  ```

### 2. Build And Start the containers

  ```bash
  docker-compose up --build
  ```

  This will start the following services:
  - **php**: Runs the application with Apache and PHP
  - **mysql**: Runs MySQL database
  - **phpunit**: Runs PHPUnit tests (for unit, integration, and E2E testing)

### 4. Tests

  Run the unit and integration tests with the following command steps:
  
  - Get into the container and open bash (considering the suggested container name. You can check out it's name running `docker ps` command)
    ```sh
      docker exec teste-tecnico-roberto-server-php-1 bash
    ```
  - Inside Bash, access html nginx application directory:
    ```bash
    cd html
    ```
  - Now run the tests through our phpunit:
    ```bash
    ./vendor/bin/phpunit
    ```
  
  - Or even use this shortcut:
  ```bash
    docker exec <image_name> sh -c "cd html && ./vendor/bin/phpunit"
  ```

  This command above will execute all tests, including E2E tests.


### Logs
  This application has logging enabled. Feel free to check it out at: `/storage/system-api.log`
