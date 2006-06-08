#!/bin/sh

DAEMON="/sbin/dhcrelay"
NAME="dhcrelay"

if ( [ ! -x "$DAEMON" ] ); then
  echo "Can't execute $DAEMON, exiting."
  exit 1
fi

case "$1" in
  start)
    for i in /etc/default/$NAME.*; do
      . $i
      echo "Starting $NAME on $INTERFACE"
      $DAEMON -i $INTERFACE $SERVER > /dev/null 2>&1
    done
    ;;
  stop)
    echo -n "Stopping $NAME: "
    killall $NAME
    echo "$NAME. "
    ;;
  restart)
    echo -n "Restarting $NAME: "
    $0 stop > /dev/null 2>&1
    sleep 1
    $0 start > /dev/null 2>&1
    echo "$NAME. "
    exit 0;
    ;;
  *)
    echo "Usage: $0 {start|stop|restart}"
    exit 1
    ;;
esac

exit 0
