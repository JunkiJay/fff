create table stimule.slot_sessions
(
    id         bigint auto_increment
        primary key,
    user_id    int       not null,
    game_id    bigint    not null,
    created_at timestamp not null on update CURRENT_TIMESTAMP,
    updated_at timestamp not null on update CURRENT_TIMESTAMP
);

create index user_id
    on stimule.slot_sessions (user_id);

