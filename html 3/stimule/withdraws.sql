create table stimule.withdraws
(
    id          bigint unsigned auto_increment
        primary key,
    user_id     bigint unsigned            not null,
    sum         double(255, 2)             not null,
    sumWithCom  double(16, 2) default 0.00 not null,
    wallet      varchar(255)               not null,
    `system`    varchar(20)                not null,
    reason      varchar(500)               null,
    status      int           default 0    not null,
    fake        int           default 0    not null,
    created_at  timestamp                  null,
    updated_at  timestamp                  null,
    is_youtuber int           default 0    not null,
    method      varchar(16)                null
)
    collate = utf8mb4_unicode_ci;

