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
			$filePath = substr($file, 7); //remove the 'public/'
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
					// break;
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
​
}
