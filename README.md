# 🚀 PHP UpTime Monitor Projekt

Saját fejlesztésű szerverfigyelő alkalmazás, amely segít nyomon követni weboldalak elérhetőségét. Egy automatizált monitorozó rendszer LAMP környezetben.

## 📋 Főbb funkciók

- **Automatizált ellenőrzés:** Ubuntu szerveren futó Cron job segítségével a rendszer 5 percenként méri a weboldalak állapotát és válaszidejét.
- **Dinamikus Dashboard:** Átlátható felület, a monitorozott oldalak aktuális státuszával, válaszidejével és az utolsó ellenőrzés időpontjával.
- **30 napos statisztika:** Adatok az elérhetőségekről, arányról (Uptime %) és az átlagos válaszidőről.
- **Hibanapló (Downtime Log):** Lista a korábbi leállásokról és a hozzájuk tartozó HTTP hibakódokról.
- **Archiválási logika:** A figyel oldalak adatai 30 napig érhetőek el, a törölt oldalak mérési adatai archiválódnak, 60 napig érhetőek el.
- **DuckDNS támogatás:** Tartalmaz egy Bash scriptet a dinamikus IP-cím frissítéséhez, hibakezeléssel és naplózással kiegészítve.

## 🛠️ Technológiai stack

- **Backend:** PHP (PDO, cURL)
- **Adatbázis:** MySQL / MariaDB 
- **Frontend:** HTML5, CSS3, Bootstrap 5 
- **Operációs rendszer:** Ubuntu Server
- **Verziókezelés:** Git

## 📦 Telepítés és használat

1. Klónozd a tárolót.
2. Hozd létre az adatbázist a mellékelt `database_schema.sql` alapján.
3. Nevezd át a `db-connect.sample.php` fájlt `db-connect.php`-ra, és add meg a MySQL hozzáféréseidet.
4. Állítsd be a Cron jobot a `monitor.php` futtatásához.

## ⏰ Időzített feladatok beállítása (Cron)

Az automatikus működéséhez add hozzá az alábbi sorokat a szerver `crontab`-jához (`crontab -e` parancs):

```bash
# Weboldalak ellenőrzése 5 percenként
*/5 * * * * /usr/bin/php /var/www/html/UptimeMonitor/php/monitor.php >> /var/www/html/UptimeMonitor/log/monitor.log 2>&1

# Opcionális: 
# DuckDNS IP frissítés 5 percenként, a HOME könyvtárba raktam, de opcionális
*/5 * * * * /bin/bash ~/duckdns/duck.sh >> ~/duckdns/duck.log 2>&1
```
## 🚀 Tervezett fejlesztések (Roadmap)

A projektben az alábbi funkciók implementálása várható a következő verziókban:

1. **Archív hibanapló:** Jelenleg az archivált adatoknál csak az összesített statisztika látható. Várható további nézet, ahol a törölt oldalak  hibakódjai is láthatóak, jelenleg csak az látszik h hány hibakód volt az adott időszakban.

Update (2026.02.18): Az archív adatok között is megtekinthető, hogy a korábban figyelt időszakban mikor és mi volt a hikód.

2. **Automatikus értesítési rendszer:** Telegram Bot vagy E-mail integráció, amely azonnali riasztást küld, ha egy figyelt oldal két egymást követő mérés során is elérhetetlen.

---
*Készítette: [Trautmann Dávid/csiga87s]*

