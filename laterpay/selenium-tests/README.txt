sudo apt-get install firefox Xvfb Xorg
screen -d -m Xvfb :99 -ac -screen 0 1280x1024x24 &
export DISPLAY=:99
screen -d -m java -jar /usr/local/bin/selenium-server-standalone-2.42.2.jar