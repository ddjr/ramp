sudo apt-get update
sudo apt-get upgrade
sudo apt-get install git
git config --global user.name "Jae Hyun Choe"
git config --global user.email "k11jc02@kzoo.edu"
git config --global merge.tool vimdiff
git config --global credential.helper cache
git config --global credential.helper 'cache --timeout=3600'
git config --global color.ui true
git clone https://github.com/AlyceBrady/Unix-Intro.git
cd Unix-Intro/
mv .[b-v]* ..
mv * ..
cd ..
rmdir Unix-Intro/
cat >>.bashrc
source .bashrc
l
a
l
sudo apt-get install apache2
vim /etc/apache2/apache2.conf 
sudo vim /etc/apache2/apache2.conf 
sudo a2enmod rewrite
sudo apt-get install libapache2-mod-php5
sudo /etc/init.d/apache2 restart
sudo adduser ubuntu www-data
sudo chown -R www-data:www-data /var/www
sudo chmod -R g+rw /var/www
cd /var/www
ls
cd html
ls
vim phptest.php
sudo chmod -R g+rw /var/www/html
vim phptest.php
ls
ls -a
vi phptest.php
exit
cd /var/www/html
ls
vim phptest.php
sudo apt-get install mysql-server
sudo apt-get install php5-mysql
sudo apt-get install phpmyadmin
sudo apt-get remove phpmyadmin
mysql -u root -p
cd ~
ls -l .my.cnf root.my.cnf .mysql_history
chmod 600 .my.cnf root.my.cnf .mysql_history
ls -l .my.cnf root.my.cnf .mysql_history
vim root.my.cnf 
mysql -u root
mysql
mysql -u root -p
vim root.my.cnf 
cd /var/www/html
mkdir testdir
ls
rmdir testdir/
ls
git clone https://github.com/AlyceBrady/ramp.git
cd installation/SmartDemoSetup
cd ~
l
cd /var/www/html
ls
cd ramp
ls
cd installation/SmartDemoSetup
chmod 600 createSmartDemoMysqlAccts.sql
vim createSmartDemoMysqlAccts.sql
mysql -u root -p
cd ../SmartDevSetup/
chmod 600 createSmartDevMysqlAccts.sql
vim createSmartDevMysqlAccts.sql 
mysql -u root -p
mysql -u smartdemo -p
mysql -u smartdev -p
cd /var/www/html/ramp/application/configs/
cat ramp_basics.ini smart_defaults.ini smartDemo.ini smartApplicationTemplate.ini 
cat ramp_basics.ini smart_defaults.ini smartDemo.ini smartApplicationTemplate.ini >application.ini
ls -l application.ini
sudo chgrp www-data application.ini
chmod 660 application.ini
vim application.ini
cd ..
cd public/
mkdir smartdemo
cd smartdemo/
ln -s ../css ../images ../tb_assets .
cp ../index.php .
vim index.php 
cd ..
mkdir smartdev
cd smartdev
ln -s ../css ../images ../tb_assets .
cp ../index.php .
vim index.php 
cd ..
ls
cd /var/www/html
ls
cd ramp
ls
cd public/
ls
cd smartdemo/
ls
cd ../smartdev
ls
vim index.php 
cd ..
ls
cd application/
ls
cd configs/
ls
cat application.ini 
ls -l application.ini 
chmod 660 application.ini 
ls -l application.ini 
vim application.ini 
cd ..
cd public/
ls
cd smartdemo/
ls
vim index.php 
cd /var/www/html/ramp
ls
cd application/
ls
cd configs/
ls
rm application.ini 
ls
cat ramp_basics.ini smart_defaults.ini smartDemo.ini smartApplicationTemplate.ini >application.ini
ls
vim application.ini 
ls
cd ../../public
ls
vim index.php 
cd ..
cd application/
ls
ls ..
cd ../library/
ls
cd Ramp/
ls
cd ../..
ls
exit
config
cd config
cd application
ls
cd ../..
ls
cd ramp
ls
cd pub
cd public/
ls
cd ../application/configs/
ls
vim application.ini 
cd ../../installation/
ls
cd Njala_Proto_Setup/
ls
mysql -u root -p
mysql -u smartdev -p
cd ../../public/
mkdir njala
cd njala/
cp ../index.php ../.htaccess 
ls
cp ../index.php ../.htaccess .
ls
cd ../
l
cd njala/
ln -s ../css ../images ../tb_assets .
vim index.php 
vim .htaccess 
cd ../
ls
cd smartdemo/
ls
l
vim .htaccess 
vim ../
cd ..
ls
vim .htaccess 
cd ../application/
cd configs/
vim application.ini 
vim ../../public/njala/
cd ../../public/njala/
cp ../index.php ../.htaccess .
ln -s ../css ../images ../tb_assets .
vim index.php 
vim .htaccess 
cd ../../application/configs/
vim application.ini 
exit
mysql -u root -p
mysql -u root -p
quit
end
exit
mysql -u root -p
cd configs/
cd ..
ls
cd devSettings/
ls
cd Smart/
l *.act
vim dbaIndex.act 
vim index.act 
vim dbaIndex.act 
ls
l ../
cd ..
cd Admin/
ls
vim smartAdmin.act 
vim smart_auth_users.ini 
vim smartAdmin.act 
l
vim smart_auth_users.ini 
mysql -u root -p
cd ../..
ls
cd controllers/
ls
vim ActivityController.php 
l
cd ../../library
l
cd Ramp
l
cd Auth
l
l Adapter/
l DbTable/
cd ..
l
l Controller/
cd Controller/Plugin/
l
vi ACL
vi ACL.php
cd config
cd configs
vi application.ini 
ls
cd /var/www/html/ramp/public/
ls
cd smartdev
ls
cd ../..
ls
cd library/
ls
cd Ramp/
ls
cd Auth/
ls
cd DbTable/
ls
vim AccessRule.php 
vim Auths.php 
vim Users.php 
cd ..
ls
cd ..
ls
cd Controller/
ls
cd Plugin/
ls
vim ACL.php 
cd ..
ls
cd ..
ls
vim Ac
vim Acl.php 
cd Auth/
ls
cd DbTable/
ls
vim AccessRule.php 
vim Auths.php 
cd ../..
ls
cd ..
ls
cd Zend/
ls
vim Acl
vim Auth.php 
cd Auth/
ls
cd Storage/
ls
vim Session.php 
cd ../../..
ls
cd ..
ls
cd application/
ls
cd controllers/
ls
vim AuthController.php 
cd ..
ls
cd configs/
ls
vim application.ini 
cd ..
ls
cd controllers/
ls
vim TableController.php 
exit
cd configs/
ls
vim application.ini 
cd ../..
ls
cd library/
ls
cd Ramp/
ls
cd Auth/
ls
cd DbTable/
ls
vim Auths.php 
vim AccessRule.php 
vim Users.php 
mysql -u root -p
cd ../../
ls
cd Auth/
ls
cd ..
ls
l
vim Acl.php 
l
vim Table/
cd Table/
ls
cd Conf
cd Config/
ls
l
cd ..
cd Controller/
ls
cd Plugin/
ls
vim ACL.php 
cd ../..
ls
cd Auth/
ls
cd DbTable/
ls
cd ../../Controller/Plugin/
vim ACL.php 
cd configs/
cd ..
ls
cd ..
ls
cd library/
ls
cd Ramp/
ls
vim Acl.php 
ls
cd Auth/
ls
cd ..
ls
cd ..
ls
cd ..
cd installation/
ls
vim create_users_auths_example.sql 
vim Njala_Proto_Setup/
cd Njala
cd Njala_Proto_Setup/
ls
vim createSmartUsersAuths.sql 
cd ../../library/Ramp/
ls
vim Acl.php 
cd ../
ls
cd ..
ls
cd ..
ls
cd ramp/library/
ls
cd Ramp/
ls
cd Controller/
ls
cd Plugin/
ls
vim ACL.php 
l
rm .ACL.php.swp 
vim ACL.php 
cd ..
l
cd ..
l
cd Table/
ls
cd DbTable/
ls
cd ..
vim SetTable.php 
cd configs
vim application.ini 
mysql -u root -[
mysql -u root -p
cd ../controllers/
ls
vim ActivityController.php 
git st
git status
git diff ActivityController.php
git checkout ActivityController.php
git status
clear
vim TableController.php 
ls
cd ..
ls
cd ..
cd application/
ls
vim controllers/TableController.php 
ls
exit
cd configs
cd ..
ls
cd configs
vim application.ini 
cd ..
ls
cd library/
ls
cd Ramp
ls
vim Acl.php
mysql
mysql -u root -p
exit
