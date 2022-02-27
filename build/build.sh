#!/bin/sh -xe

set -eu

DATE=`date +%m-%d-%Y-%H-%M`
cd "${0%/*}/.."
composer install --no-interaction --no-progress --quiet
rm -rf sa_yaml/7/drupal sa_yaml/8/drupal

php application.php drupal-contrib-sa:download > /dev/null
php application.php drupal-contrib-sa:complete > /dev/null

# Do not commit deletions.
git diff --no-renames --name-only --diff-filter=D -z | xargs -0 git checkout --

# These have crap version names.
git checkout sa_yaml/8/drupal/svg_formatter/sa-contrib-2018-027.yaml
git checkout sa_yaml/8/drupal/domain_group/sa-contrib-2021-037.yaml

# This one is manually added as a core exclusion special case.
rm sa_yaml/8/drupal/quickedit/sa-contrib-2022-025.yaml
