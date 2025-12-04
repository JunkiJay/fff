create table stimule.posts
(
    id         bigint unsigned auto_increment
        primary key,
    owner_id   varchar(50) null,
    post_id    varchar(50) null,
    created_at timestamp   null,
    updated_at timestamp   null
)
    collate = utf8mb4_unicode_ci;

