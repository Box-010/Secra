# Before starting

No matter what you are going to develop this application or just use it, you need to follow the instructions below.

## Configuration

First, you need to write some config files in `config` folder. This folder should contain the following:

```php
<?php
/* config/database.php */
define('DB_HOST', /* Your database host */);
define('DB_PORT', /* Your database port */);
define('DB_ROOT_USER', /* Your database user who has all privileges */);
define('DB_ROOT_PASS', /* Your database root user's password */);
define('DB_USER', /* Your database user, which will be created automatically and then used by the application */);
define('DB_PASS', /* Your database user's password */);
define('DB_NAME', /* Your database name */);
```

```php
<?php
/* config/admin.php */
define('ADMIN_USERNAME', /* Your admin user username */);
define('ADMIN_PASSWORD', /* Your admin user password */);
define('ADMIN_EMAIL', /* Your admin user email */);
```

```php
<?php
/* config/website.php */
const PUBLIC_ROOT = "/"; // The root path which will be used to access the application in the browser, usually it is just '/' 
```

## Enabling mod_rewrite

You need also enable `mod_rewrite` in your Apache server. You can do this by running the following command:

```bash
sudo a2enmod rewrite
```

And then, you need to edit the file `/etc/apache2/apache2.conf` and change the following:

```apache
<Directory /var/www/>
    Options Indexes FollowSymLinks
    AllowOverride None
    Require all granted
</Directory>
```

to

```apache
<Directory /var/www/>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

## Setting up the server

You need to edit the file `/etc/apache2/sites-available/000-default.conf` to change the `DocumentRoot` to the path of
the `public` folder of the application. For example, if you have cloned the repository in `/var/www/html/secra`, you
need to change the following:

```apache
<VirtualHost *:80>
    # Other configurations...
    DocumentRoot /var/www/html
    # Rest of the configurations...
</VirtualHost>
```

to

```apache
<VirtualHost *:80>
    # Other configurations...
    DocumentRoot /var/www/html/secra/public
    # Rest of the configurations...
</VirtualHost>
```

And then restart your Apache server:

```bash
sudo service apache2 restart
```

## Installing the application

Now, you should access `http://\<your-server-ip\>/install/` to initialize the database and create the admin user.

After that, you can start enjoying the application!
