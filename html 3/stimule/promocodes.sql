create table stimule.promocodes
(
    id            bigint unsigned auto_increment
        primary key,
    name          varchar(255)               not null,
    sum           double(255, 2)             not null,
    activation    int                        not null,
    wager         double(16, 2) default 0.00 not null,
    type          varchar(50)                not null,
    end_time      timestamp                  null,
    quantity_spin int                        null,
    id_spin       int                        null,
    min_deposits  double                     null,
    deposits_days int                        null,
    created_at    timestamp                  null,
    updated_at    timestamp                  null
)
    collate = utf8mb4_unicode_ci;

