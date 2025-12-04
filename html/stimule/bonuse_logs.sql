create table stimule.bonuse_logs
(
    id         bigint auto_increment
        primary key,
    user_id    int                                 not null,
    type       varchar(255)                        not null,
    size       double                              not null,
    meta       text                                null,
    created_at timestamp default CURRENT_TIMESTAMP not null,
    updated_at timestamp default CURRENT_TIMESTAMP not null
);

create index user_id
    on stimule.bonuse_logs (user_id);

