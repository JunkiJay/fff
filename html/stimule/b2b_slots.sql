create table stimule.b2b_slots
(
    id          bigint unsigned auto_increment
        primary key,
    `show`      int default 0 not null,
    gr_title    varchar(255)  not null,
    gr_id       int           not null,
    gm_is_board tinyint(1)    not null,
    gm_m_w      int           not null,
    gm_ln       int           not null,
    gm_is_copy  tinyint(1)    not null,
    gm_url      varchar(255)  not null,
    gm_is_retro tinyint(1)    not null,
    gm_bk_id    int           not null,
    gm_d_w      int           not null,
    icon_url    varchar(255)  not null,
    created_at  timestamp     null,
    updated_at  timestamp     null
)
    collate = utf8mb4_unicode_ci;

