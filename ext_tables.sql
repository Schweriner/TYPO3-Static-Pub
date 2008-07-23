#
# Table structure for table 'tx_realurl_pathcache'
#
CREATE TABLE tx_staticpub_pages (
  filepath_hash int(11) DEFAULT '0' NOT NULL,
  page_id int(11) DEFAULT '0' NOT NULL,
  filepath text NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,

  PRIMARY KEY (filepath_hash),
  KEY page_id (page_id)
);
