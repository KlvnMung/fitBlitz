# FitBlitz Application Setup Instructions

## **Overview**
FitBlitz is a fitness and nutrition tracking application that includes features such as public and private messaging, activity tracking, and access to fitness and nutrition articles. This guide provides step-by-step instructions to set up the application, from configuring the environment to creating an admin user and uploading API keys.

---

## **1. Prerequisites**
Before starting, ensure you have the following installed on your system:
- **Web Server**: [AMPPS](https://ampps.com/), [XAMPP](https://www.apachefriends.org/), or any LAMP/WAMP stack.
- **PHP**: Version 8.2 or higher.
- **MySQL Database**: A running MySQL server.
- **Composer**: Installed for dependency management.
- **Browser**: A modern web browser for testing.

---

## **2. Clone the Repository**
1. Clone the FitBlitz project repository to your local machine:
   ```bash
   git clone https://github.com/your-repo/fitblitz.git
   ```
2. Navigate to the project directory:
   ```bash
   cd fitblitz
   ```

## **3. Install Dependencies**
Install PHP dependencies using Composer:
```bash
composer install
```

## **4. Configure the .env File**
1. Create a .env file in the root directory of the project:
   ```bash
   cp .env.example .env
   ```
2. Open the .env file and configure the following environment variables:
   ```env
   DB_HOST=127.0.0.1
   DB_NAME=fitblitz
   DB_USER=root
   DB_PASS=your_password

   NEWS_API_KEY=your_news_api_key
   OPENAI_API_KEY=your_openai_api_key
   ```
   Replace:
   - `your_password` with your MySQL root password.
   - `your_news_api_key` with your News API key (from newsapi.org).
   - `your_openai_api_key` with your OpenAI API key (from OpenAI).

## **5. Set Up the Database**
1. Create the Database: Log in to MySQL and create the fitblitz database:
   ```sql
   CREATE DATABASE fitblitz;
   ```
2. Import the Schema: Import the database schema using the setup.php file:

3. Place the project folder in your web server's root directory:
   - For AMPPS: `C:\Program Files\Ampps\www\fitblitz`
   - For XAMPP: `C:\xampp\htdocs\fitblitz`

4. Open your browser and navigate to:
   ```url
   http://localhost/fitblitz/setup.php
   ```
   If there is no output, the database tables have been created successfully.

5. Verify Tables: Ensure the following tables exist in the fitblitz database:
   - `users`
   - `friends`
   - `messages`
   - `food_products`
   - `articles`
   - `profiles`
   - `activities`

## **6. Create an Admin User**
1. Log in to MySQL and insert an admin user into the users table:
   ```sql
   INSERT INTO users (username, password, role) VALUES ('admin', 'your_password', 'admin');
   ```
   Replace `your_password` with a secure password for the admin user.

2. Verify the admin user:
   ```sql
   SELECT * FROM users WHERE username = 'admin';
   ```

## **7. Upload API Keys**
1. News API Key:
   - Sign up at newsapi.org and generate an API key.
   - Add the key to the .env file under `NEWS_API_KEY`.

2. OpenAI API Key:
   - Sign up at OpenAI and generate an API key.
   - Add the key to the .env file under `OPENAI_API_KEY`.

3. Save the .env file after adding the keys.

## **8. Start the Application**
1. Start your web server and MySQL services.
2. Open your browser and navigate to:
   ```url
   http://localhost/fitblitz
   ```

## **9. Test the Application**
1. Log In:
   - Use the admin credentials (admin and the password you set) to log in.

2. Test Features:
   - Navigate through the application to test features like:
     - Public messages.
     - Private messages.
     - Fitness and nutrition articles.
     - Activity tracking.

## **10. Troubleshooting**
1. No Output on setup.php
   - Ensure the .env file is correctly configured with database credentials.
   - Verify that the MySQL server is running.

2. Database Connection Issues
   - Check the `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` values in the .env file.
   - Test the connection using a MySQL client.

3. API Key Errors
   - Ensure the API keys in the .env file are valid and have sufficient quota.

## **11. Future Enhancements**
- Add real-time updates for messages using WebSockets.
- Implement user profile pictures for a more personalized experience.
- Add pagination for long lists of messages or articles.

## **Contact**
For questions or support, contact the project maintainer:
- Email: support@fitblitz.com
- GitHub: FitBlitz Repository

This setup guide ensures that the FitBlitz application is configured correctly and ready to use.