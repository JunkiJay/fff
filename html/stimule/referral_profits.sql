create table stimule.referral_profits
(
    id         bigint unsigned auto_increment
        primary key,
    from_id    int                        not null,
    ref_id     int                        not null,
    amount     double(16, 2) default 0.00 not null,
    level      int                        not null,
    created_at timestamp                  null,
    updated_at timestamp                  null
)
    collate = utf8mb4_unicode_ci;

