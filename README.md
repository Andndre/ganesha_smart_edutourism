<div align="center">

# Ganesha Smart Edutourism

**Desa Wisata Penglipuran - Smart Sustainable Edutourism App**

<br>

![Laravel](https://img.shields.io/badge/Laravel-v13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-v4-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-v8.3-777BB4?style=flat-square&logo=php&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)

</div>

## Overview

Ganesha Smart Edutourism is a smart sustainable edutourism web application designed specifically for **Desa Wisata Penglipuran**. The application adopts a **"Super App / Modular Grid-Based Utility"** design philosophy (inspired by Grab/Gojek) to provide an outdoor mobile-first experience. It is built to be fast, neat, and highly functional, serving as a versatile tool rather than just a digital brochure.

## Features

- **Mobile-First Super App Interface**: Features a Bento-Grid menu for instant access to specific tools (Maps, UMKM, AR Camera).
- **AR Camera Module**: An integrated glassmorphism HUD that provides interactive experiences over the camera viewfinder.
- **Interactive Maps & Bottom Sheets**: Google Maps-style interaction pattern with progressive disclosure for points of interest.
- **High Accessibility & Anti-Fatigue UI**: Optimized for outdoor readability under direct sunlight, featuring a clean off-white background, high contrast typography, and minimum 44px tap targets.
- **Rich Digital Storytelling**: Uses TipTap editor for managing elegant cultural narrative descriptions.
- **Integrated Payments**: Support for transactions via Midtrans.

## Architecture & Technology Stack

- **Backend**: Laravel framework (v13) utilizing PHP 8.3/8.4
- **Frontend**: Vite, TailwindCSS (v4)
- **Database**: SQLite (default for development)
- **Other Integrations**:
  - Laravel IDE Helper
  - Pail & Pint for code formatting and debugging
  - Cloudflared tunnel for sharing local server
  - TipTap (ES Modules) for rich text editing

## Getting Started

### Prerequisites

Ensure you have the following installed:
- PHP >= 8.3
- Composer
- Node.js & npm
- SQLite extension enabled

### Installation

1. **Clone the repository:**

   ```bash
   git clone git@github.com:Andndre/ganesha_smart_edutourism.git
   cd ganesha_smart_edutourism
   ```

2. **Run setup script (via Composer):**

   This project includes a convenient setup script defined in `composer.json` which will install dependencies, create the environment file, generate the app key, run migrations, and build frontend assets.

   ```bash
   composer setup
   ```

### Development

To start the local development server (which concurrently runs Laravel server, queue listener, log tailing, and Vite dev server):

```bash
composer dev
```

### Testing

To run the test suite:

```bash
composer test
```

## Design Philosophy

The UI/UX is deeply rooted in efficiency and progressive disclosure. 

- **Color Palette**: 
  - **Penglipuran Green** (`#1E5128`) for primary CTAs.
  - **Bali Gold** (`#D4AF37`) for accents and premium labels.
- **Typography**: 
  - **Plus Jakarta Sans / Inter** for high legibility in small UI elements.
  - **Playfair Display** strictly for cultural storytelling and headlines.
- **Haptic Feedback**: Core actions trigger short vibrations to provide physical confirmation in potentially noisy outdoor environments.

For a complete breakdown of the design system, refer to the `DESIGN.md` file in the root directory.

## Contributing

Please review the existing code conventions. 
Code should be formatted using Laravel Pint before committing:
```bash
vendor/bin/pint --dirty --format agent
```

## License

This project is licensed under the MIT License.
