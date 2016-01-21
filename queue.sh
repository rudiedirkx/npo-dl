# Read line 1
line=$(head -n1 ./.queue)
echo $line

# Start line loop
while [ "$line" != "" ]; do

	# Remove line 1
	sed -i '1d' ./.queue

	# Call the download script
	/usr/bin/env php download.php $line

	# Read next line 1
	line=$(head -n1 ./.queue)
	echo $line

done
