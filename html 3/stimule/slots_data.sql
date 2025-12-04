create table stimule.slots_data
(
    id            int auto_increment
        primary key,
    user_id       int                                 not null,
    slot_id       int                                 not null,
    trx_id        bigint                              not null,
    type          varchar(55)                         null,
    amount        int                                 not null,
    balanceBefore double                              null,
    balanceAfter  double                              null,
    created_at    timestamp default CURRENT_TIMESTAMP not null,
    updated_at    timestamp                           null
)
    charset = utf8mb3;

