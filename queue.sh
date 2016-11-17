# Start line loop
while true; do

	# Read line 1
	line=$(head -n1 ./.queue)
	echo $line

	if [ "$line" != "" ]; then
		# Call the download script
		/usr/bin/env php download.php $line

		# Remove line 1
		sed -i '1d' ./.queue
	else
		now=$(date)
		echo "Queue empty $now"
	fi

	sleep 2

done
