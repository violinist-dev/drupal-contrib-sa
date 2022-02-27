#!/bin/sh -xe

set -eu

DATE=`date +%m-%d-%Y-%H-%M`
cd "${0%/*}/.."
composer install --no-interaction --no-progress --quiet
rm -rf sa_yaml/7/drupal sa_yaml/8/drupal

php application.php drupal-contrib-sa:download > /dev/null
php application.php drupal-contrib-sa:complete > /dev/null
