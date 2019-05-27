#!/bin/sh -xe

composer install --no-interaction --no-progress
rm -rf sa_yaml/7/drupal sa_yaml/8/drupal

php application.php drupal-contrib-sa:download
php application.php drupal-contrib-sa:complete

if [ ! -z "$(git status --porcelain)" ]
then
  echo "things"
fi
