#
# Table structure for table 'tx_dce_domain_model_dce'
#
CREATE TABLE tx_dce_domain_model_dce (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	type varchar(255) DEFAULT '0' NOT NULL,
	identifier text,
	fields text,

	wizard_enable varchar(255) DEFAULT '' NOT NULL,
	wizard_category varchar(255) DEFAULT '' NOT NULL,
	wizard_description text,
	wizard_icon varchar(255) DEFAULT '' NOT NULL,
	wizard_custom_icon varchar(255) DEFAULT '' NOT NULL,

	template_type varchar(255) DEFAULT '' NOT NULL,
	template_content text,
	template_file varchar(255) DEFAULT '' NOT NULL,

	cache_dce varchar(255) DEFAULT '' NOT NULL,
	show_access_tab varchar(255) DEFAULT '' NOT NULL,
	show_category_tab varchar(255) DEFAULT '' NOT NULL,
	hide_default_ce_wrap varchar(255) DEFAULT '' NOT NULL,
	preview_template_type varchar(255) DEFAULT '' NOT NULL,
	header_preview text,
	header_preview_template_file varchar(255) DEFAULT '' NOT NULL,
	bodytext_preview text,
	bodytext_preview_template_file varchar(255) DEFAULT '' NOT NULL,
	template_layout_root_path varchar(255) DEFAULT '' NOT NULL,
	template_partial_root_path varchar(255) DEFAULT '' NOT NULL,
	palette_fields text,

	enable_detailpage tinyint(4) unsigned DEFAULT '0' NOT NULL,
	detailpage_identifier varchar(255) DEFAULT '' NOT NULL,
	detailpage_template_type varchar(255) DEFAULT '' NOT NULL,
	detailpage_template text,
	detailpage_template_file varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tx_dce_domain_model_dcefield'
#
CREATE TABLE tx_dce_domain_model_dcefield (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	variable varchar(255) DEFAULT '' NOT NULL,
	type varchar(255) DEFAULT '' NOT NULL,
	configuration text,
	section_fields text,
	section_fields_tag varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tx_dce_dce_dcefield_mm'
#
CREATE TABLE tx_dce_dce_dcefield_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_dce_dcefield_sectionfields_mm'
#
CREATE TABLE tx_dce_dcefield_sectionfields_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_dce_dce int(11) DEFAULT '0' NOT NULL
);