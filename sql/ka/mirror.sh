#!/bin/bash
#
# mirror.sh
#
# Copy the KA website and mirror it on a Linux vm. Used to debug production
# or test new code. If developing the website instead then use the other
# mirror script which pulls from Git repo. If only need update of member
# database then use member_mirror.sh
#
# This script obliterates all existing data on this server in 
# MariaDB and /var/www/knightsb
#
# 1. Download three SQL database backup files: Roundcube, CMS and Member
# 2. Download the email alias files for .co.uk, .com, .org.uk etc.
# 3. Using rsync update the /var/www/knightsb so that it matches 
#    public_html on NetNerd
# 4. Overwrite existing databases with new data
#

CURL="/usr/bin/curl -s"

SITE_URI="https://uk1.cp.netnerd.com:2083/cpsess4562723289"
SITE_USER=knightsb
SITE_PWORD=XGy5PgHvXm
DB_RC=knightsb_Roundcube
DB_CMS=knightsb_cms
DB_MEMBER=knightsb_membership
ALIAS_1=knightsbridgeassociation.org.uk
ALIAS_2=knightsbridgeassociation.co.uk
ALIAS_3=knightsbridgeassociation.com
ALIAS_4=knightsbridgeassociation.net
ALIAS_5=knightsbridgeassociation.org

OUTPUT_DIR=/var/spool/ka_backup/


#RSYNC="/usr/bin/rsync -q"
#RSYNC_OPTS="-az --delete --info=progress2 --info=name0"
#RSYNC_USER=knightsb
#RSYNC_SITE=knightsbridgeassociation.com
#RSYNC_REMOTEDIR=/home/knightsb/public_html/*
#RSYNC_LOCALDIR=/var/www/knightsb/
# RSYNC no longer enabled on remote server, must use FTP(S)
RSYNC_HOST="knightsbridgeassociation.com"
RSYNC_USER="knightsb"
RSYNC_PASS="XGy5PgHvXm"
RSYNC_FTPURL="ftp://$RSYNC_USER:$RSYNC_PASS@$RSYNC_HOST"
RSYNC_LOCALDIR="/var/www/knightsb"
RSYNC_REMOTEDIR="/public_html"
#DELETE="--delete"

MYSQL=/usr/bin/mysql
ROOT_USER=root
ROOT_PWORD=nsc

CMS_USER=knightsb_cms
CMS_PWORD=JQSRHra9o1ppWWI2
CMS_TMP=$(mktemp /tmp/CMS.XXXXXXXXX)

MEM_USER=knightsb_member
MEM_PWORD=SsP4qIm4omu4M
MEM_TMP=$(mktemp /tmp/MEMBER.XXXXXXXXX)


# Download database file
echo "Downloading SQL files"
${CURL} -o ${OUTPUT_DIR}${DB_RC}.sql.gz -u ${SITE_USER}:${SITE_PWORD} ${SITE_URI}/getsqlbackup/${DB_RC}.sql.gz
${CURL} -o ${OUTPUT_DIR}${DB_CMS}.sql.gz -u ${SITE_USER}:${SITE_PWORD} ${SITE_URI}/getsqlbackup/${DB_CMS}.sql.gz
${CURL} -o ${OUTPUT_DIR}${DB_MEMBER}.sql.gz -u ${SITE_USER}:${SITE_PWORD} ${SITE_URI}/getsqlbackup/${DB_MEMBER}.sql.gz
echo "Downloading email alias files"
${CURL} -o ${OUTPUT_DIR}email/${ALIAS_1}.gz -u ${SITE_USER}:${SITE_PWORD} ${SITE_URI}/getaliasbackup/aliases-${ALIAS_1}.gz
${CURL} -o ${OUTPUT_DIR}email/${ALIAS_2}.gz -u ${SITE_USER}:${SITE_PWORD} ${SITE_URI}/getaliasbackup/aliases-${ALIAS_2}.gz
${CURL} -o ${OUTPUT_DIR}email/${ALIAS_3}.gz -u ${SITE_USER}:${SITE_PWORD} ${SITE_URI}/getaliasbackup/aliases-${ALIAS_3}.gz
${CURL} -o ${OUTPUT_DIR}email/${ALIAS_4}.gz -u ${SITE_USER}:${SITE_PWORD} ${SITE_URI}/getaliasbackup/aliases-${ALIAS_4}.gz
${CURL} -o ${OUTPUT_DIR}email/${ALIAS_5}.gz -u ${SITE_USER}:${SITE_PWORD} ${SITE_URI}/getaliasbackup/aliases-${ALIAS_5}.gz



if [ ! -f ${OUTPUT_DIR}${DB_RC}.sql.gz ]
then
    echo There was a problem downloading the SQL file for Roundcube
    exit 1
else
    echo SQL file is downloaded for Roundcube
fi
if [ ! -f ${OUTPUT_DIR}${DB_CMS}.sql.gz  ]
then
    echo There was a problem downloading the SQL file for CMS
    exit 1
else
    echo SQL file is downloaded for CMS
fi
if [ ! -f ${OUTPUT_DIR}${DB_MEMBER}.sql.gz  ]
then
    echo There was a problem downloading the SQL file for Member DB
    exit 1
else
    echo SQL file is downloaded for Member dB
fi

#echo "RSYNC'ing webserver files"
# ${RSYNC} ${RSYNC_OPTS} -e "ssh -i /root/.ssh/id_rsa" ${RSYNC_USER}@${RSYNC_SITE}:${RSYNC_REMOTEDIR} ${RSYNC_LOCALDIR}
echo "RSYNC'ing remote webserver using lftp"
lftp -c "set ftp:list-options -a;
set ssl:verify-certificate no;
open '$RSYNC_FTPURL';
lcd $RSYNC_LOCALDIR;
cd $RSYNC_REMOTEDIR;
mirror  \
       $DELETE \
       --verbose  \
       --exclude-glob .well-known/ \
       --exclude-glob cgi-bin/ \
       --exclude-glob kamail/ \
       --exclude-glob bower_components/ \
       --exclude-glob a-file-group-to-exclude* \
       --exclude-glob other-files-to-exclude"


# delete unnecessary directories
rm -rf /var/www/kamail
rm -rf /var/www/cgi-bin

# MySQL database for CMS
echo "Creating database (CMS)"
echo "DROP DATABASE IF EXISTS ${DB_CMS};" > ${CMS_TMP}
echo "CREATE DATABASE ${DB_CMS};" >> ${CMS_TMP}
echo "GRANT USAGE ON *.* TO '"${CMS_USER}"'@'%' IDENTIFIED BY '"${CMS_PWORD}"';" >> ${CMS_TMP}
echo "GRANT ALL ON ${DB_CMS}.* TO '${CMS_USER}'@'%' IDENTIFIED BY '${CMS_PWORD}';" >> ${CMS_TMP}
echo "FLUSH PRIVILEGES;" >> ${CMS_TMP}
mysql -u ${ROOT_USER} --password=${ROOT_PWORD} -D mysql < ${CMS_TMP}
rm -rf ${CMS_TMP}

echo "Populating database (CMS)"
gunzip ${OUTPUT_DIR}${DB_CMS}.sql.gz
mysql -u ${ROOT_USER} --password=${ROOT_PWORD} -D ${DB_CMS} < ${OUTPUT_DIR}${DB_CMS}.sql
# replace old file
gzip ${OUTPUT_DIR}${DB_CMS}.sql

# MySQL database for  Member DB
echo "Creating database (Members)"
echo "DROP DATABASE IF EXISTS ${DB_MEMBER};" > ${MEM_TMP}
echo "CREATE DATABASE ${DB_MEMBER};" >> ${MEM_TMP}
echo "GRANT USAGE ON *.* TO '"${MEM_USER}"'@'%' IDENTIFIED BY '"${MEM_PWORD}"';" >> ${MEM_TMP}
echo "GRANT ALL ON ${DB_MEMBER}.* TO '${MEM_USER}'@'%' IDENTIFIED BY '${MEM_PWORD}';" >> ${MEM_TMP}
echo "FLUSH PRIVILEGES;" >> ${MEM_TMP}
mysql -u ${ROOT_USER} --password=${ROOT_PWORD} -D mysql < ${MEM_TMP}
rm -rf ${MEM_TMP}

echo "Unzipping downloaded file for member DB"
gunzip ${OUTPUT_DIR}${DB_MEMBER}.sql.gz
echo "Doing find and replace for 'knightsb'"
sed 's/DEFINER=`knightsb`/DEFINER=`knightsb_member`/g' ${OUTPUT_DIR}${DB_MEMBER}.sql > ${OUTPUT_DIR}${DB_MEMBER}.sql.new
mv ${OUTPUT_DIR}${DB_MEMBER}.sql.new ${OUTPUT_DIR}${DB_MEMBER}.sql
echo "Populating database for (Members)"
mysql -u ${ROOT_USER} --password=${ROOT_PWORD} -D ${DB_MEMBER} < ${OUTPUT_DIR}${DB_MEMBER}.sql
# replace old files
gzip ${OUTPUT_DIR}${DB_MEMBER}.sql


echo "Creating log and temp directories"
mkdir -p /var/log/ka
chown -R www-data.www-data /var/log/ka

echo "Set permissions on /var/www"
chown -R www-data.www-data /var/www

echo "Amend apache host config file"
find /etc/apache2/sites-available  -type f -print0 | xargs -0 sed -i 's/knightsbdev /knightsb /g'
systemctl reload apache2

