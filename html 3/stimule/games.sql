create table stimule.games
(
    id         bigint unsigned auto_increment
        primary key,
    user_id    bigint unsigned not null,
    game       varchar(255)    not null,
    bet        double(255, 2)  not null,
    chance     double(255, 2)  not null,
    win        double(255, 2)  not null,
    type       varchar(255)    null,
    fake       int default 0   not null,
    created_at timestamp       null,
    updated_at timestamp       null
)
    collate = utf8mb4_unicode_ci;

