<?php
  $exif = exif_read_data('./image/oshiro.jpeg');
?>

<img src="./image/oshiro.jpeg" style="width: 200px"><br>
この画像のexif情報は以下の通りです。<br>
<?= nl2br(print_r($exif, true)); ?>
