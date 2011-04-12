<?=$Errors?$Errors:''?>
<?=$Success?$Success:''?>
<div class="block-header" id="start-encoding-header"><h1>Start Encoding</h1></div>
<div class="block">
    
    <p>
        <strong>Notes:</strong><br />
        1) Upload raw video file to uploads/temp dir.<br />
        2) Change permissions on file to 777
    </p>

    <form method="post">
        <label>Filename:</label>&nbsp;<input type="text" name="filename" />&nbsp;
        <input type="hidden" name="submitted" value="TRUE" />
        <input type="submit" name="submit" value="Start Encoding" />
    </form>

</div>
