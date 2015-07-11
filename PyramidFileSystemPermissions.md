This page aims to document the default file system permissions in a typical Pyramid install. This is an exercise in auditing and documentation.

### / dir : ###

```

drwxr-xr-x  17 root root   1024 Apr  8  1980 .
drwxr-xr-x  17 root root   1024 Apr  8  1980 ..
drwxr-xr-x   2 root root   4096 Apr  8  1980 bin
drwxr-xr-x   2 root root   1024 Apr  8  1980 boot
drwxr-xr-x   8 root root   2340 Jan  1 00:00 dev
drwxr-xr-x  42 root root   3072 Apr  8  1980 etc
drwxrwsr-x   2 root staff  1024 Feb  8  2002 home
drwxr-xr-x   9 root root   5120 Apr  8  1980 lib
drwx------   2 root root  12288 Jan  1  1980 lost+found
drwxr-xr-x   2 root root   1024 Feb  8  2002 mnt
dr-xr-xr-x  46 root root      0 Jan  1 00:00 proc
drwxr-xr-x   8 root root   1024 Apr  8  1980 ro
lrwxrwxrwx   1 root root      8 Apr  8  1980 root -> /rw/root
drwxrwxrwt   8 root root    160 Jan  1 00:00 rw
drwxr-xr-x   2 root root   4096 Apr  8  1980 sbin
drwxr-xr-x  10 root root      0 Jan  1 00:00 sys
lrwxrwxrwx   1 root root      7 Apr  8  1980 tmp -> /rw/tmp
drwxr-xr-x   4 root root   1024 Apr  8  1980 usr
drwxr-xr-x   8 root root   1024 Apr  8  1980 var

```


### /bin dir ###

```

drwxr-xr-x   2 root root      4096 Apr  8  1980 .
drwxr-xr-x  17 root root      1024 Apr  8  1980 ..
-rwxr-xr-x   1 root root     23936 Sep  5  2005 [
-rwxr-xr-x   1 root root      2808 Sep 20  2005 arch
-rwxr-xr-x   1 root root     80120 Feb 16  2006 ash
lrwxrwxrwx   1 root root         9 Apr  8  1980 awk -> /bin/mawk
-rwxr-xr-x   1 root root     12084 Sep  5  2005 basename
-rwxr-xr-x   1 root root    645140 Oct  5  2005 bash
-rwxr-xr-x   1 root root      6869 Oct  5  2005 bashbug
lrwxrwxrwx   1 root root         3 Apr  8  1980 captoinfo -> tic
-rwxr-xr-x   1 root root     16016 Sep  5  2005 cat
-rwxr-xr-x   1 root root      3369 Oct  9  2005 catchsegv
-rwxr-sr-x   1 root shadow   34032 Oct 11  2005 chage
-rwxr-xr-x   1 root root      7316 Aug 23  2005 chattr
-rwsr-xr-x   1 root root     28144 Oct 11  2005 chfn
-rwxr-xr-x   1 root root     30432 Sep  5  2005 chgrp
-rwxr-xr-x   1 root root     30408 Sep  5  2005 chmod
-rwxr-xr-x   1 root root     32364 Sep  5  2005 chown
-rwsr-xr-x   1 root root     28176 Oct 11  2005 chsh
-rwxr-xr-x   1 root root     14996 Sep  5  2005 cksum
-rwxr-xr-x   1 root root      3192 Aug 22  2005 clear
-rwxr-xr-x   1 root root     16972 Sep 20  2005 cmp
-rwxr-xr-x   1 root root     14252 Sep  5  2005 comm
-rwxr-xr-x   1 root root     51008 Sep  5  2005 cp
-rwxr-sr-x   1 root quagga   26616 Mar 14  2006 crontab
-rwxr-xr-x   1 root root     28116 Sep  5  2005 csplit
-rwxr-xr-x   1 root root     22120 Sep  5  2005 cut
-rwxr-xr-x   1 root root     39768 Sep  5  2005 date
-rwxr-xr-x   1 root root     28100 Sep  5  2005 dd
-rwxr-xr-x   1 root root      7272 Sep 20  2005 ddate
-rwxr-xr-x   1 root root     31372 Sep  5  2005 df
-rwxr-xr-x   1 root root     60700 Sep 20  2005 diff
-rwxr-xr-x   1 root root     18680 Sep 20  2005 diff3
-rwxr-xr-x   1 root root     74776 Apr 19  2005 dig
-rwxr-xr-x   1 root root     19848 Sep  5  2005 dircolors
-rwxr-xr-x   1 root root     12244 Sep  5  2005 dirname
-rwxr-xr-x   1 root root      4300 Sep 20  2005 dmesg
-rwxr-xr-x   1 root root     20640 Mar 28  2006 dot
-rwxr-xr-x   1 root root     39384 Sep  5  2005 du
-rwxr-xr-x   1 root root     13880 Sep  5  2005 echo
lrwxrwxrwx   1 root root         8 Apr  8  1980 editor -> /bin/joe
-rwxr-xr-x   1 root root        33 Aug 10  2005 egrep
-rwxr-xr-x   1 root root     12384 Sep  5  2005 env
-rwxr-xr-x   1 root root     14676 Sep  5  2005 expand
-rwxr-sr-x   1 root shadow   16176 Oct 11  2005 expiry
-rwxr-xr-x   1 root root     19732 Sep  5  2005 expr
-rwxr-xr-x   1 root root     10960 Sep  5  2005 false
-rwxr-xr-x   1 root root        33 Aug 10  2005 fgrep
-rwxr-xr-x   1 root root     68480 Jul  5  2005 find
-rwxr-xr-x   1 root root     18324 Sep  5  2005 fmt
-rwxr-xr-x   1 root root     15196 Sep  5  2005 fold
-rwxr-xr-x   1 root root      6188 Apr 21  2005 free
-rwxr-xr-x   1 root root     18944 Apr 14  2005 fuser
-rwxr-xr-x   1 root root     15556 Oct  9  2005 getent
-rwxr-xr-x   1 root root      9004 Sep 20  2005 getopt
-rwsr-xr-x   1 root root     34192 Oct 11  2005 gpasswd
-rwxr-xr-x   1 root root    789356 Jun 10  2005 gpg
-rwxr-xr-x   1 root root    307384 Jun 10  2005 gpgv
-rwxr-xr-x   1 root root     88500 Aug 10  2005 grep
-rwxr-xr-x   1 root root      1675 Sep  5  2005 groups
-rwxr-xr-x   1 root root     51968 Jul  8  2005 gunzip
-rwxr-xr-x   1 root root      4870 Jul  8  2005 gzexe
-rwxr-xr-x   1 root root     51968 Jul  8  2005 gzip
-rwxr-xr-x   1 root root     24976 Sep  5  2005 head
-rwxr-xr-x   1 root root     60408 Apr 19  2005 host
-rwxr-xr-x   1 root root     11540 Sep  5  2005 hostid
-rwxr-xr-x   1 root root     10352 Aug 13  2004 hostname
-rwxr-xr-x   1 root root     48692 Oct  9  2005 iconv
-rwxr-xr-x   1 root root     15072 Sep  5  2005 id
-rwxr-xr-x   1 root root     44708 Aug 22  2005 infocmp
lrwxrwxrwx   1 root root         3 Apr  8  1980 infotocap -> tic
-rwxr-xr-x   1 root root     51072 Sep  5  2005 install
-rwxr-xr-x   1 root root    127960 Feb 15  2006 ip
-rwxr-xr-x   1 root root    288664 Oct 27  2004 joe
-rwxr-xr-x   1 root root     22988 Sep  5  2005 join
-rwxr-xr-x   1 root root     12276 Apr 21  2005 kill
-rwxr-xr-x   1 root root     12440 Apr 14  2005 killall
-rwxr-xr-x   1 root root     12580 Sep 26  2005 last
lrwxrwxrwx   1 root root         4 Apr  8  1980 lastb -> last
-rwxr-xr-x   1 root root      5972 Oct  9  2005 ldd
-rwxr-xr-x   1 root root     95852 Dec 14  2004 less
-rwxr-xr-x   1 root root     11956 Sep  5  2005 link
-rwxr-xr-x   1 root root     22552 Sep  5  2005 ln
-rwxr-xr-x   1 root root     28040 Oct  9  2005 locale
-rwxr-xr-x   1 root root     28452 Jul  5  2005 locate
-rwxr-xr-x   1 root root      7616 Sep 20  2005 logger
-rwxr-xr-x   1 root root     34172 Oct 11  2005 login
-rwxr-xr-x   1 root root     11768 Sep  5  2005 logname
-rwxr-xr-x   1 root root     71912 Sep  5  2005 ls
-rwxr-xr-x   1 root root      5904 Aug 23  2005 lsattr
-rwxr-xr-x   1 root root      5640 Feb 15  2006 lsmod
-rwxr-xr-x   1 root root    127160 Jun  9  2005 lsof
-rwxr-xr-x   1 root root     23856 Mar 14  2006 lspci
-rwxr-xr-x   1 root root      1081 Jun 10  2005 lspgpot
-rwxr-xr-x   1 root root     88468 Feb 15  2006 mawk
-rwxr-xr-x   1 root root     27884 Sep  5  2005 md5sum
-rwxr-xr-x   1 root root     19968 Sep  5  2005 mkdir
-rwxr-xr-x   1 root root     15196 Sep  5  2005 mkfifo
-rwxr-xr-x   1 root root     18408 Sep  5  2005 mknod
-rwxr-xr-x   1 root root      5212 Aug  8  2005 mktemp
-rwxr-xr-x   1 root root     26348 Sep 20  2005 more
-rwsr-xr-x   1 root root     68496 Sep 20  2005 mount
-rwxrwxr-x   1 root root      5104 Mar 13  2006 mountpoint
-rwsr-xr-x   1 root root     44988 Jul  7  2005 mtr
-rwxr-xr-x   1 root root     55276 Sep  5  2005 mv
-rwxr-xr-x   1 root root      6756 Sep 20  2005 namei
lrwxrwxrwx   1 root root         9 Apr  8  1980 nano -> /bin/nano
lrwxrwxrwx   1 root root        22 Apr  8  1980 nawk -> /etc/alternatives/nawk
-rwxr-xr-x   1 root root     19576 Nov 25  2004 nc
lrwxrwxrwx   1 root root         2 Apr  8  1980 netcat -> nc
-rwxr-xr-x   1 root root     93916 Sep 29  2005 netstat
-rwsr-xr-x   1 root root     22932 Oct 11  2005 newgrp
-rwxr-xr-x   1 root root     14680 Sep  5  2005 nice
-rwxr-xr-x   1 root root     18740 Sep  5  2005 nl
-rwxr-xr-x   1 root root     14012 Sep  5  2005 nohup
-rwxr-xr-x   1 root root     62200 Apr 19  2005 nslookup
-rwxr-xr-x   1 root root     35000 Apr 19  2005 nsupdate
-rwxr-xr-x   1 root root     31420 Sep  5  2005 od
-rwxr-xr-x   1 root root    340480 Mar 28  2006 openssl
-rwsr-xr-x   1 root root     25648 Oct 11  2005 passwd
-rwxr-xr-x   1 root root     15432 Sep  5  2005 paste
-rwxr-xr-x   1 root root     10532 Apr 21  2005 pgrep
-rwxr-xr-x   1 root root   3292724 Mar 17  2006 php
lrwxrwxrwx   1 root root         9 Apr  8  1980 pico -> /bin/nano
lrwxrwxrwx   1 root root        16 Apr  8  1980 pidof -> ../sbin/killall5
-rwsr-xr-x   1 root root     30724 Jul 12  2005 ping
-rwsr-xr-x   1 root root     26556 Jul 12  2005 ping6
lrwxrwxrwx   1 root root         5 Apr  8  1980 pkill -> pgrep
-rwxr-xr-x   1 root root       146 May 27  2005 plog
-rwxr-xr-x   1 root root      2837 May 27  2005 poff
-rwxr-xr-x   1 root root      1625 May 27  2005 pon
-rwxr-xr-x   1 root root     11860 Sep  5  2005 printenv
-rwxr-xr-x   1 root root     20584 Sep  5  2005 printf
-rwxr-xr-x   1 root root     65676 Apr 21  2005 ps
-rwxr-xr-x   1 root root     12668 Sep  5  2005 pwd
lrwxrwxrwx   1 root root         4 Apr  8  1980 rbash -> bash
-rwxr-xr-x   1 root root     12536 Sep  5  2005 readlink
-rwxr-xr-x   1 root root      4584 Sep 20  2005 renice
lrwxrwxrwx   1 root root         4 Apr  8  1980 reset -> tset
-rwxr-xr-x   1 root root      4952 Sep 20  2005 rev
-rwxr-xr-x   1 root root        30 Aug 10  2005 rgrep
-rwxr-xr-x   1 root root     28916 Sep  5  2005 rm
-rwxr-xr-x   1 root root     12984 Sep  5  2005 rmdir
-rwxr-xr-x   1 root root        60 Oct 19  2004 routef
-rwxr-xr-x   1 root root      1262 Oct 19  2004 routel
-rwxr-xr-x   1 root root     10768 Oct  9  2005 rpcinfo
-rwxr-xr-x   1 root root     39007 Mar 28  2006 rrdcgi
-rwxr-xr-x   1 root root      7215 Mar 28  2006 rrdtool
-rwxr-xr-x   1 root root     37529 Mar 28  2006 rrdupdate
-rwxr-xr-x   1 root root    241284 Jul  9  2005 rsync
-rwxr-xr-x   1 root root     10824 Aug  8  2005 run-parts
-rwxr-xr-x   1 root root     33252 Oct 10  2005 scp
-rwxr-xr-x   1 root root      7484 Sep 20  2005 script
-rwxr-xr-x   1 root root     18052 Sep 20  2005 sdiff
-rwxr-xr-x   1 root root     39768 Apr 14  2005 sed
-rwxr-xr-x   1 root root     15392 Sep  5  2005 seq
-rwxr-xr-x   1 root root     10536 Mar 14  2006 setpci
-rwxr-xr-x   1 root root     18192 Sep 20  2005 setterm
-rwxr-xr-x   1 root root     61764 Oct 10  2005 sftp
lrwxrwxrwx   1 root root         6 Apr  8  1980 sg -> newgrp
lrwxrwxrwx   1 root root         4 Apr  8  1980 sh -> bash
-rwxr-xr-x   1 root root     12276 Apr 21  2005 skill
-rwxr-xr-x   1 root root     13920 Sep  5  2005 sleep
lrwxrwxrwx   1 root root         5 Apr  8  1980 snice -> skill
-rwxr-xr-x   1 root root     38816 Sep  5  2005 sort
-rwxr-xr-x   1 root root      9776 Mar 15  2006 spawn-fcgi
-rwxr-xr-x   1 root root     22200 Sep  5  2005 split
-rwxr-xr-x   1 root root    225324 Oct 10  2005 ssh
-rwxr-xr-x   1 root root     69336 Oct 10  2005 ssh-add
-rwxr-sr-x   1 root    108   55828 Oct 10  2005 ssh-agent
-rwxr-xr-x   1 root root     90568 Oct 10  2005 ssh-keygen
-rwxr-xr-x   1 root root    120824 Oct 10  2005 ssh-keyscan
-rwxr-xr-x   1 root root     36936 Sep  5  2005 stty
-rwsr-xr-x   1 root root     23504 Oct 11  2005 su
-rwsr-xr-x   1 root root     93076 Sep 30  2005 sudo
-rwxr-xr-x   1 root root     22184 Sep  5  2005 sum
-rwxr-xr-x   1 root root     11504 Sep  5  2005 sync
-rwxr-xr-x   1 root root     16788 Sep  5  2005 tac
-rwxr-xr-x   1 root root     35428 Sep  5  2005 tail
-rwxr-xr-x   1 root root    189472 Jun 27  2005 tar
-rwxr-xr-x   1 root root     13036 Sep  5  2005 tee
lrwxrwxrwx   1 root root        24 Apr  8  1980 telnet -> /etc/alternatives/telnet
-rwxr-xr-x   1 root root      5612 Aug  8  2005 tempfile
-rwxr-xr-x   1 root root     22096 Sep  5  2005 test
-rwxr-xr-x   1 root root     44200 Aug 22  2005 tic
-rwxr-xr-x   1 root root     28344 Aug 22  2005 toe
-rwxr-xr-x   1 root root     48108 Apr 21  2005 top
-rwxr-xr-x   1 root root     27248 Mar 10  2006 touch
-rwxr-xr-x   1 root root      9312 Aug 22  2005 tput
-rwxr-xr-x   1 root root     27008 Sep  5  2005 tr
-rwxr-xr-x   1 root root     10960 Sep  5  2005 true
-rwxr-xr-x   1 root root     34932 Aug 22  2005 tset
-rwxr-xr-x   1 root root     19544 Sep  5  2005 tsort
-rwxr-xr-x   1 root root     11740 Sep  5  2005 tty
-rwxr-xr-x   1 root root      6915 Oct  9  2005 tzselect
-rwsr-xr-x   1 root root     39184 Sep 20  2005 umount
-rwxr-xr-x   1 root root     12692 Sep  5  2005 uname
-rwxr-xr-x   1 root root     51968 Jul  8  2005 uncompress
-rwxr-xr-x   1 root root     14860 Sep  5  2005 unexpand
-rwxr-xr-x   1 root root     22132 Sep  5  2005 uniq
-rwxr-xr-x   1 root root     12024 Sep  5  2005 unlink
-rwxr-xr-x   1 root root      8335 Jul  5  2005 updatedb
-rwxr-xr-x   1 root root      2972 Apr 21  2005 uptime
-rwxr-xr-x   1 root root     12880 Sep  5  2005 users
lrwxrwxrwx   1 root root         8 Apr  8  1980 vi -> /bin/vim
-rwxr-xr-x   1 root root   1074380 Mar 17  2006 vim
-rwxr-xr-x   1 root root     16468 Apr 21  2005 vmstat
-rwxr-xr-x   1 root root      9068 Mar 14  2006 w
-rwxr-sr-x   1 root tty       9824 Sep 20  2005 wall
-rwxr-xr-x   1 root root      8876 Apr 21  2005 watch
-rwxr-xr-x   1 root root     18652 Sep  5  2005 wc
-rwxr-xr-x   1 root root    250320 Jun 29  2005 wget
-rwxr-xr-x   1 root root      7088 Sep 20  2005 whereis
-rwxr-xr-x   1 root root       884 Mar 13  2006 which
-rwxr-xr-x   1 root root     20616 Sep 15  2005 whiptail
-rwxr-xr-x   1 root root     19716 Sep  5  2005 who
-rwxr-xr-x   1 root root     11836 Sep  5  2005 whoami
-rwxr-xr-x   1 root root     20484 Jul  5  2005 xargs
-rwxr-xr-x   1 root root     11796 Sep  5  2005 yes
-rwxr-xr-x   1 root root     51968 Jul  8  2005 zcat
-rwxr-xr-x   1 root root      8080 Oct  9  2005 zdump
-rwxr-xr-x   1 root root      3015 Jul  8  2005 zgrep
-rwxr-xr-x   1 root root       103 Jul  8  2005 zless

```

### /boot dir ###

```

drwxr-xr-x   2 root root    1024 Apr  8  1980 .
drwxr-xr-x  17 root root    1024 Apr  8  1980 ..
-rw-r--r--   1 root root    7988 Apr 13  2002 boot-bmp.b
-rw-r--r--   1 root root    6204 Apr 13  2002 boot-compat.b
-rw-r--r--   1 root root    7964 Apr 13  2002 boot-menu.b
-rw-r--r--   1 root root    6204 Apr 13  2002 boot-text.b
lrwxrwxrwx   1 root root      11 Apr  8  1980 boot.b -> boot-menu.b
-rw-r--r--   1 root root     728 Apr 13  2002 chain.b
-rw-r--r--   1 root root   32930 Mar 22  2006 config-2.6.16-metrix
lrwxrwxrwx   1 root root      20 Apr  8  1980 grub -> ../lib/grub/i386-pc/
-rw-r--r--   1 root root     656 Apr 13  2002 os2_d.b
-rw-r--r--   1 root root 1076502 Mar 22  2006 vmlinuz-2.6.16-metrix

```

### /etc dir ###

```

drwxr-xr-x  42 root root     3072 Apr  8  1980 .
drwxr-xr-x  17 root root     1024 Apr  8  1980 ..
-rw-------   1 root root        0 Apr 21  2004 .pwd.lock
-rw-r--r--   1 root root     2091 Oct 18  2002 adduser.conf
lrwxrwxrwx   1 root root       15 Apr  8  1980 adjtime -> /rw/etc/adjtime
drwxr-xr-x   3 root root     1024 Apr  8  1980 apm
-rw-r--r--   1 root root      211 Apr  8  2002 bash.bashrc
drwxr-xr-x   2 root root     1024 Apr  8  1980 bind
drwxr-s---   2 root dip      1024 Apr  8  1980 chatscripts
-rw-r--r--   1 root root     3956 Oct  1  2001 checksecurity.conf
drwxr-xr-x   2 root root     1024 Oct  1  2001 cron.d
drwxr-xr-x   2 root root     1024 Apr  8  1980 cron.daily
drwxr-xr-x   2 root root     1024 Apr  8  1980 cron.disabled
drwxr-xr-x   2 root root     1024 Oct 17  2002 cron.monthly
drwxr-xr-x   2 root root     1024 Mar  7  2003 cron.weekly
-rw-r--r--   1 root root      596 Oct  1  2001 crontab
-rw-r--r--   1 root root       72 Sep 22  2005 ddnsu.conf
drwxr-xr-x   2 root root     1024 Apr  8  1980 default
-rw-r--r--   1 root root      336 Mar 24  2002 deluser.conf
-rwxr-xr-x   1 root root     5904 Mar 17  2002 dhclient-script
-rw-r--r--   1 root root     1518 Mar 17  2002 dhclient.conf
-rw-r--r--   1 root root     1337 May 11  2004 dhcpd.conf
-rw-r--r--   1 root root    14990 Apr  8  1980 dnsmasq.conf
-rw-r--r--   1 root root    14196 Oct 19  2005 dnsmasq.conf.example
-rw-r--r--   1 root root     1212 May 10  2004 frottle.conf
-rw-r--r--   1 root root      111 Mar 13  2006 fstab
-rw-r--r--   1 root root       97 Nov 18  2001 gateways
-rw-r--r--   1 root root      488 Mar 14  2006 group
-rw-r--r--   1 root root       26 Sep 26  1995 host.conf
lrwxrwxrwx   1 root root       16 Apr  8  1980 hostname -> /rw/etc/hostname
-rw-r--r--   1 root root       29 Mar 29  2006 hosts
-rw-r--r--   1 root root      603 Oct 17  2002 hosts.allow
-rw-r--r--   1 root root      898 Oct 17  2002 hosts.deny
drwxr-xr-x   2 root root     1024 Apr  8  1980 hotplug
-rw-r--r--   1 root root        0 Mar 23  2006 iftab
-rw-r--r--   1 root root     2161 Jan 21  2005 inetd.conf
drwxr-xr-x   2 root root     1024 Apr  8  1980 init.d
-rw-r--r--   1 root root     2529 Mar 28  2006 inittab
-rw-r--r--   1 root root     2098 Oct 18  2002 inittab.serial
-rw-r--r--   1 root root     2095 Oct 18  2002 inittab.standard
-rw-r--r--   1 root root      422 Aug 22  2001 inputrc
drwxr-xr-x   2 root root     1024 Apr  8  1980 iproute2
lrwxrwxrwx   1 root root       13 Apr  8  1980 issue -> /rw/etc/issue
lrwxrwxrwx   1 root root       17 Apr  8  1980 issue.net -> /rw/etc/issue.net
drwxr-xr-x   3 root root     1024 Apr  8  1980 joe
-rw-r--r--   1 root root       53 Jan  2  2003 kernel-img.conf
-rw-r--r--   1 root root     5255 Mar 28  2006 ld.so.cache
-rw-r--r--   1 root root    11382 Mar 17  2006 lighttpd.conf
lrwxrwxrwx   1 root root       23 Apr  8  1980 localtime -> /usr/share/zoneinfo/UTC
drwxr-xr-x   3 root root     1024 Apr  8  1980 logcheck
-rw-r--r--   1 root root     9813 Feb  2  2005 login.defs
-rw-r--r--   1 root root      586 Mar  7  2003 logrotate.conf
drwxr-xr-x   2 root root     1024 Apr  8  1980 logrotate.d
-rw-r--r--   1 root root   135289 Jan 26  2002 lynx.cfg
-rw-r--r--   1 root root       13 Nov 10  2005 metrix.version
-rw-r--r--   1 root root     1251 Mar 23  2006 modules
-rw-r--r--   1 root root     3228 Nov 30  2004 modules.conf
-rw-r--r--   1 root root      439 Oct 18  2002 modules.net4501
-rw-r--r--   1 root root      440 Dec 10  2002 modules.net4521
-rw-r--r--   1 root root      447 Apr 21  2004 modules.net4526
-rw-r--r--   1 root root      449 Feb  8  2005 modules.pcmcia
-rw-r--r--   1 root root      439 Oct 18  2002 modules.t23
drwxr-xr-x   3 root root     1024 Apr  8  1980 modutils
-rw-r--r--   1 root root      279 Mar 29  2006 motd
lrwxrwxrwx   1 root root       12 Apr  8  1980 mtab -> /proc/mounts
drwxr-xr-x   7 root root     1024 Jan  1 00:03 network
-rw-r--r--   1 root root      465 Mar 11  1999 nsswitch.conf
-rw-r--r--   1 root root      129 May 16  2003 ntp.conf
-rw-r--r--   1 root root      909 Mar 29  2006 olsrd.conf
drwxr-xr-x   2 root root     1024 Apr  8  1980 openntpd
drwxrwxrwx   4 root root     1024 Apr  8  1980 openvpn
-rw-r--r--   1 root root      534 May 16  2001 pam.conf
drwxr-xr-x   2 root root     1024 Apr  8  1980 pam.d
-rw-r--r--   1 root root     1246 Aug 18  2005 passwd
drwxr-xr-x   3 root root     1024 Apr  8  1980 pcmcia
-rw-r--r--   1 root root    41317 Mar 17  2006 php.ini
drwxr-xr-x   5 root root     1024 Apr  8  1980 ppp
-rw-r--r--   1 root root      293 Mar 14  2006 profile
-rw-r--r--   1 root root     1748 Nov 18  2001 protocols
drwxr-xr-x   2 root root     1024 May 29  2002 rc.boot
drwxr-xr-x   2 root root     1024 Apr  8  1980 rc0.d
drwxr-xr-x   2 root root     1024 Apr  8  1980 rc1.d
drwxr-xr-x   2 root root     1024 Apr  8  1980 rc2.d
drwxr-xr-x   2 root root     1024 Apr  8  1980 rc3.d
drwxr-xr-x   2 root root     1024 Apr  8  1980 rc4.d
drwxr-xr-x   2 root root     1024 Apr  8  1980 rc5.d
drwxr-xr-x   2 root root     1024 Apr  8  1980 rc6.d
drwxr-xr-x   2 root root     1024 Apr  8  1980 rcS.d
lrwxrwxrwx   1 root root       14 Apr  8  1980 resolv -> /rw/etc/resolv
lrwxrwxrwx   1 root root       19 Apr  8  1980 resolv.conf -> /rw/etc/resolv.conf
-rwxr-xr-x   1 root root      268 Feb  1  2002 rmt
-rw-r--r--   1 root root      862 Nov 18  2001 rpc
-rw-r--r--   1 root root      402 Nov 30  2004 securetty
drwxr-xr-x   2 root root     1024 Apr  8  1980 security
-rw-r--r--   1 root root    16513 Jan 21  2005 services
-rw-r-----   1 root shadow    785 Apr  8  1980 shadow
-rw-------   1 root root      786 Aug 18  2005 shadow-
-rw-r--r--   1 root root      185 Apr  7  2002 shells
drwxr-xr-x   2 root root     1024 Apr  8  1980 skel
drwxr-xr-x   2 root root     1024 Apr  8  1980 ssh
drwxr-xr-x   2 root root     1024 Apr  8  1980 ssl
-r--r-----   1 root root       73 Jan 26  2005 sudoers
-rw-r--r--   1 root root      185 Oct 27  2001 sysctl.conf
-rw-r--r--   1 root root     3629 Jan 25  2005 syslog-ng.conf.sample
-rw-r--r--   1 root root      861 Aug  8  2003 syslog.conf
drwxr-xr-x  12 root root     1024 Apr  8  1980 terminfo
drwxr-xr-x   2 root root     1024 Apr  8  1980 thttpd
drwxr-xr-x   4 root root     1024 Apr  8  1980 udev
drwxr-xr-x   2 root root     1024 Apr  8  1980 udhcpc
drwxr-xr-x   2 root root     1024 Apr  8  1980 udhcpd
-rw-r--r--   1 root root      397 Jun  2  2001 updatedb.conf
-rw-r--r--   1 root root     1036 Dec 16  2004 watchdog.conf
-rw-r--r--   1 root root     4002 Apr 14  2002 wgetrc
-rw-r--r--   1 www  root      141 Apr  8  1980 wifiadmin.passwd
lrwxrwxrwx   1 root root       14 Apr  8  1980 zebra -> /usr/local/etc

```
