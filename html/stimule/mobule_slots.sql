create table stimule.mobule_slots
(
    id                    bigint unsigned auto_increment
        primary key,
    `show`                int default 0 not null,
    alias                 varchar(255)  not null,
    group_alias           varchar(255)  not null,
    title                 varchar(255)  not null,
    provider              varchar(255)  not null,
    is_enabled            tinyint(1)    not null,
    is_freerounds_enabled tinyint(1)    not null,
    desktop_enabled       tinyint(1)    not null,
    mobile_enabled        tinyint(1)    not null,
    base_total_bet        int           not null,
    max_bet_level         int           not null,
    created_at            timestamp     null,
    updated_at            timestamp     null
)
    collate = utf8mb4_unicode_ci;

