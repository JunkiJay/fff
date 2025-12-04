create table stimule.bonus_levels
(
    id         bigint unsigned auto_increment
        primary key,
    title      varchar(50)                null,
    goal       int                        null,
    reward     double(16, 2) default 0.00 not null,
    background varchar(50)                null,
    created_at timestamp                  null,
    updated_at timestamp                  null
)
    collate = utf8mb4_unicode_ci;

