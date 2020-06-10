DIR="/var/www/html"
sudo service apache2 stop
if [ -d "$DIR" ]; then
  sudo rm -r /var/www/html
fi
