# AUTOLOADER FIX - MANUAL INSTRUCTIONS

## Problem
The composer autoloader cache on the server is stale and doesn't recognize `App\Models\Product`.

## Solution Options

### OPTION 1: Via CPanel Terminal (RECOMMENDED)
1. Log into CPanel: https://da15.host-ww.net:2083
2. Go to **Terminal** (under Advanced)
3. Run these commands:
   ```
   cd /home/relearnc/domains/relearn.co.ke/relearn
   composer dump-autoload --no-dev
   ```

### OPTION 2: Via SSH
```bash
ssh relearnc@102.209.117.98
cd /domains/relearn.co.ke/relearn
composer dump-autoload
```

### OPTION 3: Via FTP + Browser Script
1. Upload the included fix.php file to `/public_html/` via FTP
2. Visit: https://www.relearn.co.ke/fix.php
3. Let it run and see the output
4. Delete the file afterward

### OPTION 4: Contact Hosting Support
Ask your host to run:
```
cd /domains/relearn.co.ke/relearn && composer dump-autoload
```

## What's the Issue?
- Local files are correct (Product.php exists)
- Server's vendor/composer/autoload_classmap.php is outdated
- It needs to be regenerated with `composer dump-autoload`

## Verification
After running one of the solutions, visit:
https://www.relearn.co.ke/
Should work without "Class not found" error.

