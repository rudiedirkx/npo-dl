type="$1"
if [ -z "$type" ]; then
	echo 'I need a valid TYPE'
	exit 1
fi

echo "Start typing:"
echo

# Empty queue
# >./.queue

# Empty nohup log
>./nohup.out

first=1
read line
while [ "$line" != "" ]; do

	#
	# Method 1, ignore all other processes
	#

	# Add to queue
	echo "$type $type-$line" >> ./.queue

	# Run queue
	if [ $first -eq 1 ]; then
		first=0

		echo "STARTING SUB JOBS"
		nohup ./queue.sh &
	fi



	#
	# Method 2, use previous queue state
	#

	# Run queue
	# if [ -s "./.queue" ]; then
	# 	# Add to queue
	# 	echo "$type $type-$line" >> ./.queue
	#
	# 	echo "Not starting sub jobs"
	# else
	# 	# Add to queue
	# 	echo "$type $type-$line" >> ./.queue
	#
	# 	echo "STARTING SUB JOBS"
	# 	nohup ./queue.sh &
	# fi

	read line
done

tail -n9999 -f ./nohup.out
