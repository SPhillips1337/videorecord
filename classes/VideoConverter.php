<?php

class VideoConverter {
    /* class uses ffmpeg via command line to convert video from webm to mp3 */
    // later we might run this sequentially on uploaded files linked to a database by id to process them

    function convertRecording($from,$to){

        $command = 'ffmpeg -i '.$from.' -f mp3 '.$to.'.mp3';
        //echo 'ffmpeg -i '.$from.' -f mp3 '.$to.'.mp3<br>';
        exec($command);        
    }
}

?>