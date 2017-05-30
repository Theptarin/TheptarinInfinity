tb=`date '+%D %T'`
cd /var/www/service/TheptarinInfinity
php TheptarinInfinity.php >> /var/www/service/TheptarinInfinity/TheptarinInfinity.log
te=`date '+%D %T'`
echo "$tb , TheptarinInfinity.sh working transfer , $te , TheptarinInfinity.sh complete transfer " >>  /var/www/service/TheptarinInfinity/csv_TheptarinInfinity.log
