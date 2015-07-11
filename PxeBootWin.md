## Using PXE Boot to install an image from Windows ##

Read [InstallingPyramid/PxeBoot PXE Boot] for lots of useful information first.

> ''I've been sitting on the notes below for 12 months waiting for time to 'do them right'. And now Pyramid has replaced Metrix Pebble and I still haven't done it. Please feel free to jazz these up and fill them out, but I'm confident they'll be useful even as is.''

Here are some notes that work for installing images (such as [m0n0wall](http://m0n0.ch/wall) and [roofnet](http://roofnet.net)) as well as CF images (not tar archives) for Pyramid.

## Software Requirements ##

You'll need a tftp server and simple web server on your PC. The following are two programs that are freely available and worked well. Doubtless other programs will also.

## Setup Windows ##

Your computer will be disconnected from the internet while flashing via PXE (unless you want to get really fancy), so first gather the TFTPD32 program (per below) and an HTTP server. When you've done that, configure your Windows box as follows:

  1. disconnect your internet feed
  1. set your IP to 192.168.200.1/24 (the utility [winips.com](http://winips.com) is handy for this)
  1. turn of your Windows Firewall (either the builtin one, or any other one)

We do this before starting and configuring tftpd32 and the webserver because they seem to behave better if the IP address is preconfigured.

## Install and Configure TFTPD32 ##

[Tftpd32](http://tftpd32.jounin.net/) is a windows TFTP program available from various places. Some later versions I tried didn't work as well as earlier ones. [Version 2.80](http://tftpd32.jounin.net/download/tftpd32.280.zip) worked on Windows XP. The steps are:

  1. Install tftpd32 into some directly, say `c:\tftpd`
  1. copy the entire directory http://metrix.net/support/pxeboot/tftpboot into that directory
  1. start tftpd32
  1. set the basedir to `c:\tftpd` (if it doesn't default to that)
  1. on the DHCP server tab, set the following:
```
  IP pool starting address: 192.168.200.2
  Size of pool: 1
  boot file: pxelinux.0
  Mask: 255.255.255.0
```
  1. copy the file `dd` to `initrd.img`

Now tftpd32 is ready to download initrd.img when prompted. The 'dd' program will automatically fetch a files called 'image' from the web server at the same IP, so now we move on to configuring the web server.

## Install and Configure the webserver ##

The [Abyss web server](http://www.aprelium.com/abyssws/) is a simple free web server. The instructions assume you're using that.  Once you have it installed:

  1. start the webserver
  1. set the basedir to, say, `c:\httpdir`
  1. copy the desired image file to a file called `image` in the `c:\httpdir` directory

## Flash the Device ##

Now your machine is already to go.  You can flash a Soekris 4526 as follows:

  1. plug a cat-5 cable into the Soekris and your computer. Remember you'll need a crossover cable unless your computer autosenses.
  1. plug into the serial port and get the console up (9600,8,1 by default)
  1. power up the Soekris
  1. interrupt the boot sequence with Ctrl-P
  1. enter the command
```
boot f0
```

If everything goes right, you'll see lots of things happening in roughly this order:

  * tftpd will show the soekris getting an address via dhcpd
  * tftpd will show the soekris downloading various boostrap files
  * the soekris console will show it fetching the `image` file from the webserver
  * finally you'll get a pair of `xx/xxxx bytes read` then `write` message and the box will reboot

Congratulations, when the device reboots it should be running the new OS!

## Important Last Step - Turn your firewall back on! ##