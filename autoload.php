<?php declare(strict_types=1);

class Psr4AutoloaderClass
{
    public function __construct(protected array $prefixedBaseDirs = []) {}

    /**
     * Register the autoloader with SPL autoloader queue.
     *
     * @return void
     */
    public function registerNewAutoloader(): void
    {
        spl_autoload_register([$this, 'loadClassFromFQCN']);
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $namespacePrefix The <NamespaceName> and possible <SubnamespaceName>s
     * @param string $baseDirectory The directory in the filesystem, to which we will add
     *               the $namespacePrefix
     *
     * @return void
     */
    public function addPrefixedBaseDirectoryForThisClass(string $namespacePrefix, string $baseDirectory): void
    {
        // normalize namespace prefix (get rid of all "\" at beginning and end,
        // then add 1 "\" to end):
        $namespacePrefix = trim($namespacePrefix, '\\') . '\\';

        // normalize the base directory with a trailing separator:
        $baseDirectory = rtrim($baseDirectory, DIRECTORY_SEPARATOR) . '/';

        // Necessary for array_push(), and can't do it in constructor
        // w/o hard-coding the $baseDirectory (which is supposed to be dynamic):
        $this->prefixedBaseDirs[$namespacePrefix] ??= [];

        array_push($this->prefixedBaseDirs[$namespacePrefix], $baseDirectory);
    }

    /**
     * Loads the class file for a given class name. Here's how its done:
     *
     * \<NamespaceName>(\<SubNamespaceNames>)*\<ClassName>, instantiated to e.g.:
     *
     *      \Namespace\Prefix\Subnamespace\ClassName
     *
     * Iteration 0:
     *      Prefix: \Namespace\Prefix\Subnamespace
     *      Class:  \ClassName
     *
     * Iteration 1:
     *      Prefix: \Namespace\Prefix
     *      Class:  \Subnamespace\ClassName
     *
     * Iteration 2:
     *      Prefix: \Namespace
     *      Class:  \Prefix\Subnamespace\ClassName
     *      If not found, stop
     *
     * NB: Each member of 'Class' above is a separate dir, e.g. for Iteration 2:
     *      [BaseDirWithPrefix]/Prefix/Subnamespace/ClassName.php
     *
     * @param string $FQCN       The fully qualified class name.
     * @return mixed bool|string The filename or false on failure
     */
    public function loadClassFromFQCN($FQCN): bool|string
    {
        $namespacePrefix = $FQCN;
        $lastBackslash = strrpos($namespacePrefix, '\\');

        while ($lastBackslash !== false) {
            $startFromBeginningOfString = 0;
            $endAtLastBackslash = $lastBackslash + 1;
            $namespacePrefix = substr($FQCN, $startFromBeginningOfString, $endAtLastBackslash);

            $startFqcnFromLastBackslash = $lastBackslash + 1;
            $classWithPossiblePostfix   = substr($FQCN, $startFqcnFromLastBackslash);

            $fileName = $this->loadFile($namespacePrefix, $classWithPossiblePostfix);
            if ($fileName) {
                return $fileName;
            }

            $namespacePrefix = rtrim($namespacePrefix, '\\');
            $lastBackslash   = strrpos($namespacePrefix, '\\');
        }

        return false;
    }

    /**
     * Load the file iff it exists
     *
     * @param string $namespacePrefix
     * @param string $classWithPossiblePostfix
     *
     * @return mixed bool|string (name of loaded file)
     */
    protected function loadFile($namespacePrefix, $classWithPossiblePostfix): bool|string
    {
        // Check if we've saved the current $namespacePrefix with $this->addPrefixedBaseDirectoryForThisClass()
        // in the protected $prefixedBaseDirs array:
        if (!isset($this->prefixedBaseDirs[$namespacePrefix])) {
            return false;
        }

        // If we have, then look through base directories for this namespace prefix:
        foreach ($this->prefixedBaseDirs[$namespacePrefix] as $prefixedBaseDir) {
            // Glue together the whole name of the class in the filesystem:
            $file = $prefixedBaseDir .
                    str_replace('\\', '/', $classWithPossiblePostfix) .
                    '.php';

            // It's possible that we added the class in $this->addPrefixedBaseDirectoryForThisClass(),
            // but the directories and / or class doesn't actually exist in the filesystem:
            if (file_exists($file)) {
                require $file;
                return $file;
            }
        }

        return false;
    }
}

$autoloader = new \Psr4AutoloaderClass;
$autoloader->registerNewAutoloader();
$namespacePrefix = '\\App\\';
$baseDirWithPrefix = __DIR__;
$autoloader->addPrefixedBaseDirectoryForThisClass($namespacePrefix, $baseDirWithPrefix . '/src');
$autoloader->addPrefixedBaseDirectoryForThisClass($namespacePrefix, $baseDirWithPrefix . '/tests');
