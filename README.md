Node.js backend for the Service Catalog SPA

This repository contains a small, dependency-free Node.js server that serves the static SPA files and provides simple JSON API endpoints for demo data.

Quick start

1. Make sure you have Node.js installed (v12+ recommended).
2. From the repository root run:

   npm start

3. Open http://localhost:3000 in your browser. The server exposes API endpoints:

   - GET /api/services         -> { services: [...] }
   - POST /api/bookings        -> accepts JSON { serviceId, date, time, stylist } and returns created booking
   - GET /api/appointments     -> { appointments: [...] } (currently returns bookings created in-memory)

Notes

- Data is in-memory for demo purposes and will reset when the server restarts.
- To wire the frontend to the backend, update `app.js` to fetch from `/api/services` instead of using embedded mock data.
- If you want persistence, I can add a JSON file-based store or integrate a lightweight DB.
