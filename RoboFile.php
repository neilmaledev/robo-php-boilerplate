<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
​
	function replaceInFile($file, $from, $to) {
		$this->taskReplaceInFile($file)
			->from($from)
			->to($to)
			->run();
	}
​
	function version() {
		$this->taskGitStack()
 			->stopOnFail()
 			->exec("git diff --name-only | grep -E '\.js|\.css' > gitdiff.txt")
 			->printed(true)
 			->run();
 			
 		$appFile = 'public/be/js/scripts/app.js';
		$vFile = 'versions.txt';
		$files = explode(PHP_EOL, (file_get_contents('gitdiff.txt')));
​
 		foreach($files as $file) {
​
			$filePath = substr($file, 7); // remove the 'public/'
			$newVersion = date_format(date_create(), 'YmdHs');
​
			if(is_file($vFile)) {
​
				$new = false;
​
				$versions = explode(PHP_EOL, (file_get_contents($vFile)));
​
				foreach($versions as $v) {
​
					$version = explode('?ver=', $v);
​
					if($version[0] == $filePath) {
​
						$new = false;
​
						$fileVersion = $filePath . '?ver=' . $newVersion;
​
						$this->replaceInFile($appFile, $v, $fileVersion);
						$this->replaceInFile($vFile, $v, $fileVersion);
​
						break;
​
					} else {
						$new = true;
					}
				}
​
				if($new == true) {
​
					$fileVersion = $filePath . '?ver=' . $newVersion;
​
					//new line (new version)
					$current = file_get_contents($vFile);
					$current .= $fileVersion . "\n";
					file_put_contents($vFile, $current);
​
					$this->replaceInFile($appFile, $filePath, $fileVersion);
				}
​
			} else {
​
				$fileVersion = $filePath . '?ver=' . $newVersion;
​
				$this->taskWriteToFile('versions.txt')
				->line( $fileVersion )
				->run();
​
				$this->replaceInFile($appFile, $filePath, $fileVersion);
​
			}
 		}
	}

	public function vhostconfig($fastcgi_pass = "") {
        $site = $this->ask("Enter site name: ");
        if(!is_file("/etc/nginx/sites-available/".$site)){
            
            $fp =  ($fastcgi_pass === "") ? "unix:/var/run/php5-fpm.sock" : $fastcgi_pass;
​
            $dir = $this->ask("Enter location of your project: ");
            $this->taskWriteToFile("/etc/nginx/sites-available/".$site)
            ->line("server {")
            ->line("listen  80; server_name ".$site."; error_log /var/log/nginx/".$site.".error.log  error; root ".$dir."; index index.php index.html;")
            ->line("location / { if (-f $"."request_filename) { break; } if (!-e $"."request_filename) { rewrite ^(.+)$ /index.php?_url=$"."1 last; break; } }")
            ->line("location ~ \.php$ { fastcgi_pass ".$fp."; include fastcgi_params; fastcgi_param SCRIPT_FILENAME $"."document_root"."$"."fastcgi_script_name; } ")
            ->line("location ~* \.(jpg|jpeg|gif|css|png|js|ico)$ { access_log off; expires 30d; break; } ")
            ->line("}")
            ->run();
            $this->taskExec("ln -s /etc/nginx/sites-available/".$site." /etc/nginx/sites-enabled/".$site)->run();
            $file = '/etc/hosts';
            $current = file_get_contents($file);
            $current .= "\n127.0.0.1     ".$site;
            file_put_contents($file, $current);
            $this->taskExec("service nginx restart")->run();
        } else {
            $this->say("The site name is not available");
        }
    }
​
}
