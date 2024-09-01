#! /bin/env sh

git pull

if [ ! -f /etc/systemd/system/atomterm.service ]; then
    sudo cp atomterm.service /etc/systemd/system/atomterm.service
    sudo systemctl enable atomterm.service
    sudo systemctl start atomterm.service
fi

sudo systemctl daemon-reload
sudo systemctl restart atomterm.service
