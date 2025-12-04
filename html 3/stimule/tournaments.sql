create table stimule.tournaments
(
    id              bigint unsigned auto_increment
        primary key,
    history_leaders json        null,
    status          varchar(15) null,
    end_at          timestamp   not null,
    created_at      timestamp   null,
    updated_at      timestamp   null
)
    collate = utf8mb4_unicode_ci;

