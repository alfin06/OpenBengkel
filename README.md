# OpenBengkel

The Modern, Open-Source Management System for Auto Repair Shops.

OpenBengkel is a web-based application designed to help auto repair shops manage their day-to-day operations. It provides a simple and intuitive interface for managing customers, vehicles, work orders, invoices, and inventory.

## Features

*   **Customer Management:** Keep track of your customers and their contact information.
*   **Vehicle Management:** Manage the vehicles serviced in your shop, including make, model, and color.
*   **Work Order Management:** Create and manage work orders for each vehicle, including services and parts used.
*   **Invoice Management:** Generate invoices from work orders and track payments.
*   **Inventory Management:** Manage your parts inventory.
*   **Expense Tracking:** Keep track of your shop's expenses.
*   **Dashboard:** Get an overview of your shop's performance with charts and stats.

## Tech Stack

**Backend:**
*   PHP 8.3
*   Laravel 13
*   MySQL

**Frontend:**
*   Vue.js 3
*   Inertia.js
*   Vite
*   Tailwind CSS
*   Chart.js

## Installation

1.  Clone the repository:
    ```bash
    git clone https://github.com/your-username/OpenBengkel.git
    ```
2.  Navigate to the project directory:
    ```bash
    cd OpenBengkel
    ```
3.  Install PHP dependencies:
    ```bash
    composer install
    ```
4.  Install NPM dependencies:
    ```bash
    npm install
    ```
5.  Create a copy of your `.env` file:
    ```bash
    cp .env.example .env
    ```
6.  Generate an app encryption key:
    ```bash
    php artisan key:generate
    ```
7.  Configure your database in the `.env` file.
8.  Run the database migrations:
    ```bash
    php artisan migrate
    ```
9.  Run the database seeders (optional):
    ```bash
    php artisan db:seed
    ```
10. Run the development server:
    ```bash
    npm run dev
    ```
11. In a separate terminal, run the PHP server:
    ```bash
    php artisan serve
    ```

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

