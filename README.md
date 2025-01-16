# My Friend System

This repository contains a social networking application called **My Friend System**, developed using PHP, MySQL, and HTML/CSS. The system supports user sign-ups, logins, friend management, and database interaction.

## Features

### Part 1: Home Page and Database Setup
- **Home Page (`index.php`)**:
  - Displays user information and links to core system pages (Sign-Up, Log-In, About).
  - Checks and creates required MySQL tables (`friends` and `myfriends`) if not already present.
  - Populates tables with sample records for testing.

### Part 2: User Registration and Login
- **Sign-Up Page (`signup.php`)**:
  - Form to register new users.
  - Validates email, profile name, and passwords before saving to the database.
  - Sets up user sessions on successful registration.

- **Login Page (`login.php`)**:
  - Validates user credentials.
  - Redirects to the friend list on successful login.

### Part 3: User Interaction
- **Friend List Page (`friendlist.php`)**:
  - Displays a list of the user's current friends.
  - Allows users to unfriend others, updating the database accordingly.

- **Add Friend Page (`friendadd.php`)**:
  - Lists registered users who are not already friends.
  - Allows adding new friends, updating friend counts dynamically.

- **Logout Page (`logout.php`)**:
  - Clears user session and redirects to the Home page.

### Part 4: Extra Features
- **Pagination for Add Friend Page**:
  - Displays users in batches of 10, with options to navigate between pages.

- **Mutual Friend Count**:
  - Shows the number of mutual friends for each user listed on the Add Friend page.

### Part 5: About Page (`about.php`)
- Summarises the implementation, additional features, and challenges faced.
- Includes navigation links to key pages.

## File Structure

```
project/
├── index.php               # Home page and database setup
├── signup.php              # User registration page
├── login.php               # User login page
├── friendlist.php          # List of friends
├── friendadd.php           # Add new friends
├── logout.php              # Logout functionality
├── about.php               # About page
├── functions/              # Optional folder for reusable PHP functions
├── images/                 # Optional folder for images
├── style.css               # CSS styles
└── style/                  # Optional folder for stylesheet images
```

## Installation and Testing

1. Set up a PHP-enabled server with MySQL support.
2. Create a database named `s<104225166>_db`.
3. Upload project files to the server.
4. Test the application by accessing the Home page (`index.php`).

## Requirements

- Use relative paths for all file references to ensure portability.
- Ensure MySQL database credentials are correctly configured.
- Test the application to confirm full functionality.

## License

This project is released under the MIT License. Feel free to use, modify, and distribute.
