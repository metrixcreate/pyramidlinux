#!/bin/bash
#Shell script to export Pyramid from SVN and make a tarball for PXE install
#
#We assume PWD is where you want all of this to take place
FILEHEAD=pyramid
PROTO=http
HOST=pyramid.metrix.net
REPO="svn/Pyramid/dist/"
EXPORTCMD="svn export $PROTO://$HOST/$REPO | tee export.log" 
WHOAREYOU=$(whoami)
FAKEROOT=/usr/bin/fakeroot

if [ $WHOAREYOU != root ] && [ ! -x $FAKEROOT ] ; then
echo "You need to run this as root or have fakeroot installed in order to properly set permissions in the tarball distro"
exit
fi

cat > pyramid-work.sh << EOF
#!/bin/bash
mkdir work
cd work
$EXPORTCMD
if [ -e export.log ] ; then
  sed -n 's/^Exported revision \([0-9]*\)./svn-\1/p' export.log > dist/etc/pyramid_version
fi
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
chown -R 0:0 sys dev proc root rw
chown -R 500:500 ro/kismet
#next round of permission fixing
chmod 0440 etc/sudoers
chmod -R +x etc/init.d/
chmod 777 tmp

if [ -e ../export.log ] ; then
  sed -n 's/^Exported revision \([0-9]*\)./Metrix Pyramid\/\\\\s  \\\\n \\\\l (svn-\1)\n/p' ../export.log > ro/etc/issue
  sed -n 's/^Exported revision \([0-9]*\)./Metrix Pyramid\/%s %h (svn-\1)/p' ../export.log > ro/etc/issue.net
fi

tar -cvpf ../build.tar *
cd ..
EOF
chmod 755 pyramid-work.sh

if [ -x $FAKEROOT ] ; then
	$FAKEROOT "./pyramid-work.sh"
else
	./pyramid-work.sh
fi

mv work/build.tar $FILEHEAD-`cat work/dist/etc/pyramid_version`.tar
rm pyramid-work.sh
