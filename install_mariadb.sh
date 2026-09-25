#!/bin/bash
sudo apt-get update
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mariadb-server
sudo service mariadb start
sudo mysql -e "CREATE DATABASE pal_chasme_wale;"
sudo mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION; FLUSH PRIVILEGES;"
