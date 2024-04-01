# Atticus Backup to HTML Page
A PHP Script to generate a webpage from an Atticus.io backup for ebook.

## How to use it?
You need a JSON backup file from the tool Atticus.io. The JSON file is an export of all the books you wrote with the tool.

To use it, create a folder named "data" and put the JSON file in it named "atticus.json".

Launch your local server and go to your project folder URL.

## Navigate multiple books
Simply use the sidenavigation to access your other books.

## Export for PDF (print)
This script uses a dedicated page to support PagedJS.
Access /pagedjs.php?book=0 to get your first book interpreted by PagedJS.

Wait for all the page numbers to be generated before hitting CTRL + P too open the print function of your browser.
The CSS prepares the book for the Amazon Kindle Paperback and Harcover in 6x9in, with 6mm bleed.

To rework this setup, check the <a href="https://pagedjs.org/documentation/">PagedJS documentation</a>.

PagedJS will rework the HTML and CSS to make it Paged-media compatible.
