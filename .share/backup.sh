#!/bin/bash
#
ORIG="/home/vlopes/Documentos"
DEST="/run/user/1001/gvfs/smb-share:server=etlas.ipbrick.com,share=vlopes/Backups/Documentos"

rsync -av --no-perms --delete "$ORIG/" "$DEST/"
