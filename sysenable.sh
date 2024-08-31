#! /bin/env sh

cd /home/ubuntu/AtomTerm

if [ ! -f /etc/systemd/system/atomterm.service ]; then
    cp atomterm.service /etc/systemd/system/atomterm.service
    systemctl enable atomterm.service
    systemctl start atomterm.service
fi

systemctl restart atomterm.service
