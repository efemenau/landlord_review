To set up a local development environment on your Mac, follow these steps:

1. **Install Homebrew**: Homebrew is a package manager for macOS that simplifies the installation of software. Open Terminal and run:

   ```bash
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
   ```


2. **Install Apache, MySQL, PHP, and phpMyAdmin**:

   ```bash
   brew install httpd mysql php phpmyadmin
   ```


3. **Start Apache and MySQL Services**:

   ```bash
   brew services start httpd
   brew services start mysql
   ```


4. **Secure MySQL Installation**: Run the following command and follow the prompts to set a root password and secure your MySQL installation:

   ```bash
   mysql_secure_installation
   ```


5. **Configure Apache to Use PHP**:

   - Open Apache's configuration file:

     ```bash
     sudo nano /opt/homebrew/etc/httpd/httpd.conf
     ```

   - Find and uncomment the following lines (remove the `#` at the beginning):

     ```
     LoadModule php_module /opt/homebrew/opt/php/lib/httpd/modules/libphp.so
     AddHandler php-script .php
     ```

   - Ensure the `DocumentRoot` is set to your desired project directory, e.g.:

     ```
     DocumentRoot "/Users/your_username/Sites"
     ```

   - Save and exit (`CTRL+O`, `Enter`, `CTRL+X`).

6. **Restart Apache**:

   ```bash
   brew services restart httpd
   ```


7. **Install Git** (if not already installed):

   ```bash
   brew install git
   ```


8. **Clone Your GitHub Repository**:

   - Navigate to your desired directory:

     ```bash
     cd /Users/your_username/Sites
     ```

   - Clone the repository:

     ```bash
     git clone https://github.com/efemenau/landlord_review.git
     ```

9. **Set Up phpMyAdmin**:

   - Create a symbolic link to phpMyAdmin in your DocumentRoot:

     ```bash
     ln -s /opt/homebrew/share/phpmyadmin /Users/your_username/Sites/phpmyadmin
     ```

   - Restart Apache:

     ```bash
     brew services restart httpd
     ```

   - Access phpMyAdmin by navigating to `http://localhost/phpmyadmin` in your browser.

10. **Create the Database and Import Tables**:

    - Log in to phpMyAdmin using the root credentials you set earlier.

    - Create a new database for your project.

    - Import the SQL file from your cloned repository to set up the necessary tables.

11. **Configure Environment Variables**:

    - In your project directory, duplicate the sample environment file and name it `.env`:

      ```bash
      cp .env.example .env
      ```

    - Open the `.env` file and set the database name, username, and password to match your MySQL credentials.

12. **Access Your Localhost**:

    - Ensure Apache and MySQL services are running:

      ```bash
      brew services list
      ```

    - Navigate to `http://localhost/your_project_directory` in your browser to view your project.
