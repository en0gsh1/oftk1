# Si të instaloni PHP në Windows (që të punojë hyrja në llogari)

PHP **nuk instalohet në shfletues**. Instalohet në kompjuterin tuaj; pastaj nisni një "server" të vogël dhe hapni faqet në shfletues – atëherë PHP ekzekutohet dhe hyrja në llogari punon.

---

## Hapi 1: Shkarkoni PHP

1. Hapni: **https://windows.php.net/download/**
2. Zgjidhni versionin më të fundit **VS16 x64 Non Thread Safe** (p.sh. **PHP 8.3**) – klikoni "Zip" për ta shkarkuar.
3. Shkarkoni edhe **Visual C++ Redistributable** nëse nuk e keni (lidhja është në të njëjtën faqe) – duhet për të ekzekutuar PHP.

---

## Hapi 2: Çpakoni PHP

1. Hapni skedarin ZIP që shkarkuat.
2. Çpakoni të gjitha skedarët në një dosje, p.sh.:
   - `C:\php`
   Ose në Desktop:
   - `C:\Users\enoga\Desktop\php`

Mbrenda do të keni skedarë si `php.exe`, `php.ini-development`, etj.

---

## Hapi 3: Vendosni PHP në PATH (që të njohet nga sistemi)

1. Shtypni **Windows + R**, shkruani `sysdm.cpl` dhe Enter.
2. Skeda **"Advanced"** → **"Environment Variables"**.
3. Nën **"System variables"** zgjidhni **"Path"** → **"Edit"**.
4. **"New"** dhe shtoni rrugën ku çpakoni PHP, p.sh.:
   - `C:\php`
   ose
   - `C:\Users\enoga\Desktop\php`
5. **OK** në të gjitha dritaret.

**Mbyllni dhe hapni përsëri Command Prompt** (ose PowerShell) që ndryshimi të aplikohet.

---

## Hapi 4: Kontrolloni që PHP është instaluar

1. Hapni **Command Prompt** (shtypni `cmd` në Start).
2. Shkruani:
   ```
   php -v
   ```
3. Duhet të shfaqet diçka si: `PHP 8.3.x ...`  
   Nëse shfaqet "php is not recognized", kthehuni te Hapi 3 dhe kontrolloni PATH.

---

## Hapi 5: Nisni serverin dhe hapni faqet në shfletues

1. Në Command Prompt, shkoni te dosja e projektit:
   ```
   cd "C:\Users\enoga\OneDrive\Desktop\oda e babait"
   ```
2. Nisni serverin PHP:
   ```
   php -S localhost:8000
   ```
3. Lëreni këtë dritare të hapur (mos e mbyllni).
4. Hapni **shfletuesin** (Chrome, Edge, Firefox) dhe shkruani në adresë:
   ```
   http://localhost:8000
   ```
5. Klikoni **"Hyr në Llogari"** ose hapni direkt:
   ```
   http://localhost:8000/install.php
   ```
   (së pari krijoni llogarinë me install.php, pastaj http://localhost:8000/login.php për të hyrë.)

---

## Përmbledhje

| Çfarë bëni        | Ku                         |
|-------------------|----------------------------|
| Instaloni PHP     | Në Windows (jo në browser) |
| Nisni serverin    | Command Prompt: `php -S localhost:8000` |
| Hapni faqet       | Në browser: http://localhost:8000       |

Kur e mbyllni Command Prompt-un, serveri ndalet. Herën tjetër përsëri: `cd` te dosja e projektit dhe `php -S localhost:8000`, pastaj http://localhost:8000 në shfletues.
