create table stimule.setting_tournaments
(
    id         bigint unsigned auto_increment
        primary key,
    days       int default 7 not null,
    places     text          null,
    created_at timestamp     null,
    updated_at timestamp     null
)
    collate = utf8mb4_unicode_ci;

