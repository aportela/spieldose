# spieldose

tl;dr **DEV VERSION**, _use at your own risk_

```
clone repository (https://github.com/aportela/spieldose.git)
install dependencies: composer install
create database: php tools/install-upgrade-db.php
scan music path: php tools/scan-music.php --path m:\SOUNDTRACKS
scrap (optional) artist/album data: php tools/scrap-music.php --all
create user (optional, can be done from web): php tools/set-credential.php --email foo@bar --password secret
start php web server instance: cd public && php -S 0.0.0.0:8080 cli-server.php
browse http://localhost:8080
```


## Web server configurations (according to [php slim docs](https://www.slimframework.com/docs/v3/start/web-servers.html)):

### WARNING: set user/group path permissions in database & log paths according with your web server process

#### nginx

```
server {
    # server listening port
    listen 80;
    # server full qualified domain name
    server_name www.mydomain.com;
    index index.php;
    # complete local path of spieldose repository
    root /var/www/nginx/spieldose/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_index index.php;
        # uncomment this (with your address/port settings) for using php fpm connection via tcp socket
        #fastcgi_pass 127.0.0.1:9000;
        # uncomment this (with your path) for using php fpm via unix socket
        fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
    }
}
```

#### apache

```
<VirtualHost *:80>
        ServerName www.mydomain.com

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/spieldose/

        <Directory /var/www/spieldose/>
                Options +Indexes +FollowSymLinks
                AllowOverride All
                Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/spieldose-error.log
        CustomLog ${APACHE_LOG_DIR}/spieldose-access.log combined
</VirtualHost>
```

## Screenshots (for the impatient)
### sign in
![Alt text](https://i.imgur.com/m2WyqH6l.png "signin")
### dashboard
![Alt text](https://i.imgur.com/fyMqFD1l.png "dashboard")
### browse artists
![Alt text](https://i.imgur.com/3zK5jiZl.jpg "browse artists")
### browse albums
![Alt text](https://i.imgur.com/TOIuMFNl.jpg "browse albums")
