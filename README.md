Date: 2023/03/10
Version: 0.1
Author: Stephen Phillips

Idea/Concept
============
Someone on Twitter I followed a while back posted an idea for a privacy conscious video transcribing blog, Basak I think it was, Which I then took a crack at as a personal project.

Anyway at the time I played around with making it work using the YouTube API which I found ultimately to be a bit clunky, it had to upload videos under a master account which would be my own account, but required user interaction OAUTH style to do so, showing a nice popup window in the process flow which they had to confirm they gave permission to upload the video to YouTube, and then we later pulled the auto generated subtitles text back down to then present to the user, not really a desirable process.

I eventually decided at that the only cuurently viable solution was to use a paid for transcription API service to do it, and left the project alone.

Recently however I bumped into OpenAI Whisper and thought that this seemed like a great candidate for this idea and a good time to revisit this as a personal project, and thus this hacky pile of development scripts was born to see how easy it would be to do so.

WARNING
=======
This a set of scripts I have written for testing the concept and idea of transcribing from video using Open AI Whisper and should very much be considered in an ALPHA stage of development, and is not intended to be a finished or polished final product etc, you have been warned! Maybe they will be of interest or help someone else looking at a similar idea.

Overview
========

1. Ok so my plan for this was to build a HTML frontend which could be used to upload or record videos, which would get uploaded and saved in a folder /var/www/html/videorecord/files/video_uploads on the server.

I originally planned to use a plain HTML 5 upload form for this, but wound up using a nice HTML/JS MediaRecorder system to do so instead in my test here.
(Credits to https://blog.addpipe.com/mediarecorder-api/ for the code I wound up using)

2. After upload the script calls via AJAX a PHP script /transcode.php

3. /transcode.php then uses the PHP exec command to call ffmpeg to convert the uploaded video to an mp3 file via class VideoConverter.php in the /classes folder.

The MP3 audio from the video then gets saved into a folder called /var/www/html/videorecord/files/video_audio

3. Next /transcode.php then was originally intended to call a class called SpeechToTextConverter which would use the shell_exec command to then run OpenAI whisper on this mp3 file and place the resulting text transcription into a folder called /var/www/html/videorecord/files/transcriptions

The resulting text in this example was then intended to be returned to the end user once transcribed.

THIS STEP DID NOT WORK, PHP for whatever reason flat refused to execute OpenAI whisper via exec no matter what I tried, However in the grand scheme of things this really didn't matter as I had planned to move my script to a design where the transcoding was carried out on the server itself on the uploads via a cronjob for example anyway and so decided to just go down that route.

Development
===========
So the idea I had in the back of my head the whole time during this was to move to a system as described above, where files were uploaded and transcribed by a cronjob. The idea I had originally had was that this would for example allow the PHP frontend to sit on a small web server, and uploaded files would be passed, for example via FTP or SCP, to a more powerful transcribing server in the backend to work on. Transcribing for example needs a fair whack of ram (10GB for the large model) and a decent CPU and GPU combination to work at its best, so the idea of this approach had occurred as the next step if I had got the above test working, and obviously in a production style environment we probably would not want to fire off dozens of calls to OpenAI Whisper from Internet Ajax calls at the same time, it would just hammer the system CPU and crash it most likely.

So my thinking here was PHP video uploader, PHP video to audio via ffmpeg, Copy to the transcriber server, Cron based transcription via a bash script which is run regularly say every minute to check for files to work on and is written to only process 1 file at a time with OpenAI Whisper, waiting until the previous transcription completes to do this, and then this would be returned back, with perhaps a PHP script running looking for the transcribed text file linked by name and id to a database entry, so that we could show the end user a series of status and progress reports as to how the transcription was going such as "Uploaded", "Pending", "Transcribing", and "Complete" or "Failed" etc in a overall dashboard.

Results
=======
So to prove this idea would work I needed to establish that transcription could be carried out via bash script and cronjob.
I wrote a bash script transcribe.sh (This would need to be copied from the scripts folder here in the repo to /etc/cron.d) which I put in /etc/cron.d/ and set to run every 5 minutes via crontab -e with the following cron entry;
*/1 * * * * sh /etc/cron.d/transcribe.sh >> /var/log/transcribe_log.txt 2>&1

( This also outputs a nice little log file for of of what is going on which we can tail with tail -f /var/log/transcribe_log.txt )

This then has 3 folders it works with;

# Set the directory where the mp3 files are stored
SOURCE_DIR="/var/www/html/videorecord/files/video_audio"

# Set the directory where the transcriptions will be saved
DEST_DIR="/var/www/html/videorecord/files/transcriptions"

# Set the directory where the completed audio files will be stored
COMPLETED_DIR="/var/www/html/videorecord/files/completed"

It takes the source audio file, places the transcription in the destination directory, and moves the original MP3 file over to a completed folder so as not to work on the same file twice.

Then I moved OpenAI Whisper from out of my home folder and over to /usr/local/bin and set it so it could be used by anyone for running it via the cronjob.

sudo cp ~/.local/bin/whisper /usr/local/bin/
sudo chmod 755 /usr/local/bin/openai-whisper
sudo chmod 755 /usr/local/bin/whisper

This then works perfectly as we would want. For record the final command line I used for OpenAI Whisper was;
/usr/local/bin/whisper --language en --model large --output_format txt --fp16 False $SOURCE_DIR/$file --output_dir $DEST_DIR/

Further development
===================
This obviously is no where near finished, but the concept does work. I still have to hook up a MySQL database, Tidy up the frontend adding in an upload and record option perhaps to allow uploaded of previously recorded videos, build a dashboard style system to view uploaded recordings and see their state of transcription, and write the PHP script which will update the status of the transcription in a database, which as we see in the bash script transcribe.sh above can simply be done by looking in the folders for files and then matching against name and id in the db, for example;

Files in /var/www/html/videorecord/files/video_uploads are "Uploaded"
when a file is in /var/www/html/videorecord/files/video_audio it's "Pending"
If a file is in /var/www/html/videorecord/files/completed it's  "Complete" and so on

We could update the transcribe.sh I guess to put a simple file using touch in a folder called /var/www/html/videorecord/files/processing with the same file name and id combo at the start of transcription to give us a folder to check and say its "Transcribing".

The final .txt files to read in and place in a database against the original file details are in in /var/www/html/videorecord/files/transcriptions with the same name/id combo which we used for the video and the audio etc.

Which is fairly easy to do with a simple bit of php and cron as well, and could be achieved in the multiple server setup as well as described previously too.
we could also for example add a level of archiving or tidy up to the system, as there would be obvious diskspace issues with holding a large volume of video and audio files, however if the final goal was to hook this up to a blog style system we might want to keep such things attached to a post, which after upload the user could then review to share etc.

The final step if I ever finish all of the above to a decent degree would then to be to build a blog site with this integrated in as discussed here, I do have another rather basic PHP blog system that I played with before for example which I could use as a base to do so, at https://github.com/SPhillips1337/video-transcription-blog

Using such an idea and system as described here for example I could run a frontend on web hosting perhaps using something like a VPS or AWS EC2 etc where under the hood it could transfer uploads to a more powerful dedicated transcribing PC running in an office somewhere on a highspeed line.

Requirements
============
The large OpenAI Whisper language model needs about 10GB of ram to work in, see https://github.com/openai/whisper for more on the ram usage requirements etc.

Setup
=====
After installing ubuntu 20.04 LTS and then a lampstack using tasksel, updating to PHP 8.1 
I then installed the following;

(Some of the following might not actually be required, I found a few issues getting Open AI whisper to run with my Ubuntu 20.04 LTS box, and later run via crontab as well.)

sudo apt install ffmpeg
sudo apt install software-properties-common
sudo add-apt-repository ppa:deadsnakes/ppa
sudo apt install python3.9
sudo apt install build-essential zlib1g-dev libncurses5-dev libgdbm-dev libnss3-dev libssl-dev libreadline-dev libffi-dev libsqlite3-dev wget libbz2-dev
export PATH=$PATH:/home/stephen/.local/bin 
source ~/.profile
alias pip=pip3
pip install -U whisper
pip install --upgrade --no-deps --force-reinstall git+https://github.com/openai/whisper.git

LAMP install steps (roughly)
==================
sudo apt-get update
sudo apt install tasksel
sudo tasksel install lamp-server
sudo mysql_secure_installation
sudo apt install phpmyadmin php-mbstring php-zip php-gd php-json php-curl
sudo phpenmod mbstring
sudo systemctl restart apache2
sudo apt-get install build-essential
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt install php8.1
sudo apt install php8.1-common php8.1-mysql php8.1-xml php8.1-xmlrpc php8.1-curl php8.1-gd php8.1-imagick php8.1-cli php8.1-dev php8.1-imap php8.1-mbstring php8.1-opcache php8.>
chmod 0755 /var/www/html -R
sudo chmod 0755 /var/www/html -R
sudo service apache2 restart
sudo apt install php8.1-bcmath php8.1-curl php8.1-fpm php8.1-gd php8.1-mbstring php8.1-mysql php8.1-tidy php-json php-xml php-xmlrpc php-zip
sudo a2enmod php8.1
sudo a2disconf php7.4-fpm
sudo a2enconf php8.1-fpm
sudo service apache2 restart
sudo service php8.1-fpm restart
sudo service apache2 restart
cd /etc/apache2
apache2ctl configtest
sudo apt-get purge php7.4*
sudo service apache2 restart

