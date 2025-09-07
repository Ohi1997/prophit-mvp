# Prophit MVP

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-%3E=8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

Prophit MVP is a web application that tracks significant movements in Polymarket prediction markets. It answers one simple question: **"When prediction market odds change significantly, what news caused it?"**

This application monitors active Polymarket markets, stores their probability and volume data over time, and presents a clean, functional interface to surface the market movements that matter.

**Main Feed showing significant market movements:**
![Main Feed](https://github.com/user-attachments/assets/58c17c4e-7585-4415-9d58-f8ac7523f4e2)

**Detailed Market View with historical probability chart:**
![Chart View](https://github.com/user-attachments/assets/7881ae6d-9c52-42bf-a80d-69eb231df895)

**Visual indicator for new updates:**
![New Update Indicator](https://github.com/user-attachments/assets/03a8cb59-1584-4a9b-ab7e-d96f2c5528c9)

**Main Feed When there is no market movement:**
![No Market Movement Detected View](https://github.com/user-attachments/assets/84a3ec99-dc6e-4fd4-8e07-b706f232ddef)

---

## Core Features

- **Automated Data Collection:** A schedulable Artisan command connects to the Polymarket API to fetch and store market data periodically.
- **Significant Movement Feed:** The homepage displays a real-time list of markets that have moved 10% or more in the last 24 hours, sorted by the largest change.
- **Detailed Market View:** Clicking on any market opens a detailed view with a historical line chart visualizing its probability over the last 24 hours.
- **Real-time Updates:** The feed automatically checks for new movements every two minutes, with a non-intrusive visual indicator to alert the user.
- **User-Focused UX:** Includes a manual refresh button, clean loading states, and a responsive, dark-mode interface for a modern feel.

---

## Technical Stack

- **Backend:** Laravel 10 (PHP 8.2)
- **Database:** MySQL
- **Frontend:** Vanilla JavaScript (ES6+), Blade Templates
- **Charting:** ApexCharts.js
- **Styling:** Tailwind CSS (via CDN)

---

## Setup and Installation

Follow these steps to get the Prophit MVP running on your local machine.

### Prerequisites

- PHP >= 8.2  
- Composer  
- Node.js & NPM  
- A local MySQL database server  

### 1. Clone the Repository

```bash
git clone https://github.com/Ohi1997/prophit-mvp.git
cd prophit-mvp
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Your Environment

Copy the example environment file:

```bash
cp .env.example .env
```

Generate a unique application key:

```bash
php artisan key:generate
```

### 4. Update the `.env` File

Configure your database connection:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prophit_mvp
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

Add your Polymarket API credentials:

```dotenv
POLYMARKET_PRIVATE_KEY=0x722f422496e325891e4de731658599a4104c077f391955b7dba8d1c7e3bd5f0e
POLYMARKET_API_KEY=ca0fcaaf-9e67-9157-7a18-ce0e4af92087
POLYMARKET_SECRET=0MCCXAhEa1K2YWtqLqdDZA2PAtTAhrpLQZ7xl2dcdWw=
POLYMARKET_PASSPHRASE=cf3baf5742705e9eab8056aea6a49d39f94fe0a723581a1dee0fcb2d8b2ac096
```

### 5. Run Database Migrations

```bash
php artisan migrate
```

---

## Running the Application

The application requires two processes: the **data collector** and the **web server**.

### 1. Start the Data Collector

Run the Artisan command to fetch data from the Polymarket API:

```bash
php artisan polymarket:fetch-data
```

⚠️ You need at least two snapshots for each market to calculate movements. Run this command a few times, waiting a couple of minutes between runs, to populate the database.

### 2. Start the Web Server

```bash
php artisan serve
```

### 3. Visit the App

Go to [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

---

## Architecture Overview

### Backend (Laravel)
- Standard **MVC pattern**.
- `PolymarketService` handles API communication.
- `FetchPolymarketData` Artisan command polls data every 5 minutes (configured in `routes/console.php`).

### API
- `GET /api/movements`: Returns markets with significant (>10%) price changes.  
- `GET /api/markets/{market_id}`: Returns the 24-hour price history for a single market.  

### Frontend
- Blade templates define HTML structure.  
- Vanilla JavaScript handles API calls, DOM manipulation, and rendering.  
- ApexCharts.js for charts.  

---

## Design Decisions & Trade-offs

- **Vanilla JS instead of React/Vue:** Lightweight, no build step, faster to develop for MVP scope.  
- **On-the-fly movement calculation:** Percentage changes are computed at request time, avoiding redundancy.  
- **Service class for API interaction:** Follows **Single Responsibility Principle**, making the code modular and maintainable.

## Important Note for Testing

- **Volume Threshold for Data Collection:**  
  The collector only saves markets with a trading volume of **$1,000+** to reduce noise and focus on significant markets.  

- **Empty Database on First Run:**  
  When you run `php artisan polymarket:fetch-data` for the first time, your tables may stay empty if no markets currently meet this threshold.  

- **Populating with Sample Data (for UI/Charts):**  
  To ensure your UI displays data during local testing:  
  1. Open: `app/Console/Commands/FetchPolymarketData.php`  
  2. Change the filter line from:  
     ```php
     if ($volume < 1000)
     ```  
     to:  
     ```php
     if ($volume < 0)
     ```  
  3. Run the fetch command 2–3 times (waiting a minute between runs) to build up historical entries.  

- **Restore After Testing:**  
  Don’t forget to revert the filter back to `$volume < 1000` before deploying to ensure production data integrity.
