#!/bin/bash
#Shell script to export Pyramid from SVN and make a tarball for PXE install
#
#We assume PWD is where you want all of this to take place
DATE=$(date +%T-%D | sed -e "s/\//-/g")
FILENAME=pyramid-$DATE.tgz
PROTO=http
HOST=pyramid.metrix.net
REPO="svn/Pyramid/dist/"
EXPORTCMD="svn export $PROTO://$HOST/$REPO" 
WHOAREYOU=$(whoami)
if [ $WHOAREYOU != root ]; then
echo "You need to run this as root, in order to properly set permissions in the tarball distro"
exit
fi

mkdir work
cd work
$EXPORTCMD
cd dist
#Insert stuff to fix baseline permissions here
chown -R 0:0 etc
chown -R 0:0 bin
chown -R 0:0 boot
chown -R 0:50 home
chown -R 0:0 lib
chown -R 0:0 lost+found
chown -R 0:0 mnt
chown -R 0:0 ro
chown -R 0:0 sbin
chown -R 0:0 tmp
chown -R 0:0 usr
chown -R 0:0 var
#next round of permission fixing
chmod 0440 etc/sudoers
chmod -R +x /etc/init.d/


tar -cvpf ../$FILENAME *
cd ..
