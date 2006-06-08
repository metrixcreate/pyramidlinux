#!/bin/sh -e
# /etc/init.d/djbdns : start or stop the dns subsystem
# written by Adam McKenna
# <adam@flounder.net>

case "$1" in
    start)
	echo -n "Starting djbdns: " 
	for i in `ls -d /var/lib/svscan/dnscache* /var/lib/svscan/tinydns* /var/lib/svscan/axfrdns* 2>/dev/null`; do
		svc -u "$i"
		svc -u "$i/log"
		echo -n "${i#/var/lib/svscan/} "
	done
	echo "."
        ;;
    stop)
	echo -n "Stopping djbdns: "
	for i in `ls -d /var/lib/svscan/dnscache* /var/lib/svscan/tinydns* /var/lib/svscan/axfrdns* 2>/dev/null`; do
		svc -d "$i"
		svc -d "$i/log"
		echo -n "${i#/var/lib/svscan/} "
	done
	echo "."
	;;
    restart)
        $0 stop
        $0 start
        ;;
    *)
        echo 'Usage: /etc/init.d/dnscache {start|stop|restart}'
        exit 1
esac

exit 0
