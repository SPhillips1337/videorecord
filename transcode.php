<?php
    // Include classes
    require_once 'classes/VideoConverter.php';
    require_once 'classes/SpeechToTextConverter.php';

    // lets set up our folder paths relative to $_SERVER['DOCUMENT_ROOT'] for our files
    $root_path = $_SERVER['DOCUMENT_ROOT'].'/videorecord';
    $video_upload_path = $root_path.'/files/video_uploads/';
    $audio_upload_path = $root_path.'/files/video_audio/';

    //phpinfo(INFO_VARIABLES);

    // we should have been passed a wbem video file in the post, lets check and save it so we can work with it
    if (isset($_FILES['video'])) {
        $wbem_file = $_FILES['video']['tmp_name'];
        $wbem_file_name = basename($wbem_file);
        $wbem_file_extension = $_REQUEST['extension'];

        //var_dump($_FILES['video']);

        //echo $video_upload_path.basename($wbem_file)."<br>";

        // upload this file to the video upload folder
        if (move_uploaded_file($_FILES['video']['tmp_name'], $video_upload_path.basename($wbem_file).'.'.$wbem_file_extension)) {
            // next we need to tell video recorder to covert the wbem video to an mp3 file
            // in future we might put $wbem_file in the db to set the recording to be processed later by a script running in the background

            // Initialize video recording
            $record = new VideoConverter();
            $record->convertRecording($video_upload_path.basename($wbem_file).'.'.$wbem_file_extension,$audio_upload_path.basename($wbem_file));

            // now we have the mp3 file, let's convert it to text
            // Initialize speech to text converter
            $converter = new SpeechToTextConverter();
        
            // Convert video to text
            $text = $converter->convert($audio_upload_path.basename($wbem_file));

            // now we just need to return the text back to the client, in future we might want to store the text in a database
            echo $text;
        }
        else {
            echo "Not uploaded because of error #".$_FILES["file"]["error"];
        }
        //unlink($wbem_file);
        exit();
    }
?>