================================================================================
CPANEL IMAGE UPLOAD INSTRUCTIONS
================================================================================

This directory contains pre-generated event photo galleries for Events #10, #11, #12, and #13.
Each event has 4 high-definition multi-angle photos matching the database entries.

HOW TO UPLOAD TO CPANEL:
--------------------------------------------------------------------------------
1. Log in to your cPanel.
2. Open "File Manager".
3. Navigate to your website root directory:
   - For a primary domain: `public_html/storage/`
   - Also ensure `storage/app/public/` exists.
4. Upload the `events` folder from this directory directly into `public_html/storage/`
   (Resulting path: `public_html/storage/events/10/...`, `public_html/storage/events/11/...`, etc.)
   NOTE: If you upload as a .zip file, you can upload `events.zip` and click "Extract" in cPanel File Manager.
5. In cPanel, go to "phpMyAdmin", select your database (e.g. `events_app`), and click the "SQL" tab.
6. Open `database/events_seed_cpanel.sql` in any text editor, copy all SQL statements, paste them into the SQL tab, and click "Go".
7. All 4 events will instantly appear with live interactive 4-photo galleries on your website!
================================================================================
