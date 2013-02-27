<?php


  if (isset($_GET['x1']))
  		$x1=$_GET['x1'];
  	  else
  	  	$x1=155386;

  if (isset($_GET['x2']))
  		$x2=$_GET['x2'];
  	  else
  	  	$x2=155388;

  if (isset($_GET['y1']))
  		$y1=$_GET['y1'];
  	  else
  	  	$y1=170922;

  if (isset($_GET['y2']))
  		$y2=$_GET['y2'];
  	  else
  	  	$y2=170920;

  $startx = min(array($x1,$x2));
  $stopx = max(array($x1,$x2));
  $starty = max(array($y1,$y2));
  $stopy = min(array($y1,$y2));

  if (isset($_GET['mag']))
  		$mag=$_GET['mag'];
  	  else
  	  	$mag=18;

  if (isset($_GET['name']))
  		$name=$_GET['name'];
  	  else
  	  	$name='map';

  $width = ($stopx-$startx+1);
  $height = ($starty-$stopy+1);

  //до этого момента дело было в общем шло, теперь будем разбивать на куски

  $chunk_width = ceil($width / ceil($width / 30)); //ширина в тайлах куска карты (макс - 30)
  $chunk_height = ceil($height / ceil($height / 30)); //высота в тайлах куска карты (макс - 30)
  $chunk_total = $chunk_width * $chunk_height; //общее количество тайлов в куске
  $chunk_hor_count = ceil($width / $chunk_width); //количество кусков в карте по горизонтали
  $chunk_ver_count = ceil($height / $chunk_height); //количество кусков в карте по вертикали
  $chunk_total_count = $chunk_hor_count * $chunk_ver_count;


  for ($chunk_i = 0; $chunk_i < $chunk_hor_count; $chunk_i++)
  for ($chunk_j = 0; $chunk_j < $chunk_ver_count; $chunk_j++)
  {      $chunk_cur = $chunk_i*$chunk_ver_count + $chunk_j + 1;
      $chunk_startx = $startx+($chunk_i * $chunk_width);
      $chunk_starty = $starty-($chunk_j * $chunk_height);
      $chunk_stopx = $startx+ (($chunk_i+1) * $chunk_width)-1;
      $chunk_stopy = $starty - (($chunk_j+1) * $chunk_height)+1;

      $cur = 0;
      $img = imagecreatetruecolor ($chunk_width*256,$chunk_height*256);
      for ($i = 0; $i < $chunk_width; $i++)
      	for ($j = 0; $j < $chunk_height; $j++) {
      		$im = imagecreatefrompng("http://tms3.visicom.ua/1.0.3/world_ru/".$mag."/".($chunk_startx+$i)."/".($chunk_starty-$j).".png");
            if ($im) {
              imagecopy ($img, $im, 256*$i, 256*$j, 0, 0, 256, 256);
              imagedestroy($im);
            }
              $cur++;
              echo 'processing [chunk_'.$chunk_i.'_'.$chunk_j.' - '.$chunk_cur.'/'.$chunk_total_count.']: '.$cur.' / '.$chunk_total.'<br>';
      }
      //header("Content-type: image/jpeg");
      imagejpeg ($img, '/'.$name.'_'.$chunk_i.'_'.$chunk_j.'.jpg');
      imagedestroy($img);
      echo 'Image saved to '.$name.'_'.$chunk_i.'_'.$chunk_j.'.jpg';

      	//вычисляем необходимые для файла-калибровки параметры
      	$width_px  = $chunk_width*256;
      	$height_px = $chunk_height*256;

        $upp_array = array( 0 => 1565430.332031,
                            1 =>  782715.166016,
                            2 =>  391357.583008,
                            3 =>  195578.791504,
                            4 =>   97839.395752,
                            5 =>   48919.697876,
                            6 =>   24459.848938,
                            7 =>   12229.924469,
                            8 =>    6114.962234,
                            9 =>    3057.481117,
                           10 =>    1528.740559,
                           11 =>     764.370279,
                           12 =>     382.18514,
                           13 =>     191.09257,
                           14 =>      95.546285,
                           15 =>      47.773142,
                           16 =>      23.886571,
                           17 =>      11.943286,
                           18 =>       5.971643);
      	$upp = $upp_array[$mag]; // UnitsPerPixel для zoom=$mag visicom
      	$maxLng = 200375082;     //шаманские числа с визикомовского скрипта
      	$maxLat = 200375083;
      	$scaleFactor = 63781370; //десятикратный радиус земли

      	$geoLeft = ($chunk_startx*256*$upp - $maxLng)/$scaleFactor * 180/M_PI;
      	$geoRight = (($chunk_stopx + 1)*256*$upp - $maxLng)/$scaleFactor * 180/M_PI;
      	$geoTop =  atan(sinh((($chunk_starty + 1)*256*$upp - $maxLat)/$scaleFactor)) * 180/M_PI;
      	$geoBottom = atan(sinh((($chunk_stopy)*256*$upp - $maxLat)/$scaleFactor)) * 180/M_PI;

    	//а теперь создаем файл калибровки
    	$ozi = '';
    	$ozi .= "OziExplorer Map Data File Version 2.2\r\n";
    	$ozi .= "{$name}_{$chunk_i}_{$chunk_j}.jpg\r\n";
    	$ozi .= ".\\{$name}_{$chunk_i}_{$chunk_j}.jpg\r\n";
    	$ozi .= "1 ,Map Code,\r\n";
    	$ozi .= "WGS 84,WGS 84,   0.0000,   0.0000,WGS 84\r\n";
    	$ozi .= "Reserved 1\r\n";
    	$ozi .= "Reserved 2\r\n";
    	$ozi .= "Magnetic Variation,,,E\r\n";
    	$ozi .= "Map Projection,Latitude/Longitude,PolyCal,No,AutoCalOnly,No,BSBUseWPX,No\r\n";
    	$ozi .= "Projection Setup,,,,,,,,,,\r\n";
    	$ozi .= "Map Feature = MF ; Map Comment = MC     These follow if they exist\r\n";
    	$ozi .= "Track File = TF      These follow if they exist\r\n";
    	$ozi .= "Moving Map Parameters = MM?    These follow if they exist\r\n";
    	$ozi .= "MM0,Yes\r\n";
    	$ozi .= "MMPNUM,4\r\n";
    	$ozi .= "MMPXY,1,0,0\r\n";
    	$ozi .= "MMPXY,2,{$width_px},0\r\n";
    	$ozi .= "MMPXY,3,{$width_px},{$height_px}\r\n";
    	$ozi .= "MMPXY,4,0,{$height_px}\r\n";
    	$ozi .= "MMPLL,1,  {$geoLeft},  {$geoTop}\r\n";
    	$ozi .= "MMPLL,2,  {$geoRight},  {$geoTop}\r\n";
    	$ozi .= "MMPLL,3,  {$geoRight},  {$geoBottom}\r\n";
    	$ozi .= "MMPLL,4,  {$geoLeft},  {$geoBottom}\r\n";
    	$ozi .= "MM1B,0.790000\r\n";
    	$ozi .= "MOP,Map Open Position,0,0\r\n";
    	$ozi .= "IWH,Map Image Width/Height,{$width_px},{$height_px}";

    	file_put_contents('/'.$name.'_'.$chunk_i.'_'.$chunk_j.'.map',$ozi);
  }
?>