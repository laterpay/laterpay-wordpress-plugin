<h1>Acceptance test cases by Codeception-Selenium to test laterpay-wordpress-plugin</h1>

<p>This is test cases for official LaterPay plugin.</p>

<h2>Installation</h2>
1. The test cases is available on https://github.com/laterpay/laterpay-wordpress-plugin/tree/feature/selenium-tests
2. To run it with local Selenium have to be installed and started:
<code>
sudo apt-get install java-common default-jre firefox Xvfb Xorg
wget http://release.seleniumhq.org/selenium-ide/2.6.0/selenium-ide-2.6.0.jar 
sudo cp http://release.seleniumhq.org/selenium-ide/2.6.0/selenium-ide-2.6.0.jar /usr/local/bin/
screen -d -m Xvfb :99 -ac -screen 0 1280x1024x24 &
export DISPLAY=:99
screen -d -m java -jar /usr/local/bin/selenium-server-standalone-2.42.2.jar
</code>

<h2>Run tests manually</h2>
1. Run one test case manually 
<code>php codecept.phar run --html --steps -g UI1</code>
2. Run all tests
<code>php codecept.phar run --html --steps</code>

<h2>Copyright</h2>
Copyright 2014 LaterPay GmbH – Released under MIT License