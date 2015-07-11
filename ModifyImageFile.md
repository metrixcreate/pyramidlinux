## How to mount a pyramid.img file so that you can edit it. ##


The setup I used for this.

  * Development workstation - Ubunutu 7.04

  * Embedded board - Soekris net4826

1. Download the latest image from http://dl.metrix.net/support/dist/

2. `mkdir ~/metrix-images`

3. move the image file into ~/metrix-images/.

4. Gunzip the image.

5. `sudo losetup -o 16384 /dev/loop0 ~/metrix-images/pyramid-1.0b5.img`
> The first 16KB of the image file is used by  the boot loader so the actual partition and filesystem don't start until after this.

> The -o tells how many bytes past the beginning of the image file to map to /dev/loop0

6. ` sudo mount /dev/loop0 ~/metrix `

7. Edit away to get you filesystem as you want it

8. `sudo umount ~/metrix`

9. `sudo losetup -d /dev/loop0`

You should now be able to use the updated image on you board

''Notes''

The following is usefull if you want to change the ip address of the server that images are downloaded from.

If you want to edit the dd.img file try sudo losetup /dev/loop0 dd.img

Then `sudo mount /dev/loop0 ~/metrix -t minix`

Then edit the `*`.sh scripts to correspond to the server ip address.

As usual everything here is at your own risk, if anything breaks you get to keep both parts.