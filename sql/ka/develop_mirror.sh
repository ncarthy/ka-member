#!/bin/sh

#
# develop_mirror.sh
#
# 1. Pull latest source code from git repo into /var/www/knightsbdev
# 2. Update apache2 host config file and reload apache2
#

GIT=/usr/bin/git
CONFIG=config
DEST=/var/www/knightsbdev/ka
SSH_KEY=/root/aukw/kodi_rsa

eval `ssh-agent -s`
ssh-add ${SSH_KEY}
cd ${DEST}
${GIT} pull

find /etc/apache2/sites-available  -type f -print0 | xargs -0 sed -i 's/knightsb /knightsbdev /g'
systemctl reload apache2.service

