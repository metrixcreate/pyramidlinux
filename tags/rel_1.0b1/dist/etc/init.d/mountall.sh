#
# mountall.sh	Mount all filesystems.
#
# Version:	@(#)mountall.sh  2.83-2  01-Nov-2001  miquels@cistron.nl
#
. /etc/default/rcS

#
# Mount local file systems in /etc/fstab. For some reason, people
# might want to mount "proc" several times, and mount -v complains
# about this. So we mount "proc" filesystems without -v.
#
[ "$VERBOSE" != no ] && echo "Mounting local filesystems..."
mount -avt nonfs,nosmbfs,noncpfs,noproc
mount -at proc
cp -a /ro/* /rw/
#cp /proc/mounts /etc/mtab

kill -USR1 1
