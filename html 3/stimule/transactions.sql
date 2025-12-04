create table stimule.transactions
(
    transactionID    bigint auto_increment
        primary key,
    user_id          int                                 not null,
    amount           double                              not null,
    refTransactionID text                                null,
    roundID          text                                not null,
    gameID           int                                 not null,
    created_at       timestamp default CURRENT_TIMESTAMP not null,
    updated_at       timestamp                           null
);

