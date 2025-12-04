create table stimule.cashback
(
    id         int                                 not null,
    user_id    int                                 not null,
    amount     double                              not null,
    created_at timestamp                           null,
    updated_at timestamp default CURRENT_TIMESTAMP not null
);

