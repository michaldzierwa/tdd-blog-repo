## Installation Instructions

### 1. Navigate to Project Directory

In your terminal, navigate to the project directory:
```bash
cd app
```

### 2. Create Directories and Set Permissions

Create `var` and `vendor` directories with permissions set to 777:
```bash
mkdir var
mkdir vendor
chmod 777 var
chmod 777 vendor
```

### 3. Configure Database Connection

In the `.env` configuration file, update the `DATABASE_URL` line with your database credentials:
```
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
```
Replace `db_user`, `db_password`, and `db_name` with your actual database username, password, and database name.


### 4. Install Dependencies

Run the following command to install project dependencies using Composer:
```bash
composer install
```

### 5. Run Migrations and Load Data

Execute the following commands to migrate the database schema and load initial data:
```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 6. Access Project Homepage

Visit the project homepage at:
```
http://localhost/<installed project directory>/app/public/post
```
Replace `<installed project directory>` with the directory name where the project is installed.

### 7. Administrator Login Details

Use the following credentials to log in as an administrator:
- **Email:** admin0@example.com
- **Password:** admin1234

After logging in, you can update the credentials to your individual preferences.

### Enjoy using the application!