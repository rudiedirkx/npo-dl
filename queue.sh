# Start line loop
#while [ "$line" != "" ]; do
while true; do

	# Read line 1
	line=$(head -n1 ./.queue)
	echo $line

	if [ "$line" != "" ]; then
		# Remove line 1
		sed -i '1d' ./.queue

		# Call the download script
		/usr/bin/env php download.php $line
	else
		echo "Queue empty"
	fi

	sleep 2

done
