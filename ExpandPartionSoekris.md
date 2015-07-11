Expanding the partition on a Soekris net4826 from 64MB to 128MB

After installing Pyramid Linux on a Soekris net4826 these are the steps I took to expand the partition from 64MB to use the full amount of embedded CF Flash (128MB)

I assume you have already got the latest image installed on your net4826, if not do it now.

Reboot the box and when the boot menu appears select 'Shell'.

At the command prompt.

{{{fsck /dev/hda1

fdisk /dev/hda}}}  (The follow are the keystrokes to enter whilst in fdisk)

  1. d (Delete the partition)
  1. n (Create a new partition)
  1. p (set the partition as primary)
  1. 1 (Select the first partition)
  1. First Cylinder  = 1 (This is the default)
  1. Last Cylinder = [Enter](Enter.md) (On a net4826 this is likely to be 1954, the maximum size available is the default)
  1. a (This sets the boot flag)
  1. 1 ( - on partition 1)
  1. w (Write out the partition table)

Reboot the box and on boot up select the shell option again.

` resize2fs /dev/hda1` (Resizes the partition)

`fsck -n /dev/hda1` (Check the integrity of the new partition)

Reboot the box and select your pyramid installation.

When you have logged run `'df -h'` and you should now have the full amount of disk space available to you.

For more information see http://www.howtoforge.com/linux_resizing_ext3_partitions_p2


