#This is a script to setup grub during PXE boot of Soekris 4XXX device
#It creates some required devices for the initial boot and installs grub
cd /dev
mknod console c 5 1
mknod tty0 c 4 0
mknod hda b 3 0
mknod hda1 b 3 1
cat /boot/grub/grub-script | /sbin/grub
