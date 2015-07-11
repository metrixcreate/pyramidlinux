# How do I use a USB drive on Mark II or Net48XX system #

This requires a couple of kernel modules to be loaded which are not loaded by default. Add the following modules to your _/etc/modules_ file so they will be loaded at boot:
```
ohci_hcd
usb_storage
sd_mod 
```

Alternatively if you just want to test the functionality you may manually load the modules from the command line:

```
modprobe ohci_hcd usb_storage sd_mod `
```

If you run the _'dmesg'_ command you should see some informative output regarding the discovery of your USB hardware.

At this point if your usb drive is connected you should see devices in the "/dev/" directory that begin with "sd":

```
pyramid:~# ls /dev | grep sd
sda
sda1
```

From here you may mount the drive to a mount point of your liking, in this case we created a directory called _"usb"_ in _"/mnt"_:

```
cd /mnt
mkdir usb
mount /dev/sda1 /mnt
```

If your usb drive is FAT formatted drive you may need to do the following:

```
mount -t vfat /dev/sda1 /mnt -o iocharset=cp437
```