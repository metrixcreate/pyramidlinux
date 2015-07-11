### Using hardware watchdog and the watchdog daemon on Pyramid ###

Soekris based systems such as the Mark I and Mark II come with built in hardware watchdogs.

A hardware watchdog once initialized will expect a heartbeat from the operating system. If it fails
to receive this heartbeat it will force the system to reboot. This is useful if your system becomes
unstable.

The hardware watchdogs are supported by modules in the Linux kernel:
| **System Type**  |  Kernel Module |
|:-----------------|:---------------|
| **Soekris 45XX based systems:** | sc520\_wdt     |
| **Soekris 48XX based systems:** | scX200\_wdt    |

Enabling the kernel module for the watch dog timer:

1. Determine your system type. If you are using 45XX (i.e. Metrix Mark I) based board you need to load the sc520\_wdt. If you are using a 48XX (i.e. Metrix Mark II)  based system you need to load the scX200\_wdt module.

2. First determine your Pyramid version. If you are running 1.0b5 or earlier the included sc520\_wdt module does
not work properly. In releases after 1.0b5 it has been patched to work properly. If you are running 1.0b5 you can download the latest version of the sc520\_wdt driver [here](http://pyramid.metrix.net/trac/browser/Pyramid/dist/lib/modules/2.6.19.2-pyramid.metrix.net/kernel/drivers/char/watchdog/sc520_wdt.ko). You should then replace the existing one on your system in ` /lib/modules/2.6.19.2-pyramid.metrix.net/kernel/drivers/char/watchdog/ `.

If you are running a release more recent than 1.0b5 you should be fine.

If you are using the scX200\_wdt module you should be fine as well.

3. Enable the module in /etc/modules:

For the sc520\_wdt module add the following line:
```
sc520_wdt
```

For the scX200\_wdt module add the following line:
```
scx200_wdt margin=30
```

**note the sc520 defaults to rebooting the system after 30 seconds of heartbeat loss. The scx200\_wdt defaults to 60 second. The "margin" parameter changes this to 30 seconds.**

4. Reboot your system.

5. Using lsmod confirm the modules loaded and confirm that a device node for /dev/watchdog was create in /dev/.

6. Make sure you have the following line uncommented in /etc/watchdog.conf:

```

watchdog-device = /dev/watchdog

```

7. Now to test the functionality start the watchdog daemon manually with the "-v" flag for verbose logging.

```
watchdog -v
```

8. Now using '''ps''' determine the process id of the watchdog daemon. Then use "kill -9" to kill the process. In 30 seconds the system should
reboot.

9. To enable the watchdog daemon at boot, add a link from /etc/rc2.d/ to the init script /etc/init.d/watchdog.

