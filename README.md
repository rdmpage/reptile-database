# reptile-database
Reptile database


## Notes on tab-delimited data from Figshare

These files are UTF-16 Little Endian and have CR end of lines, need to convert to UTF-8 and LF end of lines.

Also have \x{0B} characters (VT, vertical tabs), need to replace with |

Also have \x{1D} characters (GS, Group Separator), need to replace with no character ''.
