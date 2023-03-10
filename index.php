<!DOCTYPE html>
<html lang="en">
<head>
	    <meta charset="UTF-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

    </head>
    <body>

        <div>
            <video id="live" controls autoplay playsinline muted></video> <video id="playback" controls autoplay></video><br>
            <div id="controls">
                <button id="rec" onclick="onBtnRecordClicked()">Record</button>
                <button id="pauseRes"   onclick="onPauseResumeClicked()" disabled>Pause</button>
                <button id="stop"  onclick="onBtnStopClicked()" disabled>Stop</button>
                <button id="stateButton" onclick="onStateClicked()">trace state</button>
            </div>
        </div>
        <a id="downloadLink" download="mediarecorder.webm" name="mediarecorder.webm" href></a>
        <p id="data"></p>

	    <script src="js/adapter-latest.js"></script>         
	    <script src="js/main.js"></script>

        <textarea id="transcription"></textarea>

    </body>
</html>