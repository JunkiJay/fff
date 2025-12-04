create table stimule.payments
(
    id            bigint unsigned auto_increment
        primary key,
    user_id       bigint unsigned            not null,
    sum           double(255, 2)             not null,
    bonus         double(16, 2) default 0.00 not null,
    wager         double(16, 2)              null,
    status        int           default 0    not null,
    `system`      varchar(20)                null,
    created_at    timestamp                  null,
    updated_at    timestamp                  null,
    merchant_meta varchar(50)                null,
    method        varchar(16)                null
)
    collate = utf8mb4_unicode_ci;

create index created_at
    on stimule.payments (created_at);

create index status
    on stimule.payments (status);

create index user_id
    on stimule.payments (user_id);

