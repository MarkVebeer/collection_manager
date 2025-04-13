# Collection Manager

A simple collection management tool designed for easy scanning and tracking of items, primarily focused on food products like Pringles, utilizing barcodes for convenience.

## Features

- User authentication system with registration and login functionality
- Barcode scanning support for quick product entry
- Product management (add, view, delete)
- API endpoints for product operations
- Web-based interface for easy access

## Technical Stack

- PHP for backend logic
- MySQL database for data storage
- HTML/CSS for frontend interface

## Project Structure

```
collection_manager/
├── api/
│   ├── delete_product.php
│   └── get_product.php
├── config.php
├── database.sql
├── index.php
├── login.php
├── logout.php
├── register.php
├── LICENSE
└── README.md
```

## Setup

1. Import the database schema using `database.sql`
2. Configure your database connection in `config.php`
3. Deploy the files to your web server
4. Access the application through your web browser

## Usage

1. Register for a new account or login with existing credentials
2. Use the main interface to scan or manually enter product information
3. View and manage your collection through the web interface
4. Logout when finished

## API Endpoints

The application provides two main API endpoints:
- `/api/get_product.php` - Retrieve product information
- `/api/delete_product.php` - Remove products from the collection

## License

This project is licensed under the terms included in the LICENSE file.

## Contributing

Feel free to submit issues and enhancement requests.
