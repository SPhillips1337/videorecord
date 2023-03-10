
#!/bin/bash
# A Shell Script to check a folder for mp3 files and transcribe them using OpenAI Whisper

# Set the directory where the mp3 files are stored
SOURCE_DIR="/var/www/html/videorecord/files/video_audio"

# Set the directory where the transcriptions will be saved
DEST_DIR="/var/www/html/videorecord/files/transcriptions"

# Set the directory where the completed files will be stored
COMPLETED_DIR="/var/www/html/videorecord/files/completed"

# Check if the source directory has any files
if [ $(ls -A $SOURCE_DIR | wc -l) -gt 0 ]; then

	# Check if Whisper is currently running
	if pgrep -x "whisper" > /dev/null
	then
	    echo "Whisper is currently running, will not start another transcription"
	else
	   # Get the first file in the source directory
	    file=$(ls -A1 $SOURCE_DIR | head -n1)
	    if [ -f $DEST_DIR/$file.txt ]; then
	        # Move the file to the completed folder
        	mv $SOURCE_DIR/$file $COMPLETED_DIR/$file
	    fi
	    # Check if the file is currently being transcribed
	    if [ ! -f $DEST_DIR/$file.txt ]; then
		/usr/local/bin/whisper --language en --model large --output_format txt --fp16 False $SOURCE_DIR/$file --output_dir $DEST_DIR/
	    fi

    	fi
else
    echo "No files found in Source Directory"
fi
