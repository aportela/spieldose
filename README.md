# spieldose

tl;dr **DEV VERSION**, _use at your own risk_

```
clone repository (https://github.com/aportela/spieldose.git)
install dependencies: composer install
create database: php tools/install-upgrade-db.php
scan music path: php tools/scan-music.php --path m:\SOUNDTRACKS
scrap (optional) artist/album data: php tools/scrap-music.php --all
create user (optional, can be done from web): php tools/set-credential.php --email foo@bar --password secret
start php web server instance: php -S 0.0.0.0:8080 -t public
browse http://localhost:8080
```

KNOWN BUG (using embedded php web server) with routes matching names with ".", ex: /artist/The.Band (https://github.com/slimphp/Slim/issues/359) -> always return "not found" (404)

## Screenshots (for the impatient)
### sign in
![Alt text](https://i.imgur.com/m2WyqH6l.png "signin")
### dashboard
![Alt text](https://i.imgur.com/fyMqFD1l.png "dashboard")
### browse artists
![Alt text](https://i.imgur.com/3zK5jiZl.jpg "browse artists")
### browse albums
![Alt text](https://i.imgur.com/TOIuMFNl.jpg "browse albums")
