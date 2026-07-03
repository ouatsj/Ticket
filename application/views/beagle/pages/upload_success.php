<html>
<head>
<title>Upload Form</title>
</head>
<body>

<h3>Your file was successfully uploaded!</h3>
<p>
<img src="<?php echo "././image/".$upload_data;?>">
</p>



<p> <?php echo anchor('upload', 'Upload Another File!'); ?> </p>
</body>
</html>