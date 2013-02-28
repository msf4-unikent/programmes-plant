echo "Setting up Programmes Plant, this could take some time - try a cup of tea...\c"
git clone -q git@github.com:unikent/programmes-plant.git
echo "...\c"
cd "$(pwd)/programmes-plant"
git submodule -q init && git submodule -q update
echo "...\c"
curl -s https://getcomposer.org/installer | php > /dev/null
php composer.phar -q --dev install
rm "$(pwd)/composer.phar"
echo "...\c"
mkdir application/config/local
cp application/config/*.sample application/config/local
ls application/config/local/*.sample | while read file; do mv $file  `echo $file | sed s/.sample//`; done
echo "your turn.\n"
echo "1. You'll need to setup a MySQL database, and record the details"
read -n 1 -p "Press any key once you are done..."
echo "\n2. Edit the credentials in application/config/local/database.php"
read -n 1 -p "Press any key once you are done..."
echo "\n3. Edit the details in application/config/local/auth.php to setup your authorisation type."
read -n 1 -p "Press any key once you are done..."
echo "\n4. Point a web server at the public/ directory."
read -n 1 -p "Press any key once you are done..."
echo "\nInstalling databases..."
php artisan migrate:install --env=local
php artisan migrate --env=local
echo "\nAll done...point a browser to your installation!"