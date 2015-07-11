# Frequently Asked Questions #
EDIT
## General ##

**''What happened to Pebble Linux?''**

You can still djjkjkkkoad [Metrix Pebble](http://metrix.net/support/dist/old/) and the original [NycWireless Pebble](http://nycwireless.net/pebble/). Pyramid is almost as complete feature wise as pebble however there are still some things that need to be added. We suggest you try Pyramid, even [Nycwireless](http://nycwireless.net) the original home of pebble has switched to using Pyramid.

**''Is Pyramid Ubuntu?''**

No. Most of the stock system binaries are taken from Ubuntu but thats about it. There is no "apt" or any of the chrome you would expect to find in Ubuntu or a desktop focused operating system.

**''Why do you store binaries in source control?''**

Pyramid is primarily created from pre-built binaries. There is some new code, primarily the web interface. However we make a lot of changes to the system, its layout and the binaries from release to release. We need to track that. Since we do not build from source we keep all the files in source control. This allows us to track what changes we make to the distro and it allows you to track it as well.

## Setup and Install ##

**''I just installed Pyramid, how do I log in?''**

The default login is username: root password: root
You should change this immediately, this allows you to login via the serial console, ssh, and the ssl web interface.

**''How do I install Pyramid on a device with on-board compact flash?''**

Use a PXE install environment to install the system. Check out the InstallingPyramid page.

**''Can I upgrade from Pebble?''**

If you are working on a Soekris 4526 or 4826, and you already have Pyramid or Pebble installed, you can use UpgradingImg


## Captive Portals ##

**'' What is the recommended captive portal on Metrix Pyramid and how does one most easily install it? ''**

Currently [WifiDog](http://wifidog.org) and [chillispot](http://chillispot.org) are included in Pyramid. They both require a backend server to handle authentication and splash pages. On pyramid you get the gateway software which handles blocking and permitting of users on the network based authentication to the central server.

I have also packaged up nocat splash for pyramid, it provides minimal portal/splash page functionality. You can find it here: http://ken.ipl31.net/pyramid/ its meant to be untarred into /usr/local. The default config uses ath0 for the wireless interface and eth0 for the internal/ethernet interface. You will probably need to change that for your environment. You will also need it add it to the appropriate run level to start at boot.


## Hardware Issues ##


**'' How do I use a USB drive on Mark II or Net48XX system ''**

This requires a couple of kernel modules to be loaded which are not loaded by default. Add the following modules to your '''/etc/modules''' file so they will be loaded at boot:

```
ohci_hcd
usb_storage
sd_mod
```

Alternatively if you just want to test the functionality you may manually load the modules from the command line:

```
modprobe ohci_hcd usb_storage sd_mod
```

If you run the 'dmesg' command you should see some informative output regarding the discovery of your USB hardware.

At this point if your usb drive is connected you should see devices in the "/dev/" directory that begin with "sd":

```
pyramid:~# ls /dev | grep sd
sda
sda1
```

From here you may mount the drive to a mount point of your liking, in this case we created a directory called "usb" in "/mnt":

```
cd /mnt
mkdir usb
mount /dev/sda1 /mnt
```

If your usb drive is FAT formatted drive you may need to do the following:

```
mount -t vfat /dev/sda1 /mnt -o iocharset=cp437
```



**''If I disable my Atheros interface (ath0 or ath1) it dissapears''**

This is a bug in our web interface that is caused by how we handle creating VAPs with the madwifi driver. To re-enable the card edit the /etc/network/interfaces file and uncomment the line "#auto ath0" by removing the "#". Reboot the box and the interface should be back.




**''My Prism2 card is acting funny''**

If your Prism card ( NL-2511MP ) is acting squirrely (can't keep an association in managed mode, doesn't scan correctly, etc.) you may need to update the firmware.

```

cd /usr/local/firmware/prism
prism2_srec -v -f wlan0 pk010101.hex sf010800.hex
prism2_srec -v -f wlan1 pk010101.hex sf010800.hex
hostap_diag wlan0
hostap_diag wlan1

```

**'' How do I disable antenna diversity? or I am only using one antenna on my Wi-Fi card in my router''**

If you are using only a single antenna port on your atheros card, you will want to disable antenna diversity (diversity enables the card to handle some forms of multipath interference, but if you only have a single antenna connected, then the card is just throwing away power to the unconnected antenna).

To turn off antenna diversity (using madwifi-ng, which is the default on pyramid), add the following lines to `/etc/sysctl.conf`:

```
dev.wifi0.diversity = 4
dev.wifi0.rxantenna = 1
dev.wifi0.txantenna = 1
```

Of course, this assumes that your atheros device is `wifi0` and that the antenna is connected to port 0 on the card. If either of these things are not the case, you can change the `wifi0` to the correct device (perhaps `wifi1`) or you can change the antenna assignments to `rxantenna = 2` and `txantenna = 2`.

**'' How do I turn up the power on my wifi card? ''**

When you bought your router, you probably were sold a Wi-Fi card with a particular power rating (100mW, 200mW or even 400mW). Depending on the card you have, Pyramid may not enable it at the highest transmit power. The output power can be controlled by the "iwconfig" command. How high the power will go depends on your card. For a CM9 the highest output power is 19dbm ( note the '''Tx-Power''' ):

```

ath0      IEEE 802.11g  ESSID:"Metrix Atheros 1"
          Mode:Master  Frequency:2.412 GHz  Access Point: 00:0B:6B:57:22:56
          Bit Rate:0 kb/s   Tx-Power:19 dBm   Sensitivity=0/3
          Retry:off   RTS thr:off   Fragment thr:off
          Encryption key:off
          Power Management:off
          Link Quality=0/94  Signal level=-95 dBm  Noise level=-95 dBm
          Rx invalid nwid:2  Rx invalid crypt:0  Rx invalid frag:0
          Tx excessive retries:0  Invalid misc:0   Missed beacon:0



```

Every card is different. To determine how high you can set the power, start increasing in increments of 1 dbm till you recieve an error:


```
    iwconfig ath0 txpower 16    
```

then

```
    iwconfig ath0 txpower 17
```

etc... Until you see an error like this:

```
Error for wireless request "Set Tx Power" (8B26) :
    SET failed on device ath0 ; Invalid argument.
```

This usually indicates that they argument you have supplied is not supported by the card, this you have reached the maximum power setting.

There are some issues in the MadwifiNg driver, where in client mode your power settings will only stick once the card is associated. If it loses association it will switch back to default power setting.


If you'd like this setting enabled by default, you can add it to `/etc/network/interfaces` after the `pre-up sleep 3` command:

```
    pre-up iwconfig ath0 txpower 19
```



**'' My WRAP does not reboot with Pyramid Linux!! Router hangs on reboot''**

It's just a problem with GRUB, let's fix this!

First of all remount read write to modify files :)
```
remountrw 
```

Now let's open the file to modify
```
nano /boot/grub/menu.lst
```

on the lines that start with the word "kernel" append the string reboot=bios

This is the file I have after the changes
```
timeout 3

serial --device=/dev/ttyS0 --speed=19200 --word=8 --parity=no --stop=1
terminal serial

title Metrix
root (hd0,0)
kernel /boot/vmlinuz root=/dev/hda1 console=ttyS0,19200n8 reboot=bios

title Shell
root (hd0,0)
kernel /boot/vmlinuz root=/dev/hda1 console=ttyS0,19200n8 init=/bin/bash reboot=bios

```

Save & Exit the nano editor :)

remount read only
```
remountro
```

Now switch the power off and on again for the last time! Since this boot the kernel option you are passing will allow correct reboots! :)

**'' How do I enable the Ethernet ports on a PC Engines Alix board?''**

We need to add the Via Rhine module to `/etc/modules/', to do this you need to edit the modules file as follows.

```
# /sbin/rw
# nano /etc/modules
```

Here is a example of the modules file.

```
# /etc/modules: kernel modules to load at boot time.
#
# This file should contain the names of kernel modules that are
# to be loaded at boot time, one per line.  Comments begin with
# a "#", and everything on the line after them are ignored.
#
# hostap and madwifi
#
hostap_pci
ath_pci autocreate=none
#
# natsemi for Ethernet
# 
natsemi
#
# via-rhine for Ethernet on Alix Boards 
#
via_rhine
#
# elan 520 watchdog timer ( soekris 45XX series or Metrix Mark I)
# (run "/etc/init.d/watchdog start" to initialize it)
#
```
You need to add these lines
```
#
# via-rhine for Ethernet on Alix Boards 
#
via_rhine
```

Save and exit nano by issuing `ctrl + x` and follow the prompts.
Remount the file system read only and reboot.

```
# /sbin/ro
# reboot
```


## Setting Up a Pyramid Development Environment ##

If you are interested in building a development environment, you will need to use Ubuntu Breezy as your starting point. Since the official mirrors have mostly been pulled, feel free to use ours. If you are just adding a binary or lib from the standard dist, it's extremely straightforward.

Just change your sources in /etc/apt/sources.list (on your dev box) to: http://metrix.net/ubuntu/

The kernel config is located on a running box at /proc/config.gz