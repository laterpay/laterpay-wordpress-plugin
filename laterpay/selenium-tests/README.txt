To run Selenium server and use it

wget http://release.seleniumhq.org/selenium-ide/2.6.0/selenium-ide-2.6.0.jar 
sudo cp http://release.seleniumhq.org/selenium-ide/2.6.0/selenium-ide-2.6.0.jar /usr/local/bin/
sudo apt-get install java-common default-jre firefox Xvfb Xorg
screen -d -m Xvfb :99 -ac -screen 0 1280x1024x24 &
export DISPLAY=:99
screen -d -m java -jar /usr/local/bin/selenium-server-standalone-2.42.2.jar
wget http://codeception.com/codecept.phar

php codecept.phar run


