#!/bin/bash
# Controle du serveur
# A mettre dans le sudoers par www-data
#

if test $# != 4 -a $# != 3 -a $# != 2; then
	echo "Usage : serverctl.sh <type> <param1> [<param2> <param3>]";
	exit 1
fi
TYPE=$1
PARAM1=$2
PARAM2=$3
PARAM3=$4

DIR=$(cd $(dirname $0) && pwd)


# Selon le type
case "$TYPE" in 

	## Droits et deplacement des scans reduits
	scanmove)
	
		FILE=$PARAM1
		FILEREDUIT=$PARAM2
		OWNER=$PARAM3
		/bin/rm $FILE
		/bin/mv $FILEREDUIT $FILE
		/bin/chown $OWNER:$OWNER $FILE
		/bin/chmod 774 $FILE
		;;

	## Creation du .ok pour les scans
	scantouch)
	
		FILEOK=$PARAM1
		OWNER=$PARAM2
		/bin/touch $FILEOK
		/bin/chown $OWNER:$OWNER $FILEOK
		/bin/chmod 777 $FILEOK
		;;

	## Copie script crontab
	crontab)
	
		if [[ "$PARAM1" != "" ]] && [[ "$PARAM2" != "" ]]; then
			CRONFILE=$PARAM1
			CRONFILEDEST=/etc/cron.d/$PARAM2
			/bin/chown root:root $CRONFILE
			/bin/mv -f $CRONFILE $CRONFILEDEST
			/bin/chmod 644 $CRONFILEDEST
		else
			echo "[serverctl.sh] arguments manquants"
			exit 3
		fi
		;;

	crontabcomment)

		CRONFILE=$PARAM1
		PATTERN=$PARAM2
		if [[ "$PATTERN" != "" ]]; then
			sed -e "/$PATTERN/ s/^#*/#`date +%Y%m%d-%H%M` /" -i /etc/crontab
		fi
		;;

	## Suppr script crontab
	crontabdel)
		if [[ "$PARAM1" != "" ]]; then
			CRONFILEDEST=/etc/cron.d/$PARAM1
			/bin/rm -f $CRONFILEDEST
		else
			echo "[serverctl.sh] argument manquant"
			exit 3
		fi
		;;
			
	## Arret/démarrage de service
	service) 
		
		SERVICE=$PARAM1
		if test ! -f $SERVICE; then
			echo "[serverctl.sh] Le service $PARAM1 n'existe pas"
			exit 3
		fi
		
		case "$PARAM2" in
			start)
				$SERVICE start 2>&1
				;;
			stop)
				$SERVICE stop 2>&1
				;;
			restart)
				$SERVICE restart 2>&1
				;;
   			graceful)
				apache2ctl -k graceful 2>&1
   			    ;;
			
			*)
				echo "[serverctl.sh] choix possibles : start stop restart";
				exit 4
				;;
		esac		
		;;
		
	firewall)
		IPTB=$(which iptables)
		case "$PARAM1" in
			installed)
				which iptables
				if test $? = 1; then
					echo "[serverctl.sh] firewall non installé"
					exit 5
				else 
					echo "[serverctl.sh] firewall installé"
				fi
				;;
				
			status)
				$IPTB -L -v
				;;
				
			running) 
				NB=$($IPTB -L -v | wc -l)
				if test $NB = 8; then
					echo "[serverctl.sh] firewall non lancé"
					exit 1
				else 
					echo "[serverctl.sh] firewall lancé"
					exit 0
				fi
				;;
			*)
				;;
		esac
		;;
	fetchmail)
		FETCHMAIL=$(which fetchmail)
		case "$PARAM1" in
			check)
				if test -f /etc/fetchmailrc; then
					$FETCHMAIL -c -f /etc/fetchmailrc 2>&1
					RES=$?
					if test $RES = 0 -o $RES = 1; then
						exit 0
					else
						exit $RES
					fi
				else
					exit 1
				fi					
				;;
			*)
				;;
		esac
		;;
	install)
		case "$PARAM1" in
			openoffice)
				script="$DIR/install/openoffice.sh"
				if test -f "$script"; then
					/bin/bash ${script}
				else 
					echo "script ${script} manquant"
				fi
				;;
			logrotate)
				script="$DIR/install/logrotate.sh"
				if test -f "$script"; then
					/bin/bash ${script}
				else 
					echo "script ${script} manquant"
				fi
				;;
			toolbox)
				script="$DIR/install/toolbox.sh"
				if test -f "$script"; then
					/bin/bash ${script}
				else 
					echo "script ${script} manquant"
				fi
				;;
			*)
				exit 1
				;;
		esac
		;;
		
	telemaintenance)
		RSAFILE=$PARAM1
		NIVDROIT=$PARAM2
		USER="apache"
		if [[ -f /etc/debian_version ]]; then
			USER="www-data"
		fi	
		/bin/chown $USER:$USER $RSAFILE 
		/bin/chmod $NIVDROIT $RSAFILE 
		;;
			
	*)
		echo "[serverctl.sh] Type non supporté"
		exit 2
		;;
esac
exit 0
