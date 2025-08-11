# Book System Management (PHP + MySQL)

## Quick Start

1. Create the database and tables:
   - Open phpMyAdmin or MySQL CLI
   - Run `schema.sql` (adjust the initial admin later).
2. Update DB settings in `config.php`.
3. Put the project inside your server root (e.g., `htdocs/book-system`).
4. Open `http://localhost/book-system/public/` to see the login.
5. Login using: `admin@example.com` / `admin123`, then go to **Users** and change the password (it will re-hash using PHP).

## Roles

- **Admin**: Full access (manage users, CRUD books, delete, etc.)
- **Editor**: Add/edit books, cannot delete books or manage users
- **Viewer**: Read-only access

## Features

- CRUD for books with: Title, Author, ISBN, Year, Language, Genre, Tags, Description
- Powerful filters: text search, by language, genre, year range, pagination
- Exports: CSV, Excel (.xlsx), PDF (client-side via jsPDF & SheetJS)
- UI Languages: English, Kurdish (Sorani), Arabic, Persian, Turkish (easy to extend)
- RTL layout auto-switch for RTL languages
- Dark Mode toggle (persisted in localStorage)
- Mobile-first, responsive layout

## Extending

- Add more UI strings in `i18n.php`
- Add more book languages in `dashboard.php` and `book_form.php`
- Add genres in DB table `genres`

## Backups

- Use phpMyAdmin to export `books` and `genres` regularly.

## Security Notes

- Uses prepared statements and session-based auth.
- For production, set proper session cookies and HTTPS.
