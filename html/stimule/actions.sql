create table stimule.actions
(
    id            int auto_increment
        primary key,
    user_id       bigint                              not null,
    action        varchar(255)                        not null,
    balanceBefore double(16, 2)                       not null,
    balanceAfter  double(16, 2)                       not null,
    created_at    timestamp default CURRENT_TIMESTAMP not null,
    updated_at    timestamp                           null
)
    charset = utf8mb3;

create index action
    on stimule.actions (action);

create index created_at
    on stimule.actions (created_at);

create index user_id
    on stimule.actions (user_id);

