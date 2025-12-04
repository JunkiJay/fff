create table stimule.telegram_bindings
(
    id         int auto_increment
        primary key,
    user_id    bigint      not null,
    code       varchar(64) not null,
    expires_at timestamp   not null on update CURRENT_TIMESTAMP,
    created_at timestamp   not null on update CURRENT_TIMESTAMP,
    updated_at timestamp   not null
);

