<?php
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title>MAINTENANCE</title>
<link href="/favicon.ico" rel="shortcut icon" />
<style>
  body {text-align: center; padding: 150px; background: #F7F7F7;
		 font: 20px Helvetica, sans-serif; color: #333;}
  h1 {font-size: 50px;}
  article {display: block; text-align: left; width: 650px; margin: 0 auto;}
  a {color: #dc8100; text-decoration: none;}
  a:hover {color: #333; text-decoration: none;}
</style>
</head>
<body>
<article>
    <div style="text-align: center;">
    <img src="https://www.svgrepo.com/show/426192/cogs-settings.svg" width="150px"/>
    <h1>We'll be back soon!</h1>
    </div>
    <div>
        <p>Hi, <a href="/" style="font-weight:bold;"><?php bloginfo('name'); ?></a> is going to maintenance. Sorry for the inconvenience. Please come back later 'kay.</p>
        <p>Regards,<br />
		&mdash; Administrator</p>
    </div>
</article>
</body>
</html>