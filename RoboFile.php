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
	function pregReplaceInFile($file, $from, $to) {
		$this->taskReplaceInFile($file)
			->regex("[$from([^'\"]*)]")
			->to($to)
			->run();
	}

	function version() {

		$this->taskGitStack()
 			->stopOnFail()
 			->exec("git diff --name-only | grep -E '\.js|\.css|\.less' > gitdiff.txt")
 			->printed(true)
 			->run();

 		$destinationFiles = [
 			'public/be/js/scripts/app.js',
 			'public/fe/scripts/app.js',
 			'app/backend/views/layouts/main.volt',
 			'app/frontend/views/layouts/main.volt'
 		];

		$files = explode(PHP_EOL, (file_get_contents('gitdiff.txt')));

 		foreach($files as $file) {

			$filePath = substr($file, 7); //remove the 'public/'
			$newVersion = date('YmdHs');

			$fileVersion = $filePath . '?ver=' . $newVersion;

			if($filePath != '') {

				foreach($destinationFiles as $d) {

					$this->pregReplaceInFile($d, $filePath, $fileVersion);

				}

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
