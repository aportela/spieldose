<?php

return (array(
    1 => array(
        '
            CREATE TABLE `USER` (
                `id` VARCHAR(36) NOT NULL,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password_hash` VARCHAR(60) NOT NULL,
                PRIMARY KEY (`id`)
            );
        '
    )
));
