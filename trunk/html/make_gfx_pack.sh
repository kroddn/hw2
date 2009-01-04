#!/bin/sh
#
# Erzeuge ein Grafikpaket im Ordner "grafikpaket"
#

NAME=grafikpaket/hw_grafik_v3.10.zip

if [ -d grafikpaket ]; then
    zip -9r \
        ${NAME} \
        `find images/ingame_v3/ -name "*.gif"; find images/ingame_v3/ -name "*.png" ; find images/ingame_v3/ -name "*.jpg"` gfx_version.gif 
else
    echo 'Verzeichnis "grafikpaket" existiert nicht.'
fi