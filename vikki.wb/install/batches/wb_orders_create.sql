CREATE TABLE IF NOT EXISTS `wb_api_orders`
(
    ID integer not null auto_increment primary key,
    ORDER_WB_ID varchar(255),
    ORDER_BITRIX_ID integer,
    CREATED_DATE datetime NOT NULL default CURRENT_TIMESTAMP,
    WAREHOUSE_ID integer,
    SKUS varchar(1000),
    STATUS varchar(100),
    PRICE varchar(100),
    NM_ID varchar(100),
    RID varchar(100)
);