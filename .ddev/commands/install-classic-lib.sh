#!/bin/bash

set -euo pipefail

set_typo3_setting() {
    local config_file="$1"
    local setting_path="$2"
    local json_value="$3"

    php -r '
    $configFile = $argv[1];
    $settingPath = $argv[2];
    $jsonValue = $argv[3];
    $config = include $configFile;
    $value = json_decode($jsonValue, true, 512, JSON_THROW_ON_ERROR);
    $segments = explode("/", $settingPath);
    $lastIndex = count($segments) - 1;
    $reference = &$config;
    foreach ($segments as $index => $segment) {
        if ($index < $lastIndex && (!isset($reference[$segment]) || !is_array($reference[$segment]))) {
            $reference[$segment] = [];
        }
        $reference = &$reference[$segment];
    }
    $reference = $value;
    file_put_contents($configFile, "<?php\nreturn " . var_export($config, true) . ";\n");
    ' "${config_file}" "${setting_path}" "${json_value}"
}

install_classic_instance() {
    local version="$1"
    local download_channel="$2"
    local db_name="$3"
    local site_url="$4"

    local instance_root="/var/www/html/${version}"
    local config_file="${instance_root}/typo3conf/system/settings.php"
    local headers_file
    local archive_file
    local resolved_version
    local cli_path

    rm -rf "${instance_root:?}/"*
    mkdir -p "${instance_root}"

    headers_file="$(mktemp)"
    archive_file="$(mktemp --suffix=.tar.gz)"
    trap 'rm -f "${headers_file}" "${archive_file}"' RETURN

    curl -fsSL -D "${headers_file}" -o "${archive_file}" "https://get.typo3.org/${download_channel}"
    resolved_version="$(sed -nE 's#.*typo3_src-([0-9]+\.[0-9]+\.[0-9]+)\.tar\.gz.*#\1#p' "${headers_file}" | tail -n 1)"

    if [ -z "${resolved_version}" ]; then
        echo "Unable to resolve TYPO3 version for ${download_channel}" >&2
        exit 1
    fi

    tar -xzf "${archive_file}" -C "${instance_root}"

    mkdir -p "${instance_root}/fileadmin" "${instance_root}/typo3conf/ext" "${instance_root}/typo3temp"
    ln -s "typo3_src-${resolved_version}" "${instance_root}/typo3_src"
    ln -s "typo3_src/index.php" "${instance_root}/index.php"
    ln -s "typo3_src/typo3" "${instance_root}/typo3"
    ln -s "typo3_src/vendor" "${instance_root}/vendor"
    ln -s /var/www/dce "${instance_root}/typo3conf/ext/dce"
    touch "${instance_root}/FIRST_INSTALL"

    mysql -h db -u root -p"root" -e "DROP DATABASE IF EXISTS \`${db_name}\`; CREATE DATABASE \`${db_name}\`;"

    cli_path="${instance_root}/typo3/sysext/core/bin/typo3"
    "${cli_path}" setup -n --force --dbname="${db_name}" --password="${TYPO3_DB_PASSWORD}" --create-site="${site_url}" --admin-user-password="${TYPO3_SETUP_ADMIN_PASSWORD}"

    "${cli_path}" extension:activate dce
    "${cli_path}" extension:setup

    set_typo3_setting "${config_file}" 'BE/debug' 'true'
    set_typo3_setting "${config_file}" 'FE/debug' 'true'
    set_typo3_setting "${config_file}" 'SYS/devIPmask' '"*"'
    set_typo3_setting "${config_file}" 'SYS/displayErrors' '1'
    set_typo3_setting "${config_file}" 'SYS/trustedHostsPattern' '".*.*"'
    set_typo3_setting "${config_file}" 'MAIL/transport' '"smtp"'
    set_typo3_setting "${config_file}" 'MAIL/transport_smtp_server' '"localhost:1025"'
    set_typo3_setting "${config_file}" 'MAIL/defaultMailFromAddress' '"admin@example.com"'
    set_typo3_setting "${config_file}" 'GFX/processor' '"ImageMagick"'
    set_typo3_setting "${config_file}" 'GFX/processor_path' '"/usr/bin/"'
    set_typo3_setting "${config_file}" 'LOG/TYPO3/CMS/deprecations/writerConfiguration/notice/TYPO3\CMS\Core\Log\Writer\FileWriter/disabled' 'false'

    "${cli_path}" cache:flush
}
