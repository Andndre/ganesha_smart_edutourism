# **Smart Sustainable Edutourism - Development Plan & Execution Strategy**

## **1. Project Overview & Current Status**

**Project Name:** Smart Sustainable Edutourism Desa Wisata Penglipuran (PWA & WebAR)  
**Current Status (As of Phase 1):** - Database migrations and schema are **COMPLETED**.

- Base Layouts (app.blade.php, auth.blade.php), Super App Bottom Navigation, and app.css are **COMPLETED**.
- UI Design for Home (home.blade.php), Login, Register, and PWA Offline (offline.html) are **COMPLETED** and adhere to the "Grab-style / Modular Grid-Based Utility" design system.
- PWA Service Worker (sw.js) basic setup is **COMPLETED**.

**Development Paradigm for AI Assistant:**

- **Strictly** follow the Tailwind 4.0 configuration established in app.css. Do not invent new colors.
- Always use 100dvh for full-screen modules and tap-target (min 44px) for interactive elements.
- Never use @vite inside offline.html.
- Do not modify existing layouts (app or auth) unless explicitly instructed.

## **2. Tech Stack Summary**

| Component | Technology                           |
| :-------- | :----------------------------------- |
| Framework | Laravel 11.x (PHP 8.3)               |
| Frontend  | Blade + Vanilla JS + Tailwind 4.0    |
| PWA       | Manual Service Worker (sw.js)        |
| AR Engine | AR.js / A-Frame (Marker-based WebAR) |
| Maps      | Leaflet.js (Mobile-optimized)        |
| Database  | SQLite (Dev) / MySQL (Prod)          |

## **3. Revised Development Phases (Actionable for AI)**

_Note: Phase 1 (Foundation) is completed. We are now executing from Phase 2 onwards. The AI must build UI components first, mock the data, and then wire it to the controllers._

### **Phase 2: Core Utility Modules (Map, Catalog & Events)**

**Goal:** Build the essential features tourists will click from the Home Grid.

- **Task 2.1: Interactive Digital Map (explore.blade.php)**
    - Implement Leaflet.js full-screen map (h-[calc(100dvh-4rem)]).
    - Create custom HTML markers for Cultural Objects and UMKM.
    - **[NEW]** Integrate "Smart Edu-Tourism Route", "Emergency SOS Routes", and "Accessibility" map filters.
    - **[NEW]** Add a "Heatmap Toggle" for tourists to check real-time crowd density.
    - Build a "Bottom Sheet" component that slides up when a marker is tapped (no page reloads for details).
- **Task 2.2: UMKM Catalog (umkm.blade.php & product-detail.blade.php)**
    - Build a two-column grid layout for products.
    - Create a clean product detail page with a sticky "Beli Sekarang" button at the bottom.
- **Task 2.3: Cultural Objects List (cultural-objects.blade.php)**
    - Build a list view of heritage sites using large image cards.
    - Implement "Digital Storytelling" layout (clean typography, _Playfair Display_ for headers).
- **Task 2.4: Event & Cultural Calendar (events.blade.php) [NEW]**
    - Build a timeline or calendar view for upcoming village events and cultural ceremonies.

### **Phase 3: The AR Engine & Edutourism (Critical)**

**Goal:** Implement the core educational technology.

- **Task 3.1: AR Camera Wrapper (ar-scan.blade.php)**
    - Build the camera UI overlay (Glassmorphism HUD).
    - Ensure the camera view takes 100dvh and sits _behind_ the UI.
    - Implement a "Target Reticle" (kotak pembidik) in the center.
- **Task 3.2: AR.js Integration (JavaScript)**
    - Set up A-Frame scene and Hiro/Custom marker tracking.
    - Handle loading states (Skeleton or 3D placeholder) while .glb models download.

- **Task 3.4: Educational Pocket Book (learning.blade.php)**
    - Create a "Duolingo-style" progress UI for reading cultural histories.

### **Phase 4: Transaction, Ticketing & Post-Visit**

**Goal:** Handle the business flow (Booking, Payment, and Feedback).

- **Task 4.1: Tour Packages & Reservation Form**
    - Build the UI for selecting dates (Horizontal scroll chips) and party size.
    - Create the checkout summary page.
- **Task 4.2: E-Ticket & Profile (profile.blade.php)**
    - Design the user profile screen showing active e-tickets.
    - Implement a large, scannable QR Code UI for ticket gates.
- **Task 4.3: Feedback & Satisfaction Form (feedback.blade.php) [NEW]**
    - Create a post-visit survey/form with a 5-star rating system to collect user satisfaction.

### **Phase 5: Admin & Analytics (Backend Focus)**

**Goal:** Command Center for Village Managers.

- **Task 5.1: Admin Dashboard Layout (layouts/admin.blade.php)**
    - Build a desktop-optimized sidebar layout (different from the mobile app).
- **Task 5.2: Analytics UI**
    - Create layout for crowd density tracking and UMKM sales charts.
    - Implement CRUD interfaces for managing AR markers, Tour Routes, Events, and products.
    - **[NEW]** Add "Tourist Capacity Warning System" UI for real-time monitoring.

## **4. Frontend Component Guidelines (For AI Prompting)**

When generating Blade views, the AI **must** use these patterns:  
**1. Floating Bottom Sheet (For Map/AR Details):**

<div class="fixed inset-x-0 bottom-0 z-50 transform transition-transform translate-y-full bg-white rounded-t-3xl shadow-[0_-8px_30px_rgba(0,0,0,0.12)]">  
   <!-- Drag Handle -->  
   <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mt-3 mb-5"></div>  
   <!-- Content -->  
</div>

**2. Standard Page Header (Non-Home):**

<header class="pt-sat sticky top-0 z-40 bg-surface/90 backdrop-blur-md px-4">  
    <div class="flex h-14 items-center gap-3">  
        <button onclick="history.back()" class="tap-target -ml-2 text-gray-500">  
           <!-- Back Icon SVG -->  
        </button>  
        <h1 class="text-lg font-bold text-charcoal truncate">Page Title</h1>  
    </div>  
</header>

**3. Cards (UMKM/Packages):**

- Always use bg-white, rounded-2xl, border border-gray-100, and shadow-sm.
- Ensure tap targets (<a> or <button>) have active:scale-[0.98] transition-all.

## **5. Next Immediate Action for AI**

The developer (User) has completed Phase 1.  
**Next Prompt to AI:** "Begin Phase 2. Build the explore.blade.php (Interactive Digital Map) view. Focus only on the HTML/Tailwind layout first. Create a full-screen map container, a floating search bar at the top, and an empty hidden Bottom Sheet component at the bottom for location details."
