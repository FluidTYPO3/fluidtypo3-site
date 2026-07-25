CREATE TABLE tx_fluidtypo3org_search_index (
    uid int unsigned NOT NULL auto_increment,
    record_type varchar(32) DEFAULT '' NOT NULL,
    source_identifier varchar(255) DEFAULT '' NOT NULL,
    source_uid int unsigned DEFAULT 0 NOT NULL,
    route varchar(255) DEFAULT '' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,
    title_normalized varchar(255) DEFAULT '' NOT NULL,
    summary text NOT NULL,
    summary_normalized text NOT NULL,
    content_normalized mediumtext NOT NULL,
    tags varchar(2048) DEFAULT '' NOT NULL,
    extension_context varchar(1024) DEFAULT '' NOT NULL,
    feature_context varchar(1024) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    UNIQUE KEY source (record_type, source_identifier),
    KEY record_type (record_type),
    KEY source_uid (source_uid),
    KEY title_normalized (title_normalized(191))
);
