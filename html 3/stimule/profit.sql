create table stimule.profit
(
    id           bigint unsigned auto_increment
        primary key,
    bank_dice    double(16, 2) default 0.00 not null,
    bank_mines   double(16, 2) default 0.00 not null,
    bank_bubbles double(16, 2) default 0.00 not null,
    bank_wheel   double(16, 2) default 0.00 not null,
    bank_plinko  float                      not null,
    earn_bubbles double(16, 2) default 0.00 not null,
    comission    int           default 0    not null,
    earn_dice    double(16, 2) default 0.00 not null,
    earn_mines   double(16, 2) default 0.00 not null,
    earn_plinko  float                      not null,
    created_at   timestamp                  null,
    updated_at   timestamp                  null
)
    collate = utf8mb4_unicode_ci;

create index bank_dice
    on stimule.profit (bank_dice, bank_mines, bank_bubbles, bank_wheel, bank_plinko);

