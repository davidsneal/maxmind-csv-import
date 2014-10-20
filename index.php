<!DOCTYPE html>
<html>

<head>
	<title>Maxmind CSV Import</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='//fonts.googleapis.com/css?family=Inconsolata' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="/assets/css/style.css">

	<script type="text/javascript">
		function startImport() {
			document.getElementById('iframe').src = '/inc/import_csv.php';
		}
	</script>
</head>

<body>

<div class="main-wrapper">
	<div class="main-container">
		<h1>Maxmind CSV Import</h1>
	</div>
	<div class="iframe-container">
		<iframe src="/views/ready.html" id="iframe" class="iframe" width="600" height="330">
		  <p>Your browser does not support iframes.</p>
		</iframe>
	</div>
	<div class="btn-container">
		<span class="btn-start" onclick="startImport()">START</span>
	</div>
</div>

</body>
</html>