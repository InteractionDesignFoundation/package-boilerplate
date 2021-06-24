#!/usr/bin/env bash

# This is a current number of duplicates, we should decrease it
ALLOWED_NUMBER_OF_CLONES=6

OUTPUT_FILENAME='phpcpdreport.log'
composer run phpcpd > ./$OUTPUT_FILENAME

cat $OUTPUT_FILENAME

DETECTED_NUMBER_OF_CLONES=`grep "Found " $OUTPUT_FILENAME | cut -f2 -d " "`

if [[ $DETECTED_NUMBER_OF_CLONES -gt $ALLOWED_NUMBER_OF_CLONES ]] ; then
    echo "❌ ERROR: Number of code clones is higher than allowed ($DETECTED_NUMBER_OF_CLONES > $ALLOWED_NUMBER_OF_CLONES)"
    echo 'All found clones (old and new ones):'
    echo "You can run “composer phpcpd” to get a new list of duplicates when you made some changes"
    exit 1 # terminate and indicate error
fi;

echo "✅ ${DETECTED_NUMBER_OF_CLONES} clones found ($ALLOWED_NUMBER_OF_CLONES allowed)"

CLONES_ALLOW_TO_ADD=$(($ALLOWED_NUMBER_OF_CLONES - $DETECTED_NUMBER_OF_CLONES))
if [[ $CLONES_ALLOW_TO_ADD -gt 3 ]] ; then
    echo "🚀 It seems like you have removed code clones – that’s great! Now it’s a time to update ALLOWED_NUMBER_OF_CLONES variable: you can reduce it by $CLONES_ALLOW_TO_ADD"
    exit 1 # terminate and indicate error
fi;
