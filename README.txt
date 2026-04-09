E-Ration Service Portal Setup Instructions

1. Set up Database:
   - Ensure you have a MySQL server running (e.g., standalone MySQL).
   - Create a database called `erationportal`.
   - Import the `database/erationportal.sql` file into your database to create the necessary tables and sample data.

2. Start the Server:
   - Open a terminal or command prompt in this project folder (`erationportal`).
   - Run the following command to start PHP's built-in development server:
     `php -S localhost:8000`

3. Access the Application:
   - Open your web browser.
   - Go to: http://localhost:8000/
   - This will open the citizen-facing public live dashboard.

4. Test the Roles:

   Shopkeeper / Admin:
   - Login URL: http://localhost:8000/shopkeeper/login.php
   - Username: admin
   - Password: admin123
   - (Here you can open/close the shop, update stock, and view complaints).

   Citizen:
   - Login URL: http://localhost:8000/citizen/login.php
   - Try registering a new citizen using any 10-16 char Ration Card Number and 10 digit mobile.
   - Or use a sample account:
     - RC No: RC1234567890
     - Password: password
   - (Here you can simulate "Collect Ration" using the fingerprint button when the shop is OPEN).
