<?php

/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/

/**
 * Options avaiable in configuration file, tc.ini
 */
/**
 * Enable/disable html automatical refresh by using setTimeout in JS
 * Option for web page in client browser
 */
define("TC_DEFAULT_PAGE_REFRESH", TRUE);

/**
 * Enable/disable client registration requested by client
 */
define("TC_DEFAULT_CLIENT_OPEN_REGISTRATION", TRUE);
/**
 * Default page file size for client disk partition, in MiB
 */
define("TC_DEFAULT_CLIENT_PAGEFILE", 8192);
/**
 * Default max delay for client heartbeat, in second
 */
define("TC_DEFAULT_HEARTBEAT_TIMEOUT", 120);
define("TC_HEARTBEAT_TIMEOUT_MIN", 60);
define("TC_HEARTBEAT_TIMEOUT_MAX", 600);

define("TC_DEFAULT_CLIENT_DHCP_DNS", "");

define("TC_DEFAULT_GID_ROOT", 3);
define("TC_DEFAULT_UID_ROOT", 1);
define("TC_DEFAULT_GID_AUTOBOOT", 2);

define("TC_DEFAULT_CLIENT_NAMING", FALSE);
define("TC_DEFAULT_CLIENT_NAMING_PREFIX", "client");
define("TC_DEFAULT_CLIENT_NAMING_SUFFIX", "");
define("TC_DEFAULT_CLIENT_NAMING_WIDTH", 2);
define("TC_DEFAULT_CLIENT_NAMING_FIRST", 1);



/**
 * Default size list for personal storage, in MiB
 * The first value is the default selection
 */
define("TC_DEFAULT_PS_SIZES", "40960,20480,40960,61440,81920,122880,245760,327680,491520");
/**
 * Default cache size ratio for personal storage
 */
define("TC_DEFAULT_PS_CACHE_RATIO", 0.5);
/**
 * Default type list for the OS type of client image
 * The first value is the default selection
 */
define("TC_DEFAULT_OS_TYPES", "win7,win10,ubuntu,android");
/**
 * Default delay before auto login, shared with client
 */
define("TC_DEFAULT_CLIENT_AUTO_LOGIN_DELAY", 3);
/**
 * Internal options for web console
 */
/**
 * Global TC configuration file full path
 */
define("TC_CONF_PATH", "/opt/tci/etc/tc.ini");
/**
 * Global TC issue file full path
 */
define("TC_ISSUE_PATH", "/etc/tci/issue");
/**
 * file full path for DHCP static address assignment
 */
define("TC_DHCP_ETHERS", "etc/ethers");
/**
 * conf file for delivery tracker over WAN
 */
define("TC_TRACKER_WAN_PATH", "/opt/tci/etc/tcs-wan");
/**
 * Post installation check.
 */
define("TC_POST_INSTALLATION_CHECK_PATH", "/opt/tci/etc/done");
/**
 * file full path for DHCP static address assignment
 */
define("TC_COPY_IMAGE", false);
/*
 * Limit values
 */
define("TC_CLIENT_AUTO_LOGIN_DELAY_MAX", 10);
define("TC_CLIENT_AUTO_LOGIN_DELAY_MIN", 3);
define("TC_CLIENT_UPLOAD_KBS_MAX", 500000);
define("TC_CLIENT_UPLOAD_KBS_MIN", 0);
define("TC_CLIENT_DOWNLOAD_KBS_MAX", 500000);
define("TC_CLIENT_DOWNLOAD_KBS_MIN", 0);
define("TC_CLIENT_FIRMWARE_SIZE", 64*1024*1024);
/*
 * TC_CLIENT_NAME_MAX is also used for user name length, because auto boot
 * user name is the same as the client name
 */
define("TC_CLIENT_NAME_MAX", 30);
define("TC_CLIENT_MEMO_MAX", 40);
define("TC_CLIENT_NAMING_EX", "-_");
define("TC_CLIENT_NAMING_WIDTH_MIN", 1);
define("TC_CLIENT_NAMING_WIDTH_MAX", 6);
define("TC_CLIENT_NAMING_FIRST_MIN", 1);
define("TC_CLIENT_NAMING_FIRST_MAX", 999999);
define("TC_CLIENT_NAMING_PREFIX_SUFFIX_MAX", 12);

define("TC_USER_GROUP_NAME_MAX", 16);
define("TC_USER_GROUP_DESC_MAX", 40);

define("TC_ROLE_NAME_MAX", 16);
define("TC_ROLE_DESC_MAX", 40);

define("TC_BOOTIMAGE_NAME_MAX", 30);
define("TC_BOOTIMAGE_DESC_MAX", 40);

define("TC_DEFAULT_USER_BATCH_MAX", 200);
define("TC_CLIENT_GROUP_MAX", 10);
define("TC_CLIENT_GROUP_DESC_MAX", 100);

define("TC_TRACKER_WAN_PORT", 4630);
/**
 * Backup related default settings
 */
define("TC_DEFAULT_BACKUP_LOCAL_LOCATION", "/opt");
define("TC_DEFAULT_BACKUP_INTERVAL", 60);
define("TC_DEFAULT_BACKUP_INTERVAL_MIN", 1);
define("TC_DEFAULT_BACKUP_INTERVAL_MAX", 60*24*7);
define("TC_DEFAULT_BACKUP_HEADLESS", FALSE);

/**
 * Names and file locations
 */
define("TC_RP_LOADING", "loading");
define("TC_RP_SAVING", "saving");
define("TC_EXT_MERGING", ".merging");
define("TC_EXT_BACKUP", ".backup");
define("TC_LIC_LIC", "/etc/tci/license.xml");
define("TC_AUTOBOOT_SUFFIX", "-ab");
define("TC_BACKUP_STAT_PATH", "var/sb_stat");
define("TC_BACKUP_PID_PATH", "var/tc-ar.pid");
define("TC_CCONTROL_PID_PATH", "var/tc-ccontrol-server.pid");
define("TC_DHCP_UPLOAD_FILE", "var/network-upload.tar.tc");
define("TC_DHCP_LEASE_FILE", "etc/dnsmasq.leases");
define("TC_UPLOAD_PATH", "upload");
define("TC_SYNC_STAT_PATH", "var/sync_stat");
define("TC_CURRENT_SYNC_IMAGE_PEER", "var/sync_image_peer");
define("TC_RS_GAP_UFILES", "var/rs_gap_ufiles");
define("TC_EXPORT_CLIENT_IMAGE_ROOT", "/opt/lampp/htdocs/v1/ci");
define("TC_EXPORT_CLIENT_IMAGE_SITE", "/v1/ci");

define("TCI_CLIENT_IMAGE_HANDBOOK", "TCI_Client_Image_Handbook.html");
define("TCI_USER_MANUAL", "TCI_User_Manual.pdf");

define("TC_DEFAULT_SYSTEMD_ENV", "etc/tcs-env");
define("TC_SYSTEMD_BACKUP", "tcs-backup");
/**
 * Shared constant values with other TC services
 */
define("TC_CLIENT_IMAGE_READY", 0);
define("TC_CLIENT_IMAGE_PENDING", 1);
define("TC_CLIENT_IMAGE_MERGING", 2);

/**
 * background picture for preboot, 1024x768
 */
define("TC_PREBOOT_BACKGROUND", "etc/preboot_background.png");
/**
 * Internal passwords
 */
define("TC_AUTOBOOT_PWD", "380dd7b45840548fc037671cdec9ddd7");
define("TC_DHCP_SETTINGS_PWD", "tc-dhcp");
define("TC_FIRMWARE_PWD", "tc-firmware");

/**
 * Customized web console by user role when login
 */
define("TC_ENABLE_CUSTOMIZED_WEB_LOGIN", FALSE);

/**
 * AD domain server name max length
 */
define("TC_AD_DOMAIN_SERVER_NAME_MAX", 64);
define("TC_DEFAULT_AD_DOMAIN_NAME_RULE", "-_.");
/**
 * Enable/disable AD domain
 */
define("TC_DEFAULT_AD_DOMAIN_ENABLE", FALSE);

define("TC_DEFAULT_SSL_ENCRYPTION_ENABLE", FALSE);

?>
