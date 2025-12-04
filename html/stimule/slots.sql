create table stimule.slots
(
    id         bigint unsigned auto_increment
        primary key,
    title      text      not null,
    provider   text      not null,
    image      text      not null,
    alias      text      not null,
    priority   int       null,
    `show`     int       null,
    created_at timestamp null,
    updated_at timestamp null
)
    collate = utf8mb4_unicode_ci;

