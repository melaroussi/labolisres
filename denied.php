<?php
if(isset($_GET['origin'])) {
	header('Location: '.$_GET['origin']);
} else {
	header('Location: index.php');
}
?>