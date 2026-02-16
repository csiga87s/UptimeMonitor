#!/bin/bash

DOMAIN=""
TOKEN=""
LOG_FILE="$HOME/duckdns/duck.log"
IP_FILE="$HOME/duckdns/last_ip.log"

# aktuális ip cím lekérése
CURRENT_IP=$(curl -s https://ifconfig.me)
# ha nem ip formát érkezett, akkor logolja a hibát és kilép
if [[ ! $CURRENT_IP =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - HIBA: Érvénytelen IP érkezett ($CURRENT_IP). Megszakítás." >> "$LOG_FILE"
    exit 1
fi
# logfileból kiolvassa az ip-t
if [ -f "$IP_FILE" ]; then 
    LAST_IP=$(cat "$IP_FILE") 
else 
    LAST_IP="" 
fi

# összehasonlítja a lekért és korábban log-ot IP-t
#csak akkor frissíti, megváltozott az IP
if [ "$CURRENT_IP" != "$LAST_IP" ]; then

    RESPONSE=$(curl -s "https://www.duckdns.org/update?domains=$DOMAIN&token=$TOKEN&ip=$CURRENT_IP")

    if [ "$RESPONSE" == "OK" ]; then    
        echo "$(date '+%Y-%m-%d %H:%M:%S') - IP változás: $LAST_IP -> $CURRENT_IP - Válasz: $RESPONSE" >> "$LOG_FILE"
        echo "$CURRENT_IP" > "$IP_FILE"
    else
        echo "$(date '+%Y-%m-%d %H:%M:%S') - DuckDNS HIBA: $RESPONSE (IP nem lett frissítve a fájlban)" >> "$LOG_FILE"
    fi
    tail -n 30 "$LOG_FILE" > "$LOG_FILE.tmp" && mv "$LOG_FILE.tmp" "$LOG_FILE"
    echo "" >> "$LOG_FILE"    
fi
#else
    # ha nem változott az ip, nincs teendő
    #echo -n "$(date '+%Y-%m-%d %H:%M:%S') - Not changed." >> "$LOG_FILE"
    #tail -n 100 "$LOG_FILE" > "$LOG_FILE.tmp" && mv "$LOG_FILE.tmp" "$LOG_FILE"
    #echo "" >> "$LOG_FILE"
#fi