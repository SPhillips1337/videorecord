<?php
class SpeechToTextConverter
{
    public function convert($audioFile)
    {
        //$text = shell_exec('whisper --language en --model tiny --fp16 False '.escapeshellarg($audioFile).'.mp3');

        return $text;
        
    }
}
?>