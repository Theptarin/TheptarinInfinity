tb=`date '+%D %T'`
cd /var/www/service/TheptarinInfinity
php TheptarinHIMS.php >> /var/www/service/TheptarinInfinity/TheptarinHIMS.log
te=`date '+%D %T'`
echo "$tb , TheptarinHIMS.sh working transfer , $te , TheptarinHIMS.sh complete transfer " >>  /var/www/service/TheptarinInfinity/csv_TheptarinHIMS.log
