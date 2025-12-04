create table stimule.mines
(
    id         int auto_increment
        primary key,
    user_id    int           not null,
    amount     float         not null,
    bombs      int           not null,
    step       int default 0 not null,
    grid       json          null,
    status     int default 0 not null,
    fake       int default 0 not null,
    created_at timestamp     null,
    updated_at timestamp     null
)
    charset = latin1;

create index status
    on stimule.mines (status);

create index user_id
    on stimule.mines (user_id);

