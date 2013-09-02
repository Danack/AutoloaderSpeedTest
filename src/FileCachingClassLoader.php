<?php

namespace Intahwebz\Autoload;

function ensureDirectoryExists($filePath) {
    $pathSegments = array();

    $slashPosition = 0;
    $finished = false;

    while ($finished === false) {

        $slashPosition = mb_strpos($filePath, '/', $slashPosition + 1);

        if ($slashPosition === false) {
            $finished = true;
        }
        else {
            $pathSegments[] = mb_substr($filePath, 0, $slashPosition);
        }

        $maxPaths = 20;
        if (count($pathSegments) > $maxPaths) {
            throw new \Exception("Trying to create a directory more than $maxPaths deep. What is wrong with you?");
        }
    }

    foreach ($pathSegments as $segment) {
        //echo "check $segment<br/>";

        if (file_exists($segment) === false) {
            //echo "Had to create directory $segment";
            $result = mkdir($segment);

            if ($result == false) {
                throw new \Exception("Failed to create segment [$segment] in ensureDirectoryExists($filePath).");

                return false;
            }
        }
    }

    return true;
}


class FileCachingClassLoader
{

    private $pendingClass;

    /**
     * @var \Composer\Autoload\ClassLoader
     */
    private $composerAutoloader = null;
    
    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    /**
     * Unregisters this instance as an autoloader.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string    $class The name of the class
     * @return bool|null True if loaded, null otherwise
     */
    public function loadClass($class) {
        // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        $cachedFilename = $this->getCachedFilename($class);

      //  $cachedFilename = realpath($cachedFilename);
        
        $this->pendingClass = $class;
        set_error_handler(array($this, 'errorHandler'));//, int $error_types ] )

        include $cachedFilename;

        restore_error_handler();
        
        if ($this->pendingClass == null) {
            return true;
        }

        $this->pendingClass = null;

        return false; //failed to load.
    }
    
    function getCachedFilename($class) {
        
        if ($class == null) {
            return null;
        }
        
        $cacheDir = dirname(dirname(dirname(dirname(__DIR__)))).'/var/classCache/';

        $defaultFilename = $cacheDir.$class.".php";

        return $defaultFilename;
    }
    
    
    function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {

//        echo "error number is ".$errno."<br/>";
//        echo "errstr is ".$errstr."<br/>";

        if ($this->pendingClass == null) {
            return;
        }
        
        if (strrpos($errfile, "FileCachingClassLoader.php") === false) {
//            echo "Error wasn't called from FileCachingClassLoader.php";
            return;
        }

        $class = $this->pendingClass;
        
//        echo "Loading error: ".$this->pendingClass;

        if ($this->composerAutoloader == null) {
            $this->composerAutoloader = $this->getComposerLoader();
        }

        $filepath = $this->composerAutoloader->findFile($this->pendingClass);

        if (!$filepath) {
            //faild
            return true;
        }
        
        $cachedFilename = $this->getCachedFilename($class);

        ensureDirectoryExists($cachedFilename);
        
        $copied = copy($filepath, $cachedFilename);

        $this->pendingClass = null;

        include $cachedFilename;

        //echo "copied and included $cachedFilename";
        return true;
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public function getComposerLoader() {

        $vendorDir = dirname(dirname(dirname(__DIR__)));

        if (class_exists('\Composer\Autoload\ClassLoader', false) == false) {
            require $vendorDir.'/composer/ClassLoader.php';
        }

        $loader = new \Composer\Autoload\ClassLoader();

        $map = require $vendorDir.'/composer/autoload_namespaces.php';
        foreach ($map as $namespace => $path) {
            $loader->set($namespace, $path);
        }

        $classMap = require $vendorDir.'/composer/autoload_classmap.php';
        if ($classMap) {
            $loader->addClassMap($classMap);
        }

        return $loader;
    }
}


$loader = new \Intahwebz\Autoload\FileCachingClassLoader();

$loader->register(true);

return $loader;



 