create table stimule.plinko_data
(
    id         bigint unsigned not null,
    data       json            not null,
    created_at timestamp       null,
    updated_at timestamp       null
)
    collate = utf8mb4_unicode_ci;

