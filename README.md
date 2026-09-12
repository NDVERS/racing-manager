# 🏎️ Racing Manager &bull; Paddock OS

> **A High-Performance Motorsport Management Simulation & Live Pit-Wall Strategy Engine built with Laravel, Tailwind CSS, and Alpine.js.**

[![Tests](https://img.shields.io/badge/Tests-112%20Passed%20%7C%20687%20Assertions-brightgreen.svg?style=flat-square)](tests)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.5-blue.svg?style=flat-square)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg?style=flat-square)](https://laravel.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-v4.0-38bdf8.svg?style=flat-square)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-8.0-646cff.svg?style=flat-square)](https://vitejs.dev)
[![Code Style](https://img.shields.io/badge/Code%20Style-PSR--12%20(Pint)-success.svg?style=flat-square)](https://laravel.com/docs/pint)

---

## 📋 Executive Overview

**Racing Manager** is an immersive motorsport strategy game that puts you in the team principal seat of a Formula constructor. Oversee vehicle development, scout and negotiate driver contracts, secure multi-tier sponsorship packages, calibrate tactical race strategies (tire compounds & engine mappings), and compete in grand prix rounds across an international championship calendar.

---

## ⚡ Key Game Systems & Features

### 1. 🏁 Paddock Command Dashboard

- **Team HQ Telemetry**: Real-time financial treasury tracker, reputation level progress bar, and primary chassis/driver summary cards.
- **Onboarding Directives**: 4-step interactive onboarding task system with automatic +5,000 CR bonus payout upon completion.
- **Operations Manual**: Slide-based tutorial modal accessible anytime from anywhere in the paddock.

### 2. 🏎️ Garage & Fleet Engineering

- **Chassis Development**: Detailed car specifications across 5 performance pillars: **Speed (SPD)**, **Acceleration (ACC)**, **Handling (HND)**, **Braking (BRK)**, and **Reliability (REL)**.
- **Modular Upgrade Workshop**: Purchase component upgrades to level up vehicle stats with dynamic cost and stat scaling.
- **Active Chassis Designation**: Switch your designated primary car between different vehicle chassis in your garage.

### 3. 👤 Driver Market & Roster Management

- **Scouting & Attributes**: Driver telemetry covers **Pace**, **Cornering**, **Consistency**, **Overtaking**, **Defensive**, and **Racecraft**, calculating a unified **Overall Rating (OVR)**.
- **Contract Negotiations**: Hire promising rookies or seasoned veterans from the free-agent market.
- **Lineup Assignment**: Promote and designate your lead driver for the active Grand Prix season.

### 4. 🎯 Dynamic Race Tactics & Strategy Selector

- **Tire Compound Physical Models**:
  - 🔴 **Soft (C3)**: Maximum initial launch pace (**-0.85s/lap**); sharp degradation cliff after 35% distance with late-lap graining/lock-up risk.
  - 🟡 **Medium (C2)**: Balanced standard benchmark (**0.00s/lap**) with moderate linear degradation.
  - ⚪ **Hard (C1)**: Endurance compound (**+0.50s/lap** initial deficit); near-zero degradation for consistent lap-by-lap performance.
  - 🔵 **Wet Rain**: Deep water evacuation grooves. Mandatory in wet conditions; heavy drag/overheating penalty (**+4.50s/lap**) on dry asphalt.
- **ECU Engine Modes**:
  - ⚡ **Push (Aggressive)**: Extra power boost (**-0.45s/lap**) and overtake divebombs; accelerates tire wear (1.35x) and increases incident probability.
  - ⚙️ **Balanced (Standard)**: Manufacturer baseline settings with nominal fuel and component wear.
  - 🛡️ **Conserve (Defensive)**: Defensive track positioning (**+0.55s/lap**), reduced tire wear (0.70x), and enhanced mechanical safety buffer (+15% reliability).

### 5. ⏱️ Live Pit-Wall Telemetry Simulation Engine

- **Lap-by-Lap Progression**: Deterministic mathematical simulation factoring in composite vehicle/driver performance index, circuit layout characteristics (*High Speed*, *Technical*, *Balanced*), weather factors (*Dry*, *Wet*), driver consistency variance, and tire degradation curves.
- **Real-Time Classification Leaderboard**: 10-competitor grid displaying live positions, competitor tire/engine tactics, total elapsed time, gap intervals, and fastest laps.
- **Pit-Wall Radio & Commentary Feed**: Color-coded telemetry logs reacting to overtakes, position defenses, tire cliff warnings, and weather alerts.

### 6. 💼 Commercial Sponsorship Contracts

- **Tiered Partners**: Sign contracts across **Primary (Title)** and **Secondary (Technical)** sponsor tiers.
- **Signing Bonuses & Objectives**: Collect upfront capital on contract execution plus performance bonuses for hitting race objectives (*Finish Race*, *Finish Top 5*, *Finish Top 3*, *Fastest Lap*).
- **Contract Duration**: Tracks remaining races per contract with automatic expiration upon completion.

### 7. 🏆 Season Championship Standings

- **FIA World Constructors' Championship**: Cumulative constructor points across all season rounds.
- **FIA World Drivers' Championship**: Driver points table with race win count (P1), podiums (P1-P3), and fastest lap tally.
- **Interactive Standings UI**: Tabbed Alpine.js standings view with dedicated highlighting for the player's team and drivers.

### 8. 📊 Financial Ledger & Historic Race Archives

- **Double-Entry Ledger**: Full transaction history tracking entry fees, prize payouts, driver salaries, upgrade expenditures, and sponsor revenues.
- **Grand Prix Archives**: Historic race catalog detailing past classifications, fastest laps, and economic returns.

---

## 🛠️ Technology Stack

| Layer | Technology |
| :--- | :--- |
| **Backend Framework** | [Laravel 12.x](https://laravel.com) / PHP 8.2+ (PHP 8.5 Ready) |
| **Database & ORM** | MySQL / SQLite via Eloquent ORM with Atomic DB Transactions |
| **Frontend Styling** | [Tailwind CSS v4.0](https://tailwindcss.com) (Modern Dark Mode / Glassmorphism) |
| **Interactive UI** | [Alpine.js](https://alpinejs.dev) & Blade Components |
| **Build Tooling** | [Vite 8.0](https://vitejs.dev) |
| **Typography** | Plus Jakarta Sans & JetBrains Mono (Fonts Bunny) |
| **Testing Suite** | PHPUnit 11.x (112+ Feature & Unit Tests) |
| **Code Formatter** | Laravel Pint (PSR-12 Standard) |

---

## 🚀 Quickstart & Local Installation Guide

### Prerequisites

- **PHP** >= 8.2 (PHP 8.2, 8.3, 8.4, 8.5 supported)
- **Composer** >= 2.x
- **Node.js** >= 18.x & **npm**
- **MySQL** (or SQLite)

### Step-by-Step Setup

1. **Clone the Repository**

   ```bash
   git clone https://github.com/NDVERS/racing-manager.git
   cd racing-manager
   ```

2. **Install Backend Dependencies**

   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**

   ```bash
   npm install
   ```

4. **Configure Environment File**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   *Configure your database credentials in `.env` if using MySQL, or set `DB_CONNECTION=sqlite`.*

5. **Run Migrations & Seed Default Game Data**

   ```bash
   php artisan migrate --seed
   ```

   *Seeds default Grand Prix circuits, competitive chassis, driver market roster, and sponsor packages.*

6. **Build Frontend Assets**

   ```bash
   npm run build
   # or for live development:
   # npm run dev
   ```

7. **Launch Local Development Server**

   ```bash
   php artisan serve
   ```

   *Open [http://localhost:8000](http://localhost:8000) in your browser.*

---

## 🧪 Automated Testing & Code Quality

Run the complete test suite to verify all game modules:

```bash
# Run all 112+ feature & unit tests
php artisan test

# Run code style formatting check (Pint)
vendor/bin/pint --format agent
```

### Test Coverage Highlights

- `TeamOnboardingTest`: User registration, team creation, and starter kit validation.
- `CarUpgradeTest`: Chassis upgrade formulas, component attribute increments, and solvency checks.
- `DriverTest`: Driver recruitment, salary settlements, lead driver assignment, and ratings.
- `RaceSimulationTest`: Performance index calculation, track types, and grid classification.
- `RaceTacticsTest`: Tire compound degradation, engine ECU modes, weather penalties, and telemetry badges.
- `SponsorTest`: Multi-tier sponsor contracts, signing bonuses, and per-race objective settlements.
- `ChampionshipTest`: Constructors' and Drivers' championship standings calculations and tie-breaking.
- `RaceEconomySettlementTest`: Atomic double-entry financial ledger and transaction integrity.
- `EndToEndGameLoopTest`: Complete full player lifecycle journey from registration to championship history.

---

## ⚖️ Economy & Balancing Reference

| Action | Cost / Revenue | Notes |
| :--- | :--- | :--- |
| **Starter Team Budget** | `+50,000 CR` | Initial treasury provided on team registration |
| **Onboarding Directives Bonus** | `+5,000 CR` | Awarded upon completing 4 paddock setup tasks |
| **Grand Prix Entry Fees** | `1,000 - 2,500 CR` | Required upfront per race entry |
| **Race Prize Pools** | `15,000 - 40,000 CR` | P1: 40%, P2: 25%, P3: 15%, P4: 8%, P5: 5% |
| **Fastest Lap Bonus** | `+5% Prize Pool` | Extra cash + 5 reputation points |
| **Sponsor Signing Bonuses** | `10,000 - 60,000 CR` | Immediate capital injection upon signing |
| **Sponsor Objective Bonus** | `2,000 - 8,500 CR` | Paid per race when target objective is achieved |

---

## 📄 License

This project is open-source software licensed under the [MIT License](LICENSE).
