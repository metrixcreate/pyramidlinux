#! /bin/sh -e
#
# Map cdrom, cd-r, cdrw, dvd, dvdrw, dvdram, cdm, cdmrw, cdram
# to suitable devices.
# Prefers cd* for DVD-incapable and cdrom and dvd for read-only devices.
# First parameter is the kernel device name.
# Second parameter, if present, must be "-d" => output the full mapping.
#
# Usage:
# BUS="ide", KERNEL="hd[a-z]", PROGRAM="/etc/udev/cdsymlinks.sh %k", SYMLINK="%c{1} %c{2} %c{3} %c{4} %c{5} %c{6}"
# BUS="scsi", KERNEL="sr[0-9]*", PROGRAM="/etc/udev/cdsymlinks.sh %k", SYMLINK="%c{1} %c{2} %c{3} %c{4} %c{5} %c{6}"
# BUS="scsi", KERNEL="scd[0-9]*", PROGRAM="/etc/udev/cdsymlinks.sh %k", SYMLINK="%c{1} %c{2} %c{3} %c{4} %c{5} %c{6}"
# (this last one is "just in case")
#
# (c) 2004, 2005 Darren Salt <linux@youmustbejoking.demon.co.uk>
#
# Last modified: 2005-02-15

test -e /proc/sys/dev/cdrom/info || exit 0

# Defaults; it's better that you alter them in /etc/udev/cdsymlinks.conf
OUTPUT='CD CDRW DVD DVDRW DVDRAM'
NUMBERED_LINKS=1
LINK_ZERO=0

test -e /etc/udev/cdsymlinks.conf && . /etc/udev/cdsymlinks.conf

DEBUG=''
test "$2" = '-d' && DEBUG=1


# Array emulation. 'setArray FOO a b c' gives FOO=3 FOO_1=a FOO_2=b FOO_3=c
setArray () {
  local _ARRAY=$1
  local _INDEX=0
  shift
  while test $# -ne 0; do
    eval ${_ARRAY}_$_INDEX="$1"
    _INDEX=$(($_INDEX+1))
    shift
  done
  eval $_ARRAY=$_INDEX
}

ix () { eval echo -n \$$1_$2; }
ixs () { eval $1_$2="$3"; }

# Args: variable, value
# Returns true if value is not in variable (a whitespace-separated list)
notin () {
  test "$2" = '' && return 0
  local i
  for i in `eval echo '$'$1`; do
    test "$i" != "$2" || return 1
  done
}


# Scan /proc/sys/dev/cdrom/info for details on the present devices
setArray DEVICES `grep -i '^drive name:'        /proc/sys/dev/cdrom/info | sed 's/.*://'`
setArray DVDRAMs `grep -i '^Can write DVD-RAM:' /proc/sys/dev/cdrom/info | sed 's/.*://'`
setArray DVDRWs  `grep -i '^Can write DVD-R:'   /proc/sys/dev/cdrom/info | sed 's/.*://'`
setArray DVDs    `grep -i '^Can read DVD:'      /proc/sys/dev/cdrom/info | sed 's/.*://'`
setArray CDRWs   `grep -i '^Can write CD-RW:'   /proc/sys/dev/cdrom/info | sed 's/.*://'`
setArray CDRs    `grep -i '^Can write CD-R:'    /proc/sys/dev/cdrom/info | sed 's/.*://'`
setArray CDMRWs  `grep -i '^Can write MRW:'     /proc/sys/dev/cdrom/info | sed 's/.*://'`
setArray CDMs    `grep -i '^Can read MRW:'      /proc/sys/dev/cdrom/info | sed 's/.*://'`
setArray CDRAMs  `grep -i '^Can write RAM:'     /proc/sys/dev/cdrom/info | sed 's/.*://'`

# How many devices do we have?
NumDevs=$(($DEVICES-1))
Count=''
i=-1
while test $i -ne $NumDevs; do
  i=$(($i+1));
  Count="$i${Count:+ }$Count";
done

# Fill in any missing capabilities information (assume not capable)
for i in $Count; do
  test "`ix DVDRAMs $i`" != '' || ixs DVDRAMs $i 0
  test "`ix DVDRWs $i`"  != '' || ixs DVDRWs $i 0
  test "`ix DVDs $i`"    != '' || ixs DVDs $i 0
  test "`ix CDRWs $i`"   != '' || ixs CDRWs $i 0
  test "`ix CDRs $i`"    != '' || ixs CDRs $i 0
  test "`ix CDMRWs $i`"  != '' || ixs CDMRWs $i 0
  test "`ix CDMs $i`"    != '' || ixs CDMs $i 0
  test "`ix CDRAMs $i`"  != '' || ixs CDRAMs $i 0
done

DVDRAM=''
DVDRW=''
DVD=''
CDRW=''
CDR=''
CDMRW=''
CDM=''
CDRAM=''
CD=''

# Calculate symlink->device mappings.
# We need to be careful about how we assign mappings because we want
# read-only devices to have /dev/cdrom and /dev/dvd.
# Hot-plugged CD/DVD devices may well cause this scheme some problems.
for i in $Count; do
  test "`ix DVDRAMs $i`" = 1 &&	DVDRAM="$DVDRAM `ix DEVICES $i`"
done
for i in $Count; do
  test "`ix DVDRWs $i`"	= 1 &&	DVDRW="$DVDRW `ix DEVICES $i`"
done
for i in $Count; do
  test "`ix DVDs $i`" = 1 &&	DVD="$DVD `ix DEVICES $i`"
done
for i in $Count; do
  test "`ix CDRWs $i`" = 1 &&	CDRW="$CDRW `ix DEVICES $i`"
done
for i in $Count; do
  test "`ix CDRs $i`" = 1 &&	CDR="$CDR `ix DEVICES $i`"
done
for i in $Count; do
  test "`ix CDMRWs $i`" = 1 &&	CDMRW="$CDMRW `ix DEVICES $i`"
done
for i in $Count; do
  test "`ix CDMs $i`" = 1 &&	CDM="$CDM `ix DEVICES $i`"
done
for i in $Count; do
  test "`ix CDRAMs $i`" = 1 &&	CDRAM="$CDRAM `ix DEVICES $i`"
done
for i in $Count; do
				CD="$CD `ix DEVICES $i`"
done

# Debug output
if test "$DEBUG" = 1; then
  echo 'Devices:' `for i in $Count; do ix DEVICES $i; echo -n \ ; done`
  echo 'CDROM  : (all)' $CD
  echo 'CD-R   :' `for i in $Count; do ix CDRs    $i; echo -n \ ; done` $CDR
  echo 'CDRW   :' `for i in $Count; do ix CDRWs   $i; echo -n \ ; done` $CDRW
  echo 'DVD    :' `for i in $Count; do ix DVDs    $i; echo -n \ ; done` $DVD
  echo 'DVDRW  :' `for i in $Count; do ix DVDRWs  $i; echo -n \ ; done` $DVDRW
  echo 'DVDRAM :' `for i in $Count; do ix DVDRAMs $i; echo -n \ ; done` $DVDRAM
  echo 'CDMRW  :' `for i in $Count; do ix CDMRWs  $i; echo -n \ ; done` $CDMRW
  echo 'CDWMRW :' `for i in $Count; do ix CDMs    $i; echo -n \ ; done` $CDM
  echo 'CDRAM  :' `for i in $Count; do ix CDRAMs  $i; echo -n \ ; done` $CDRAM
fi

# Prepare symlink names output
do_output () {
  test "`eval echo '$'$3`" = '' && return
  local i
  local COUNT=$4
  local DEVLS="`ls -dl \"/dev/$2\" \"/dev/$2\"[0-9]* 2>/dev/null`"
  local PRESENT="`echo "$DEVLS" |
    sed -re 's!^.* /dev/('$2'[[:digit:]]*) -> [^[:space:]]+$!\1!'`"
  for i in `eval echo '$'$3`; do
    # First, we look for existing symlinks to the target device.
    local DEVPRESENT="`echo "$DEVLS" |
      sed -re 's!^.* /dev/('$2'[[:digit:]]*) -> '$i'$!\1!; t X; d; :X'`"
    if test "$DEVPRESENT" != ""; then
      # Existing symlinks found - don't output a new one.
      # If the target dev ($1) is the current dev ($i), we output their names.
      test -z "$4" || return;
      test "$1" = "$i" && echo " $DEVPRESENT" | sed -e 'N; $ s/\n/ /'
    else
      # If we found no existing symlinks for the target device...
      # Find the next available (not present) symlink name.
      # We always need to do this for reasons of output consistency: if a
      # symlink is created by udev as a result of use of this program, we
      # DON'T want different output!
      until notin PRESENT "$2$COUNT"; do
        test -z "$4" || return;
	COUNT=$(($COUNT+1))
      done
      # If the target dev ($1) is the current dev ($i), we output its name.
      if test $(($NUMBERED_LINKS)) -ne 0 || test -z "$COUNT"; then
	test "$i" != "$1" || echo -n " $2$COUNT"
      fi
      # If the link isn't in our "existing links" list, add it and increment
      # our counter.
      if notin PRESENT "$2$COUNT"; then
        test -z "$4" || return;
	PRESENT="$PRESENT\n$2$COUNT"
	COUNT=$(($COUNT+1))
      fi
    fi
  done
}

output () {
  do_output "$@"
  test $(($LINK_ZERO)) -eq 0 || do_output "$@" 0
}

# And output it
notin OUTPUT CD     || echo -n "`output "$1" cdrom CD`"
notin OUTPUT CDR    || echo -n "`output "$1" cd-r CDR`"
notin OUTPUT CDRW   || echo -n "`output "$1" cdrw CDRW`"
notin OUTPUT DVD    || echo -n "`output "$1" dvd DVD`"
notin OUTPUT DVDRW  || echo -n "`output "$1" dvdrw DVDRW`"
notin OUTPUT DVDRAM || echo -n "`output "$1" dvdram DVDRAM`"
notin OUTPUT CDMRW  || echo -n "`output "$1" cdmrw CDM`"
notin OUTPUT CDWMRW || echo -n "`output "$1" cdwmrw CDMRW`"
notin OUTPUT CDRAM  || echo -n "`output "$1" cdram CDRAM`"
echo
