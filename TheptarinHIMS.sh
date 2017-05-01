tb=`date '+%D %T'`
php TheptarinHIMS.php >> TheptarinHIMS.log
te=`date '+%D %T'`
echo "$tb , TheptarinHIMS.sh working transfer , $te , TheptarinHIMS.sh complete transfer " >>  csv_TheptarinHIMS.log
