create table stimule.vip_invites
(
    id          int auto_increment
        primary key,
    user_id     int       not null,
    invite_link text      not null,
    created_at  timestamp not null on update CURRENT_TIMESTAMP,
    used_at     timestamp not null,
    is_active   int       not null
);

