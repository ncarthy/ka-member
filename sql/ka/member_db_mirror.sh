#!/bin/bash
#
# member_mirror.sh
#
# 1. Download a backup copy of the knightsb member database from the NetNerd server
# 2. Overwrite the existing member dB on this vm with the new data
# 3. Echo to user if all went ok
#
# If need the cms database updated then use mirror.sh

CURL="/usr/bin/curl -s"
COOKIE="/root/ka/cookies.txt"
SITE_URI="https://uk1.cp.netnerd.com:2083"
DB_MEMBER_OLD=knightsb_membership
DB_MEMBER=knightsb_membership2
OUTPUT_DIR=/var/spool/ka_backup/

MYSQL=/usr/bin/mysql
ROOT_USER=root
ROOT_PWORD=nsc

MEM_USER=knightsb_member
MEM_PWORD=SsP4qIm4omu4M
MEM_TMP=$(mktemp /tmp/MEMBER.XXXXXXXXX)
SKIP=NO
APPLY=YES

AMEND_FILE=/root/ka/ka-member/sql/amendments.sql

for i in "$@"
do
case $i in
    -s*|--skip*)
    SKIP=YES
    shift # past argument=value
    ;;
    -n*|--dont-apply*)
    APPLY=NO
    shift # past argument=value
    ;;
   -v*|--debug*)
    set -x
    shift
    ;;
    --default)
    DEFAULT=YES
    shift # past argument with no value
    ;;
    *)
          # unknown option
    ;;
esac
done

echo "Skip downloading from NetNerd = ${SKIP}, Apply amendments.sql to new DB = ${APPLY}"

TOKEN=$(php /root/ka/login.php)

if [[ $SKIP = "NO" ]]
then
        # Download database file
        echo "Downloading Member DB SQL file"
        echo "from " ${SITE_URI}${TOKEN}/getsqlbackup/${DB_MEMBER_OLD}.sql.gz
        echo "to " ${OUTPUT_DIR}${DB_MEMBER}
        ${CURL} -o ${OUTPUT_DIR}${DB_MEMBER}.sql.gz -b ${COOKIE} ${SITE_URI}${TOKEN}/getsqlbackup/${DB_MEMBER_OLD}.sql.gz

        if [ ! -f ${OUTPUT_DIR}${DB_MEMBER}.sql.gz  ]
        then
            echo There was a problem downloading the SQL file for Member DB
            return 1
        else
            echo SQL file is downloaded for Member dB
        fi
else
        echo "Not downloading SQL file"
fi

# MySQL database for  Member DB
echo "Creating database (Members)"
echo "DROP DATABASE IF EXISTS ${DB_MEMBER};" > ${MEM_TMP}
echo "CREATE DATABASE ${DB_MEMBER};" >> ${MEM_TMP}
echo "GRANT USAGE ON *.* TO '"${MEM_USER}"'@'%' IDENTIFIED BY '"${MEM_PWORD}"';" >> ${MEM_TMP}
echo "GRANT ALL ON ${DB_MEMBER}.* TO '${MEM_USER}'@'%' IDENTIFIED BY '${MEM_PWORD}';" >> ${MEM_TMP}
echo "FLUSH PRIVILEGES;" >> ${MEM_TMP}
mysql -u ${ROOT_USER} --password=${ROOT_PWORD} -D mysql < ${MEM_TMP}
rm -rf ${MEM_TMP}

echo "Unzipping downloaded file"
gunzip ${OUTPUT_DIR}${DB_MEMBER}.sql.gz
echo "Doing find and replace"
sed 's/DEFINER=`knightsb`/DEFINER=`knightsb_member`/g' ${OUTPUT_DIR}${DB_MEMBER}.sql > ${OUTPUT_DIR}${DB_MEMBER}.sql.new
mv ${OUTPUT_DIR}${DB_MEMBER}.sql.new ${OUTPUT_DIR}${DB_MEMBER}.sql

echo "Populating database for (Members)"
mysql -u ${ROOT_USER} --password=${ROOT_PWORD} -D ${DB_MEMBER} < ${OUTPUT_DIR}${DB_MEMBER}.sql
# replace old files
gzip ${OUTPUT_DIR}${DB_MEMBER}.sql

echo "Update amendments.sql file from git"
# 'source' from https://stackoverflow.com/a/8352939/6941165
source /root/ka/update_repo.sh
if [[ $APPLY = "YES" ]]
then
        echo "Applying SQL file"
        mysql -u ${MEM_USER} --password=${MEM_PWORD} -D ${DB_MEMBER} < ${AMEND_FILE} >> /dev/null
fi
