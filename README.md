# elovalasztok2018
Ez a 2018 -as előválasztásra készülő Joomla 3 alapú szoftver

A php fájlok joomla JUMI modulok vagy componensek

A projekt jelenleg nem többnyelvű, a magyar szövegkonstansok fixen be vannak írva a kódokba

Telepités:
Normál Joomla 3 telepités

. JUMI, bfstop, eprivacy, jcomments, jce, jhackguards, akaebabackup kiegészitések telepitése a jooma extension könyvtárból, ezek konfigurálása

. com_adalogin kiegészitő telepitése (github repoból) és konfigurálása

. A jelen repoban szerepló alkönyvtárak és fájlok feltöltése joomla_root/elovalasztok könyvtárba

. Joomla JUMI modulok és JUMI komponens hivások kialakitása a joomla admin felületen. cimkefelho JUMI modul, gombok JUMI modul,  oevklist JUMI component, szavazok JUMI component

. Dizájn kialakitása - joomla template, css, images stb

<<<<<<< HEAD
Biztonsági megjegyzések
-----------------------

1. A joomla rendszer NE ROOT jogú mysql loginnal müködjön, a joomla által használt mysql loginnak NE LEGYEN JOGA triggert felvinni, törölni, létrehozni!
2. A szerver rendszergazdának más eszközzel (linux konzol + mysql parancssor) kell a biztonsági triggereket telepítenie
3. A rendszer adminisztrátorok erős jelszavakat használjanak, azt gyakran modosítsák és biztonságosan kezeljék
4. A szerveren a php fájlok irását, modosítását, törlését a rendszer telepítése után az appache user számára le kell tiltani
5. A joomla jogosultsági rendszert gondosan konfigurálni kell (csak a joomla adminok vihetnek fel, modosithatnak, törölhetnek cikk-kategoriákat és cikkeket)
6. A joomla által használt mysql jelszót időszakonként módosítani kell.
7. a mysql szerver ne legyen külső URL -ről elérhető
8. A joomla_root/administrator/index.php üzemszerüen ne legyen az appache user által elérhető (olvasásra sem)



Biztonsági megoldások a programban.
-----------------------------------

Szavazás inditása, lezárása
	Joomla admin login szükséges hozzá. 
	Joomla beépített CSR védelem aktív, 
	Joomla bfoorce védelem aktív,
	Esetenként linux root user és linux konzol belépés szükséges (config.php file modositás)
	
Jelölt felvitele, jelölt adatok modosítása
	ADA login és joomla login jogosultság kell hozzá,
	Joomla login szükséges hozzá. 
	Joomla bfoorce védelem aktív,
	Joomla beépített CSR védelem és hozzáférés szabályozás aktív 

Szavazat beküldése
	ADA login, területi tanusitvány, és joomla login jogosultság kell hozzá,
	Joomla login szükséges hozzá, (automatikusan létrejön, de admin letilhatja), 
	Joomla beépített CSR védelem aktív,
	Joomla bfoorce védelem aktív,
    Egyedi mysql-trigger -ben megvalósított CSR védelem, ami biztosítja, hogy egy user csak egyszer szavazhat	
	
Szavazat törlése
	ADA login, területi tanusitvány, és joomla login jogosultság kell hozzá,
	Joomla login szükséges hozzá, (automatikusan létrejön, de admin letilhatja), 
	Joomla beépített CSR védelem aktív,
	Joomla bfoorce védelem aktív,
	Egyedi képernyőre kiirt és cookiban tárolt random szavazat specifikus biztonsági kód, ami szükséges aszavazat törléséhez, 
    Egyedi mysql-trigger -ben megvalósított a fenti random szavazat specifikus biztonsági kód alapú ellenörzés

Szavazat modosítása
    A programba ilyen funkció nincs beépítve,
	Egyedi mysql alapú trigger megakadályozza, hogy esetleg más uton ilyen mysql tranzakció történjen.
	
	
	
	


 


=======
>>>>>>> f50828945db4e68422012014f1ae5575a52444c0


