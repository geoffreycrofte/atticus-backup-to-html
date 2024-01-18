# Atticus Backup to HTML Page
A PHP Script to generate a webpage from an Atticus.io backup for ebook.

## How to use it?
You need a JSON backup file from the tool Atticus.io. The JSON file is an export of all the books you wrote with the tool.

To use it, create a folder named "data" and put the JSON file in it named "atticus.json".

Launch your local server and go to your project folder URL.

## Browser multiple books
At the moment, you need to use the URL parameter "?book=0" to set which book you are exploring.
If you want to display the fourth book, use "?book=3", the first one beeing "0"

## Export for PDF (print)
Simply use the command "print" of your browser, and choose "Print as PDF".

If you want to prepare the file to generate a printable PDF, add to the URL the param "?print".
This will rework a little bit the HTML and CSS to make it Paged-media compatible.
Then do "Print as PDF" again, it'll prepare the file for an A5 format.
