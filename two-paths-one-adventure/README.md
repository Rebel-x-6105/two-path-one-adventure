# Two Paths, One Adventure

A responsive PHP and MySQL choice game that creates a personalized outing plan from five playful decisions.

## Included

- Responsive welcome, choice, summary, and acceptance screens
- Bootstrap 5.3.8 layout support
- Light and dark themes saved on the device
- Five database-managed questions with two options each
- Server-side session, choice, summary, and response storage
- Installable web app manifest and service worker
- Offline fallback page
- Optional ambient sound made directly in the browser
- Share button and celebration animation
- Prepared statements and server-side input validation

## Requirements

- PHP 8.1 or newer
- MySQL 8 or MariaDB 10.5 or newer
- Apache through XAMPP, WAMP, MAMP, Laragon, or a similar local server
- HTTPS in production for installation and service worker support
  - `localhost` works during local development

## XAMPP Setup

1. Copy the `two-paths-one-adventure` folder into:

   `C:\xampp\htdocs\`

2. Start **Apache** and **MySQL** in XAMPP.

3. Open phpMyAdmin:

   `http://localhost/phpmyadmin`

4. Import:

   `database/doctor_project.sql`

5. Check the database settings in:

   `api/config.php`

   Default XAMPP values are already set:

   - Database: `doctor_project`
   - User: `root`
   - Password: empty

6. Open the project:

   `http://localhost/two-paths-one-adventure/`

## Database Tables

Every table starts with `doc_proj_`:

- `doc_proj_questions`
- `doc_proj_options`
- `doc_proj_adventures`
- `doc_proj_adventure_choices`
- `doc_proj_invitation_responses`

## Production Notes

- Serve the project through HTTPS.
- Change the database credentials in `api/config.php` or use these environment variables:
  - `TPOA_DB_HOST`
  - `TPOA_DB_PORT`
  - `TPOA_DB_NAME`
  - `TPOA_DB_USER`
  - `TPOA_DB_PASS`
- Keep PHP error display disabled in production.
- Update the cache version in `service-worker.js` after changing cached files.
- The Bootstrap files are loaded from jsDelivr. The custom interface still supplies the full visual design.

## Customize the Choices

Edit the seeded rows in `database/doctor_project.sql`, or update these tables through phpMyAdmin:

- `doc_proj_questions`
- `doc_proj_options`

The `summary_fragment` field controls how each option appears in the final sentence.
