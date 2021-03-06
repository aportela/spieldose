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

TODO: access through sub-folder

If you want to use a virtual host, the configuration would be the following:

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

If you want to access through a sub-folder of the server (example http://www.mydomain.com/spieldose) you do not have to do anything. Just unzip the package in the webserver root path folder, ex: /var/www/spieldose)

If you want to use a virtual host, the configuration would be the following:

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
![Alt text](https://i.imgur.com/jG4FxfJ.png "signin")
### dashboard
![Alt text](https://i.imgur.com/hpJKF91.png "dashboard")
### search
![Alt text](https://i.imgur.com/tLfXN74.png "search")
### browse artists
![Alt text](https://i.imgur.com/HuOn7W5.jpg "browse artists")
### artist resume
![Alt text](https://i.imgur.com/db3uoIm.png "artist resume")
### artist bio
![Alt text](https://i.imgur.com/yzqXQOq.jpg "artist bio")
### artist tracks
![Alt text](https://i.imgur.com/PAe7hRO.png "artist tracks")
### artist albums
![Alt text](https://i.imgur.com/lCCk888.jpg "artist albums")
### browse albums
![Alt text](https://i.imgur.com/QjrftdQ.jpg "browse albums")
### browse playlists
![Alt text](https://i.imgur.com/FsghrUc.png "browse playlists")
