<?php
// UPDATE `pharm_users_shops` SET `shop_status`='new' WHERE `engine` LIKE '2.0.1' AND `hosting` = 'inside' AND `user_id` = '1'
error_reporting('E_ALL');

final class moduleShop extends CronController
{
	/**
 	* @todo refactoring: replace $PTH_TO_SHOP_EXPORT with this constant
 	*/
	const PTH_TO_SHOP_EXPORT = '/var/www/shop_export/';
	/**
 	* Path to html version
 	*/
	const PATH_TO_DOWNLOAD   = '/var/www/tmp/';
    
    protected function executeOneInstall()
    {
   	 $PTH_TO_SHOP_EXPORT = '/var/www/shop_export/';
   	 
   	 $this->mysql = Db::getInstance('master');
   	 
   	 $file = file_get_contents($PTH_TO_SHOP_EXPORT.'tmp/main.php');
   	 
   		 $item = $this->mysql->getOne('
   		 SELECT
   			 `domain`, `shop_id`, `key`, (SELECT `xml_id` FROM pharm_users_shops_xml xml WHERE `xml`.`shop_id` = `shops`.`shop_id` ORDER BY `xml_id` DESC LIMIT 1) as `xml_id`  
   		 FROM `pharm_users_shops` `shops`
   		 WHERE
   			 `hosting` = "inside" and `shop_status`="new" and `engine` = "2.0.1" LIMIT 1', 'object');
   		 
   		 if(!$item)
   		 {
   			 die('no');
   		 }
   		 $this->mysql->query("UPDATE `pharm_users_shops` SET `shop_status`='in_process' WHERE `shop_id` = '{$item->get('shop_id')}' LIMIT 1");
   		 sleep(10);
   		 
   		 $shop_conf = PTH_TMP.'install/configs/'.$item->get('domain');
   		 
   		 if(file_exists($shop_conf))
   		 {
   			 exec('rm -R '.$shop_conf);
   		 }
   		 
   		 mkdir($shop_conf.'/data/', 777, true);
   		 mkdir($shop_conf.'/cache/', 777, true);
   		 
   		 $mainphp = str_replace(array('#DOMAIN#', '#KEY#'), array($item->get('domain'), $item->get('key')), $file);
   		 file_put_contents(PTH_TMP.'install/configs/'.$item->get('domain').'/main.php', $mainphp);
        	file_put_contents(PTH_TMP.'install/configs/'.$item->get('domain').'/revision_date.php', "<?php \n\n \$data = '".date('Y-m-d')."'; \n");
   		 
   		 $_GET['domain'] = $item->get('domain');
   		 $_GET['key'] = $item->get('key');
    
   		 ShopFeedController::factory('Data')->createZipArchive();
   		 
   		 # Unzip archive
   		 $exdir = $shop_conf.'/data/';
   		 $zip = PTH_TMP.'shop_data_'.$item->get('xml_id').'.zip';
   		 
   		 exec("/usr/bin/unzip $zip -d $exdir");
   		 
   		 exec('/bin/chmod -R 777 '.PTH_TMP.'install/configs/');
   		 //exec('/bin/chown -R www-data:www-data '.PTH_TMP.'install/configs/');
   		 
   		 //$upload_command = "/usr/bin/sudo -uwebmaster scp -r  $shop_conf webmaster@204.152.214.163:/var/www1/v2/configs/";
   		 
   		 //print($upload_command."\n");
   		 //exec($upload_command);
   		 
   		 
   		 //exec('rm -R '.$shop_conf);
   		 
   		 $this->mysql->query("UPDATE `pharm_users_shops` SET `shop_status`='installed', `gzip` = '1' WHERE `shop_id` = '{$item->get('shop_id')}' LIMIT 1");
   		 
   		 die("Installed {$item->get('domain')} {$item->get('user_id')} ".memory_get_peak_usage()."\n");
    }
    
    protected function executeInstall()
    {
   	 while(true)
   	 {
   		 $str = exec('cd /var/www/cron/; /usr/bin/php OneShopInstall.php;', $output);
   		 
   		 if($str == 'no')
   		 {
   			 break;
   		 } else
   		 {
   			 $output = implode("\n", $output);
   			 
   			 if(strpos($output, 'Unknown error'))
   			 {
   				 die(__LINE__.': Error:'.$output);    
   			 }
   			 
   			 if(strpos($str, 'Installed') === false)
   			 {
   				 var_dump($output);
   			 } else
   			 {
   				 print($str."\n");
   			 }
   		 }
   	 }
   	 die('ok');
    }
    
    protected function executeOneUpdate()
    {
   	 try
   	 {
   		 $this->mysql = Db::getInstance('master');
   		 
   		 $shop = $this->mysql->getOne('SELECT `domain`, `shop_id` FROM `pharm_users_shops` WHERE `apply_changes` = "waiting" and `shop_status`="installed" ORDER BY `apply_changes_date` ASC LIMIT 1');
   		 if(!$shop)
   		 {
   			 die('no');
   		 }
   		 sleep(10);
   		 $this->mysql->query("UPDATE `pharm_users_shops` SET `apply_changes` = 'in_process' WHERE `shop_id` = '{$shop['shop_id']}' LIMIT 1");
   		 $this->shop = Model::factory('Shop')->getByName($shop['domain']);
   			 
   		 //if($this->shop->get('gzip') && in_array($this->shop->get('user_id'), array('7139', '1')))
   		 if($this->shop->get('gzip'))
   		 {
   			 $this->updateShopWithZip();
   		 } else
   		 {
   			 $this->updateShopWithoutZip();
   		 }
   			 
   		 $this->mysql->query("UPDATE `pharm_users_shops` SET `apply_changes` = 'complete' WHERE `shop_id` = '{$shop['shop_id']}' LIMIT 1");   			 
   		 
   		 die(date('H:i:s', time()).": Updated {$this->shop->get('domain')} {$this->shop->get('user_id')} ".memory_get_peak_usage()."\n");
   	 } catch(Exception $e)
   	 {
   		 $this->mysql->query("UPDATE `pharm_users_shops` SET `apply_changes` = 'error' WHERE `shop_id` = '{$shop['shop_id']}' LIMIT 1");
   		 $this->sendMsg($e->getMessage());
   		 print($e->getMessage());
   		 exit;
   	 }
    }
    protected function executeUpdate()
    {
   	 # protection
   	 
   	 /*
   	 $this->cronname = 'ShopUpdateAllFiles';
   	 
   	 if($this->isStarted())
   	 {
   		 $this->setMsg('can\'t start, its worked');
   		 die('can\'t start, its worked');
   	 }
   	 */
   	 $i = 0;
   	 while(true)
   	 {
   		 $str = exec('cd /var/www/cron/; /usr/bin/php OneShopUpdate.php;', $output);
   		 
   		 $output = implode("\n", $output);
   		 
   		 if($output == 'no')
   		 {
   			 break;
   		 } else
   		 {
   			 if(strpos($output, 'Unknown error'))
   			 {
   				 die(__LINE__.': Error:'.$str);    
   			 }
   			 
   			 if(strpos($output, 'Updated') === false)
   			 {
   				 var_dump($output);
   			 } else
   			 {
   				 print($output."\n");
   			 }
   		 }
   		 
   		 if($i > 90)
   		 {
   			 die('i > 90');
   		 }
   	 }
   	 die('ok update complete');

    }
    
    private function updateShopWithZip()
    {
   	 $memcache = new Memcache;
   	 $memcache->connect('localhost', 11211);
   	 $memcache->set('update_'.$this->shop->get('shop_id') , 0);
   	 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Start update (with zip)');
   	 
   	 $_GET['domain'] = $this->shop->get('domain');
   	 $_GET['key'] = $this->shop->get('key');
   	 
   	 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Domain and key is defined');
   	 
   	 if(ShopFeedController::factory('Data')->createZipArchive())
   	 {
   		 if($this->shop->get('hosting') == 'inside')
   		 {
   			 /* Апдейт шопов которые у нас хостятся */
   			 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Data for inside hosting site');
   			 
   			 $exdir = PTH_TMP.'install/configs/'.$this->shop->get('domain').'/data';
   			 $zip = PTH_TMP.'shop_data_'.$this->shop->getConfig()->get('xml_id').'.zip';
   			 
   			 if(file_exists($exdir) || true){
   				 exec('/bin/rm '.$exdir.'/*');
   				 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Old data removed');
   			 }
   			 
   			 exec("/bin/mkdir -p '$exdir/'; /usr/bin/unzip -o '$zip' -d '$exdir/'" );
   				 
   			 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Unzip new data');
   			 exec('/bin/chmod -R 777 '.$exdir);   			 
   			 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Update for inside hosting complete');
   			 $memcache->set('update_'.$this->shop->get('shop_id') , 100);
   			 
   			 
   			 return true;
   			 /*//  Апдейт шопов которые у нас хостятся */
   		 }
   		 
   		 $memcache->set('update_'.$this->shop->get('shop_id') , 99);
   		 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Upload file');
   	 }
   	 
   	 
   	 
   	 $data = array();
   	 $data['f'] = '../data.zip';
   	 $data['content'] = '@'.PTH_TMP.'shop_data_'.$this->shop->getConfig()->get('xml_id').'.zip';
   	 $data['domain'] = $this->shop->get('domain');
   	 $data['key'] = $this->shop->get('key');
   	 $data['debug'] = 1;

   	 $http = new Http();
   	 $http->timeout = 900;
   	 //unset($response);
   	 
   	 $response = $http->postProxy(
   						 Request::buildQuery($this->shop->getSyncUrl(), array('ac'=>'uploadFile'), false)
   						 , $data, false);
   	 
   	 $http_response = json_decode($response, true);
   	 if(isset($http_response['error']) && $http_response['error'] === false)
   	 {
   		 $http = new Http();
   		 $http->timeout = 900;
   		 $response = $http->getProxy($this->shop->getSyncUrl(), array(
   			 'ac' => 'archive',
   			 'domain' => $this->shop->get('domain'),
   			 'key' => $this->shop->get('key')
   		 ));
   		 
   		 $http_response = json_decode($response, true);
   		 
   		 if(isset($http_response['error']) && $http_response['error'] === false)
   		 {
   			 $memcache->set('update_'.$this->shop->get('shop_id') , 100);
   			 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , '');
   			 
   			 # Очищаем кешь
   			 $http = new Http();
   			 $http->timeout = 25;
   			 $http->getProxy($this->shop->getSyncUrl(),
   				 array(
   					 'ac' => 'clearCache',
   					 'domain' => $this->shop->get('domain'),
   					 'key' => $this->shop->get('key')
   				 ));
   						 
   			 $memcache->close();
   		 } else
   		 {
   			 throw new Exception('Shop update error:'.$response);
   		 }
   	 } else
   	 {
   		 var_dump($http);
   		 $err_message = ( isset( $http_response['msg'] )) ? "::{$http_response['msg']}" : '::Error not defined';
   		 throw new Exception('Shop update error 120: '.$http->last['url']."\n".$response.$err_message);
   	 }
   	 
    }
    
    private function updateShopWithoutZip()
    {
   	 $http = new Http();
   	 $http->timeout = 50;
   	 
    	$mysql = Db::getInstance('master');
   		 
    	$feedServerUrl = $mysql->getOne('SELECT `url` FROM `pharm_shop_services_urls` WHERE `url_name`="feed-v2" AND `is_active`=1');
    	$feedServerUrl = rtrim($feedServerUrl, '/');
    	$feedServerUrl = 'http://' . $feedServerUrl . '/';
   			 
   	 $response = $http->get($feedServerUrl.'data/', array(
   		 'domain' => $this->shop->get('domain'),
   		 'key' => $this->shop->get('key'),
   		 'mode' => 'production',
   		 'encode' => 'json'
   	 ));

   	 $memcache = new Memcache;
   	 $memcache->connect('localhost', 11211);
   	 $memcache->set('update_'.$this->shop->get('shop_id') , 0);
   	 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Connect to feed server');
   	 
   	 //$response = file_get_contents($feedServerUrl."data/?domain={$this->shop->get('domain')}&key={$this->shop->get('key')}&mode=production&encode=json");
   	 $response = json_decode($response, true);
   	 if(!$response)
   	 {
   		 throw new Exception('Can\'t recieve data for: '.$feedServerUrl."data/?domain={$this->shop->get('domain')}&key={$this->shop->get('key')}&mode=production&encode=json");
   	 }
   		 
   	 if(!isset($response['files']))
   	 {
   		 throw new Exception('Key files not found in: '.$feedServerUrl."data/?domain={$this->shop->get('domain')}&key={$this->shop->get('key')}&mode=production&encode=json");
   	 }
   	 $files = &$response['files'];
   	 unset($response['files']);
   		 
   	 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Start process');
   	 
   	 $total_files = count($files);
   		 
   	 Time::start('shop');
   	 foreach($files as $key => $item)
   	 {
   		 /* update status */
   		 $current_files = count($files);
   		 $percent = intval(($total_files - $current_files)*100/$total_files);
   		 $memcache->set('update_'.$this->shop->get('shop_id'), $percent);
   		 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Get file #'.$key );
   		 unset($files[$key]);
   			 
   		 $url = parse_url($item);
   		 if(empty($url['query']))
   		 {
   			 $url['query'] = '';
   		 } else
   		 {
   			 $url['query'] .= '&';
   		 }
   			 
   		 $url['query'] .= "domain={$this->shop->get('domain')}&key={$this->shop->get('key')}&mode=production&encode=php";
   		 $url = "{$url['scheme']}://{$url['host']}{$url['path']}?{$url['query']}";
   		 
   		 $content = file_get_contents($url);
   		 if($content == 'Auth error')
   		 {
   			 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Auth error' );
   			 throw new Exception('auth error in: '.$url);
   		 }
   			 
   		 $data = array();
   		 $data['f'] = $key;
   		 $data['content'] = "<?php \n $content";
   		 $data['domain'] = $this->shop->get('domain');
   		 $data['key'] = $this->shop->get('key');

   		 $http = new Http();
   		 $http->timeout = 25;

   		 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Save file #'.$key );
   		 
   		 $syncUrl = $this->shop->getSyncUrl().'?ac=saveFile';
   		 $http_response = $http->postProxy( $syncUrl, $data );
   		 $response = json_decode($http_response, true);
   			 
   		 if(isset($response['error']) && $response['error'] === false)
   		 {
   			 //print($http_response);
   			 # Все хорошо
   		 } else
   		 {
   			 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , 'Unknown error' );
   			 throw new Exception('unknown error for save file in the shop: '.$http_response.';syncUrl='.$syncUrl);
   		 }
   			 
   	 }
   	 Time::end('shop');
   		 
   	 /*
   	 $this->end();
   	 die('cool');
   	 */
   		 
   	 print('ok: '. Time::get('shop'));
   		 
   	 # Очищаем кешь
   	 $http = new Http();
   	 $http->timeout = 25;
   	 $http->getProxy($this->shop->getSyncUrl(),
   		 array(
   			 'ac' => 'clearCache',
   			 'domain' => $this->shop->get('domain'),
   			 'key' => $this->shop->get('key')
   		 ));
   	 $memcache->set('update_'.$this->shop->get('shop_id').'_msg' , '' );
   	 
   	 $memcache->close();
    }
    
	/**
 	* Searching for first shop in a waiting queue
 	* Then starting to generate html version for it
 	*/
	protected function executeGenerateHtml()
	{
    	if(Model::factory('HtmlShop')->isInProcess()) {
        	die("Generating html is in process\r\n");
    	}
   	 
    	$shop_id = Model::factory('HtmlShop')->getFirstWaiting();
    	if(empty($shop_id)) {
        	die("No shops waiting\r\n");
    	}
    	$this->shop = Model::factory('Shop')->getFullInfoByShopId($shop_id);
   	 
    	//var_dump($this->shop);

    	$dirName = self::PATH_TO_DOWNLOAD . "shop{$this->shop->get('tpl')}_html_preview";
   	 
    	if (!is_dir($dirName)) {
        	echo "Creating shop with {$this->shop->get('tpl')} template...\r\n";
        	$this->createShop(self::PTH_TO_SHOP_EXPORT, $dirName);
    	}
    	if(file_exists(self::PATH_TO_DOWNLOAD . 'shop_html_preview')) {
        	unlink(self::PATH_TO_DOWNLOAD . 'shop_html_preview');
    	}
    	echo "Creating symlink...\r\n";
    	symlink($dirName, self::PATH_TO_DOWNLOAD . 'shop_html_preview');
//    	echo "Creating configs...\r\n";
//    	$this->setConfigs($dirName);
//    	$this->setMainConfig($dirName);
    	echo "Generating HTML...\r\n";
    	$start = microtime(true);
    	$this->createHtmlVersion();
    	$fin = microtime(true);
    	$time_spent = $fin - $start;
    	echo "$time_spent seconds spent\r\n";

	}
    
	private function createShop ($source, $target)
	{
    	if (is_dir($source)) {
        	@mkdir($target, 0777);
        	chmod($target, 0777);
        	$d = dir($source);
        	while (FALSE !== ($entry = $d->read())) {
            	if ($entry == '.' || $entry == '..') {
                	continue;
            	}
            	$this->createShop("$source/$entry", "$target/$entry");
        	}
        	$d->close();
    	} else {
        	copy($source, $target);
    	}
	}
    
	private function deleteOldConfigs ($dir) {
    	if (is_dir($dir)) {
        	$objects = scandir($dir);
        	foreach ($objects as $object) {
            	if ($object != "." && $object != "..") {
                	if (filetype($dir."/".$object) == "dir") {
                    	$this->deleteOldConfigs($dir."/".$object);
                	}
                	else unlink($dir."/".$object);
            	}
        	}
        	reset($objects);
        	rmdir($dir);
    	}
	}
    
	private function setConfigs ($path)
	{
    	set_time_limit(0);
    	ini_set('memory_limit', '1G');
   	 
    	$zipPath = $path.'/configs';
   	 
    	//delete old configs folder and create new
    	$this->deleteOldConfigs($zipPath);
    	$zipPath .= "/shop_html_preview";
    	mkdir($zipPath, 0777, true);
   	 
    	chmod($zipPath, 0777);
   	 
   	 
    	//set configs zip to folder
    	$_GET['domain'] = $this->shop->get('domain');
   	 $_GET['key'] = $this->shop->get('key');
    	ShopFeedController::factory('Data')->createZipArchive($zipPath.'/data.zip');
   	 
    	if (file_exists($zipPath.'/data.zip')) {
        	chmod($zipPath.'/data.zip', 0777);

        	//extract files from zip
        	$zip = new ZipArchive();
        	$res = $zip->open($zipPath.'/data.zip');
        	if ($res === TRUE) {
            	$zip->extractTo($zipPath.'/data');
            	$zip->close();
            	unlink($zipPath.'/data.zip');
        	}
    	}
    	mkdir("$zipPath/cache", 0777);
    	chmod($zipPath, 0777);
    	chmod("$zipPath/cache", 0777);
	}
    
	private function setMainConfig ($path)
	{
    	include($path.'/tmp/main.php');
    	$data['auth']['domain'] = $this->shop->get('domain');
    	$data['auth']['key'] = $this->shop->get('key');
   	 
    	$mainConfigPath = $path.'/configs/shop_html_preview/main.php';
    	file_put_contents($mainConfigPath, "<?php \n\n \$data = ".var_export($data, true)."; \n");
    	chmod($mainConfigPath, 0777);
	}
    
	private function createHtmlVersion()
	{
    	require_once(self::PATH_TO_DOWNLOAD . 'shop_html_preview/configs/shop_html_preview/data/sitemap.php');

    	$pages_count = count($data);
    	$pages_by_step = 1000;
    	$step = 1;
    	$num = 0;
    	$urls = array();
   	 
    	for($i = 0; $i < 1000; $i++) {
        	if($data[$i]['url'] == ':shop_domain:') {
            	continue;
        	}
        	$urls[] = 'http://shop_html_preview/' . $data[$i]['url'];
    	}
    	$mh = curl_multi_init();   	
    	$connectionArray = array();
    	foreach($urls as $key => $url)
    	{
            	$ch = curl_init();
            	curl_setopt($ch, CURLOPT_URL, $url);
            	curl_setopt($ch, CURLOPT_HEADER, false);
            	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            	curl_multi_add_handle($mh, $ch);
            	$connectionArray[$key] = $ch;
    	}
    	$running = null;
    	do
    	{
        	curl_multi_exec($mh, $running);
    	}while($running > 0);

    	foreach($connectionArray as $key => $ch)
    	{
            //здесь получаю контент страницы
            $content = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
    	}

    	curl_multi_close($mh);
   	 
    	for($i = 0; $i < count($contents); $i++) {
        	if(!is_dir("/var/www/tmp/htmlversion/{$page_names[$i]}")){
             	$file = fopen("/var/www/tmp/htmlversion/{$page_names[$i]}", "w");
             	fwrite($file, $contents[$i]);
             	fclose($file);
         	}
    	}
   	 
    	//for($step = 1; $step < ceil($pages_count / $pages_by_step); $step++)
//    	{
//

//        	//for($i = ($step - 1) * $pages_by_step; $i < $pages_by_step * $step && $i < $pages_count; $i++) {
//        	for($i = 0; $i < 100; $i++) {
//            	$urls[] = 'http://shop_html_preview/' . $data[$i]['url'];
//        	}
//
        	$mh = curl_multi_init();
        	$chs = array();

        	foreach ($urls as $url) {
            	$chs[] = ( $ch = curl_init() );
            	curl_setopt($ch, CURLOPT_URL, $url);
            	curl_setopt($ch, CURLOPT_HEADER, 0);
            	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            	curl_multi_add_handle($mh, $ch);
        	}

        	$running = null;


        	do {
            	curl_multi_exec($mh, $running);

            	$info = curl_multi_info_read($mh);

            	if (is_array($info) && ( $ch = $info['handle'] )) {
                	// получаю содержимое загруженной страницы
                	$content = curl_multi_getcontent($ch);
            	}           	 
        	} while ($running);

        	foreach ($chs as $ch) {
            	curl_multi_remove_handle($mh, $ch);
            	curl_close($ch);
        	}
        	curl_multi_close($mh);
//
//        	echo "Generated $num pages! \r\n";
//        	sleep(1);
//    	}

//    	foreach ($data as $page) {
//        	try{
//            	$start = microtime(true);
//            	$url = 'http://shop_html_preview/'.$page['url'];
//           	 
//            	if($page['url'] == ':shop_domain:') continue;
//           	// echo "Generating $url...\r\n";
//            	$content = file_get_contents($url);
//            	if(!is_dir("/var/www/tmp/htmlversion/{$page['url']}")) {
//                	file_put_contents("/var/www/tmp/htmlversion/{$page['url']}", $content);
//            	}
//            	echo "done!\r\n";
//            	//$zip->addFromString($page['url'], $content);
//        	} catch (Exception $e) {
//            	echo $e->getMessage();
//        	}
//    	}
	}

}




