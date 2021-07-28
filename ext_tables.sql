#
# Table structure for table 'tx_dce_domain_model_dce'
#
CREATE TABLE tx_dce_domain_model_dce (

	identifier varchar(255) DEFAULT '' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
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
	show_media_tab varchar(255) DEFAULT '' NOT NULL,
	show_category_tab varchar(255) DEFAULT '' NOT NULL,
	hide_default_ce_wrap varchar(255) DEFAULT '' NOT NULL,
	flexform_label varchar(255) DEFAULT 'LLL:EXT:dce/Resources/Private/Language/locallang_db.xlf:tx_dce_domain_model_dce.flexformLabel.default' NOT NULL,
	direct_output varchar(255) DEFAULT '1' NOT NULL,

	use_simple_backend_view tinyint(4) unsigned DEFAULT '0' NOT NULL,
	backend_view_header varchar(255) DEFAULT '*dcetitle' NOT NULL,
	backend_view_header_expression text,
    backend_view_header_use_expression tinyint(4) unsigned DEFAULT '0' NOT NULL,
	backend_view_bodytext text,

	backend_template_type varchar(255) DEFAULT '' NOT NULL,
	backend_template_content text,
	backend_template_file varchar(255) DEFAULT '' NOT NULL,

	template_layout_root_path varchar(255) DEFAULT '' NOT NULL,
	template_partial_root_path varchar(255) DEFAULT '' NOT NULL,
	palette_fields text,
	prevent_header_copy_suffix tinyint(4) unsigned DEFAULT '1' NOT NULL,

	enable_detailpage tinyint(4) unsigned DEFAULT '0' NOT NULL,
	detailpage_identifier varchar(255) DEFAULT '' NOT NULL,
	detailpage_slug_expression text,
	detailpage_title_expression text,
	detailpage_use_slug_as_title varchar(255) DEFAULT '' NOT NULL,
	detailpage_template_type varchar(255) DEFAULT '' NOT NULL,
	detailpage_template text,
	detailpage_template_file varchar(255) DEFAULT '' NOT NULL,

	enable_container tinyint(4) unsigned DEFAULT '0' NOT NULL,
	container_item_limit int(11) DEFAULT '0' NOT NULL,
    container_detail_autohide tinyint(4) unsigned DEFAULT '1' NOT NULL,
	container_template_type varchar(255) DEFAULT '' NOT NULL,
	container_template text,
	container_template_file varchar(255) DEFAULT '' NOT NULL

);

#
# Table structure for table 'tx_dce_domain_model_dcefield'
#
CREATE TABLE tx_dce_domain_model_dcefield (

	title varchar(255) DEFAULT '' NOT NULL,
	variable varchar(255) DEFAULT '' NOT NULL,
	type varchar(255) DEFAULT '0' NOT NULL,
	configuration text,
	map_to varchar(255) DEFAULT '' NOT NULL,
	new_tca_field_name varchar(255) DEFAULT '' NOT NULL,
	new_tca_field_type varchar(255) DEFAULT '' NOT NULL,
	section_fields text,
	section_fields_tag varchar(255) DEFAULT '' NOT NULL,
	parent_dce int(11) DEFAULT '0' NOT NULL,
	parent_field int(11) DEFAULT '0' NOT NULL,

    KEY parent_dce (parent_dce)

);

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (

	tx_dce_dce int(11) DEFAULT '0' NOT NULL,
	tx_dce_index mediumtext,
	tx_dce_slug varchar(255) DEFAULT '' NOT NULL,
	tx_dce_new_container tinyint(4) unsigned DEFAULT '0' NOT NULL

);
