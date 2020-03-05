#!/bin/sh -xe

set -eu

DATE=`date +%m-%d-%Y-%H-%M`
cd "${0%/*}/.."
composer install --no-interaction --no-progress --quiet
rm -rf sa_yaml/7/drupal sa_yaml/8/drupal

php application.php drupal-contrib-sa:download > /dev/null
php application.php drupal-contrib-sa:complete > /dev/null

# This will checkout all deleted files, but not untracked.
git checkout sa_yaml/ --quiet

if [ ! -z "$(git status --porcelain)" ]
then
  git add sa_yaml
  git checkout -b autoupdate/$DATE
  git config --global user.email "committer@example.com"
  git config --global user.name "Auto Commit"
  git commit -m "Drupal Contrib SA $DATE"
  git remote add github "https://$GITHUB_ACTOR:$GITHUB_TOKEN@github.com/$GITHUB_REPOSITORY.git"
  git push github autoupdate/$DATE
fi
