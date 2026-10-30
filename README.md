# Blood Bond — Blood Donation Management System

Blood Bond is a PHP and MySQL web application for donor registration, donor search, appointment booking, blood inventory viewing, and administrator management.

> **Important:** This is a portfolio/demo project. Do not use it with real donor or patient data until you have added a privacy policy, consent workflow, access controls, monitoring, and clinical/operational approval.

## Features

- Donor registration and search by blood group and location
- Account registration and login
- Donor dashboard and appointment booking
- Automatic standard appointment slots for each selected future date
- Public blood inventory view
- Admin dashboard for donors, appointments, inventory, and CSV exports
- English/Hindi interface and dark mode

## Technology

- HTML, CSS, JavaScript
- PHP 8+
- MySQL / MariaDB
- Apache (XAMPP for local development)

## Run locally with XAMPP

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Create a database named `donorsdatabase`, select it in phpMyAdmin, then import `sql/schema.sql`.
3. Open `http://localhost/Blood%20Donation%20Website/`.

## Deploy to a PHP + MySQL host

1. Create a MySQL database and user at your hosting provider.
2. Import `sql/schema.sql` into that database.
3. Upload this project to the web root (`public_html`, `htdocs`, or the directory required by your provider).
4. Copy `includes/config.local.php.example` to `includes/config.local.php`.
5. Put your live database credentials and website URL in `config.local.php`.
6. Enable HTTPS in the host control panel.
7. Create a normal user through the website, then promote that user in phpMyAdmin:

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your-admin-email@example.com';
   ```

## Security note

`includes/config.local.php` is excluded from Git. Never commit database passwords, SMTP passwords, API keys, or real donor information.

## License

For educational and portfolio use.
