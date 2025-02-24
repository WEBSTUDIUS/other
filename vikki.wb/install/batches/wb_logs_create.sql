CREATE TABLE IF NOT EXISTS `wb_api_logs`
(
    ID integer not null auto_increment primary key,
    ORDER_WB_ID varchar(255),
    CREATED_DATE datetime NOT NULL default CURRENT_TIMESTAMP,
    ORDER_BITRIX_ID integer,
    MESSAGE_TYPE integer,
    MESSAGE varchar(1000),
    RID varchar(100)
);