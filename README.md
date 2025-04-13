# Collection Manager

A robust PHP-based collection management system with barcode scanning capabilities, designed for tracking products in personal collections.

## Features

- Secure user authentication with registration and login functionality
- Real-time barcode scanning for quick product entry
- Comprehensive product management (add, view, edit, delete)
- RESTful API endpoints for seamless integration
- Responsive web interface for desktop and mobile access
- Data export capabilities for backup and analysis

## Technical Stack

- PHP 7.4+ for backend processing
- MySQL/MariaDB for reliable data storage
- HTML5/CSS3 for modern responsive interface
- JavaScript for client-side functionality
- Barcode.js for scanner integration

## Project Structure

```
collection_manager/
├── api/
│   ├── delete_product.php   # Handle product deletion
│   └── get_product.php      # Retrieve product information
├── config.php               # Database and application configuration
├── database.sql            # Database schema and initial data
├── index.php               # Main application interface
├── login.php               # User authentication
├── logout.php              # Session termination
├── register.php            # New user registration
├── LICENSE                 # Project license terms
└── README.md              # Project documentation
```

## Setup Instructions

1. Server Requirements:
   - PHP 7.4 or higher
   - MySQL/MariaDB database
   - Web server (Apache/Nginx)

2. Installation:
   - Clone the repository to your web server
   - Import `database.sql` to create the required schema
   - Copy `config.php.example` to `config.php` and update database credentials
   - Ensure proper file permissions are set

3. Configuration:
   - Adjust database connection parameters in `config.php`
   - Configure web server virtual host if needed
   - Set up SSL certificate for secure connections

## Usage Guide

1. User Management:
   - Register a new account via the registration page
   - Login using your credentials
   - Use "Forgot Password" if needed

2. Collection Management:
   - Scan products using device camera or barcode scanner
   - Manually enter product details when needed
   - View your complete collection in the dashboard
   - Edit or remove items as necessary
   - Export collection data for backup

3. API Integration:
   - Use `/api/get_product.php` to retrieve product details
   - Send DELETE requests to `/api/delete_product.php` to remove items
   - API documentation available in the docs folder

## Security Features

- Password hashing using modern algorithms
- Protection against SQL injection
- CSRF token validation
- Rate limiting on API endpoints
- Secure session management

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contributing

Contributions are welcome! Please feel free to:
- Report bugs and issues
- Suggest new features
- Submit pull requests
- Improve documentation

## Support

For support and questions:
- Create an issue in the GitHub repository
- Contact the maintainers directly
- Check the documentation for common solutions
