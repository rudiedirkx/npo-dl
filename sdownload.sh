type="$1"
if [ -z "$type" ]; then
	echo 'I need a valid TYPE'
	exit 1
fi

echo 'Paste lines to download, formatted like "NAME URL"'
echo

downloads=()

read line
while [ "$line" != "" ]; do
	downloads+=("$line")
	read line
done

PHP=$(which php)
for data in "${downloads[@]}"; do
	cmd="$PHP download.php $type $type-$data"
	$cmd
	echo
done
