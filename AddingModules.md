### Adding modules ###

'''How do I add an kernel module to my Pyramid system if it is not included in the stock distro?'''

This is a fairly common question on the mailing list. So this is an attempt at creating a how to for people that would like start customizing their kernels and/or kernel modules.

## What is required ##

1. You are going to need a linux system with build tools (make, gcc, etc.). If you have already built custom kernels on your machine then you are probably ready to go. For this document I am using the standard issue build tools from "apt" on Ubuntu Gutsy.

2. Kernel sources. A copy of the kernel sources we use for building the kernel and modules. This can be downloaded here http://dl.metrix.net/support/dist/kernel/.


## Adding a new module ##

In some cases you can just build the required module in your kernel source dir and copy it over to your pyramid box. In some cases you may need to do more in which case I suggest you just copy everything over (kernel and modules).

For a simple example let say we want to build the eepro100 network card driver kernel module.

After downloading the kernel source, decompress and untar it:

```
ken@apu:/tmp/kernel$ tar -xvzf pyramid-1.0b5-kernel-src.tgz 
```

You should now have a directory called "linux-2.6.19.2" and within it a sub directory called "linux". Go into the linux directory and run the command "make clean":

```
ken@apu:/tmp/kernel/linux-2.6.19.2/linux$ make clean
```

This will clean out any previously compiled things. Although in this case there is nothing in the tar ball to clean, its a good idea to get in the habit of doing this.

Next use the command "make menuconfig". This will provide a ncurses based interface for manipulating your kernel configuration.

'''Note: make menuconfig uses ncurses and the ncurses-dev package. If you get any errors running it please make sure you have these components installed.'''

```
ken@apu:/tmp/kernel/linux-2.6.19.2/linux$ make menuconfig
```

Now you should be in the curses menu. In this example we are going to add a network card driver:

1. Choose the device driver option from the first menu

2. On the second menu choose network device support

3. On the next menu choose Ethernet (10 or 100Mbit)

4. On the next menu choose  EtherExpressPro/100 support (eepro100, original Becker driver) and hit space bar until you see an "M" in between the "<>" brackets.
  * M tells the system to build this driver as loadable kernel module. If you choose `*` it will attempt to compile it into the kernel. Loadable modules are desired because then you can keep the main kernel size down and only load the modules you need when you need them.

5. Now exit "make menuconfig" and when prompted make sure to save the changes to the configuration file.

6. Now run the "make" command to begin building your kernel (note: this can take a while).

7. Once the kernel compilation is complete run "make modules".

8. Now in kernel/drivers/net you will find the eepro100.ko kernel module.

9. If your pyramid system is running the same version of the kernel you can copy this file to /lib/modules/2.6.19.2-pyramid.metrix.net/kernel/drivers/ on your Pyramid box. On the Pyramid system you must first run the "remountrw" command to mount the root filesystem read/write. The copy the file over to the proper modules sub directory.

10. Next run "depmod" to update the modules map.

11. Test your eepro100.ko module with the following command "modprobe eepro100"
