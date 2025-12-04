create table stimule.promocode_activations
(
    id         bigint unsigned auto_increment
        primary key,
    user_id    bigint unsigned   not null,
    promo_id   bigint unsigned   not null,
    status     tinyint default 0 null,
    created_at timestamp         null,
    updated_at timestamp         null
)
    collate = utf8mb4_unicode_ci;

