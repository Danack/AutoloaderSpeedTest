<?php



namespace Intahwebz\Autoload;


class NonOverloadedNamespaceClassLoader
{
    private $prefix = "Intahwebz_ClassLoader";
    
    private $prefixes = array();

    /**
     * Registers a set of classes, replacing any others previously set.
     *
     * @param string       $prefix The classes prefix
     * @param array|string $paths  The location(s) of the classes
     */
    public function set($prefix, $paths){

        var_dump($prefix);
        var_dump($paths);
        exit(0);
        
        $this->prefixes[substr($prefix, 0, 1)][$prefix] = (array) $paths;
    }

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
    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            include $file;

            return true;
        }
        //return false;
    }

    
    public function findFile($class) {

//        $file = apc_fetch($this->prefix.$class);

  //      if ($file === false) {
            $file = $this->findFileInternal($class);
            
//            if ($file) {
//                apc_store($this->prefix.$class, $file);
//                return $file;   
//            }
//            return false;
//        }

        return $file;
    }
        
    
    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|false The path if found, false otherwise
     */
    public function findFileInternal($class)
    {

        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        } else {
            // PEAR-like class name
            $classPath = null;
            $className = $class;
        }

        $classPath .= strtr($className, '_', DIRECTORY_SEPARATOR) . '.php';

        $first = $class[0];
        if (isset($this->prefixes[$first])) {
            foreach ($this->prefixes[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
                            return $dir . DIRECTORY_SEPARATOR . $classPath;
                        }
                    }
                }
            }
        }

        return false;
    }
}


$loader = new \Intahwebz\Autoload\APCCacheClassLoader();

$filepath = __DIR__.'/../../../composer/autoload_namespaces.php';
$map = require $filepath;

foreach ($map as $namespace => $paths) {

    $path = $paths[0];
    $loader->set($namespace, $path);
}

$loader->register(true);

return $loader;



 