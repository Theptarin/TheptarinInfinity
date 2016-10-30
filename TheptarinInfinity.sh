tb=`date '+%D %T'`
cd /var/www/TheptarinInfinity
php5 TheptarinInfinity.php >> /var/www/TheptarinInfinity/TheptarinInfinity.log
te=`date '+%D %T'`
echo "$tb , TheptarinInfinity.sh working transfer , $te , TheptarinInfinity.sh complete transfer " >>  /var/www/TheptarinInfinity/csv_TheptarinInfinity.log
