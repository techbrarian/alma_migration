# alma_migration
These php scripts were created to wrangle/shape the Robert M Bird Library's Sirsi Symphony data so that it would be compatible with the Ex Libris Alma product. The scripts do a number of RMB specific things, so users will need to tailor them. They were built and rebuilt on the fly and under severe time constraints, so there's some spaghetti in with the meatballs and they could use further tweakage from potential users. Did the job for us, though.


Each of the scripts has some read-me information at the beginning and various comments. Read them carefully. Some of the
scripts must be run in a particular order. The bibs script needs the processed holdings and serial records to be in
the same folder when it runs (it requires info from and makes modifications to those files). The users.php script
requires that the processed charges file be in the same folder when it runs. That script produces multiple files and
will require various levels of local tweakage, so look it over closely. 
