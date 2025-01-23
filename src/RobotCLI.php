<?php declare(strict_types=1);

namespace App;

class RobotCLI
{
    public function __construct(
        protected ?Robot $robot = null,
        protected bool $robotInitialized = false
    ) {}

    /**
     * Takes in the file and uses it to start the Robot class
     *
     * @param  string $file The file robots.php uses
     * @return void
     */
    public function initializeRobot(string $file): void
    {
        $directory = dirname(__FILE__, 2).'/';
        $resource = fopen($directory.$file, 'r');
        $this->loopUntilEOF($resource);
        fclose($resource);
    }

    /**
     * Loops through the file one line at a time until
     * the end of the file is reached
     *
     * @param  string $resourceFile The resource robots.php uses, based on the file
     * @return void
     */
    protected function loopUntilEOF($resourceFile): void
    {
        while(!feof($resourceFile)) {
            $argument = fgets($resourceFile);

            // One past the last in feof() is boolean:
            if ($argument === false) {
                break;
            }

            // Blank lines, comments:
            if (empty($argument) || substr($argument, 0, 1) == '#') {
                continue;
            }

            if (!$this->robotInitialized) {
                // In case anything other than a number is included as input.txt's first line,
                // e.g. "10 blocks":
                if (strpos($argument, ' ') !== false) {
                    $argument = explode(' ', $argument)[0];
                }
                $howManyBlocks = intval($argument);
                if (!$this->robot) {
                    $this->robot = new Robot($howManyBlocks);
                }
                $this->robotInitialized = true;
                continue;
            }

            if (substr($argument, 0, 6) == 'print ') {
                $argument = substr($argument, 6);
                echo $argument;

                $splitIntoSeparateArguments = explode(' ', $argument);
                $whichMethod = $this->roboticizeString($splitIntoSeparateArguments);
                $this->useMethod($whichMethod, $splitIntoSeparateArguments);
                $this->useMethod('quit', $splitIntoSeparateArguments);
                continue;
            }

            $splitIntoSeparateArguments = explode(' ', $argument);
            $whichMethod = $this->roboticizeString($splitIntoSeparateArguments);
            $this->useMethod($whichMethod, $splitIntoSeparateArguments);;
        }
    }

    /**
     * Creates an intermediate form of the string, which will be converted
     * into a method in the Robot class
     *
     * @param  array  $splitIntoSeparateArguments An array, split from a natural language string given as input
     * @return string The intermediate string which is converted in $this->useMethod()
     */
    protected function roboticizeString(array $splitIntoSeparateArguments): string
    {
        // Cleaning up the natural language:
        $initialMethod = '';
        $howLong = count($splitIntoSeparateArguments);
        if ($howLong > 2) {
            $initialMethod = $splitIntoSeparateArguments[0].$splitIntoSeparateArguments[2];
        } else {
            // For initialization (first line of .txt file), echo, and quit():
            $initialMethod = $splitIntoSeparateArguments[0];
        }

        $trimmedMethod = trim($initialMethod);
        $lowerCaseMethod = strtolower($trimmedMethod);
        $whichMethod = preg_replace('#[^a-zA-Z0-9]#', '', $lowerCaseMethod);

        return $whichMethod;
    }

    /**
     * Uses the methods in the Robot class
     *
     * @param  string $whichMethod The intermediate string which is converted
     *                             into a full-fledged method, with arguments
     * @param  array  $splitIntoSeparateArguments The original argument from the resource, split into
     *                             an array which contains the arguments for each Robot function
     * @return void
     */
    protected function useMethod(string $whichMethod, array $splitIntoSeparateArguments): void
    {
        switch ($whichMethod) {

            case 'moveonto':
                $blockA = intval($splitIntoSeparateArguments[1]);
                $blockB = intval($splitIntoSeparateArguments[3]);
                $this->robot->moveOnto($blockA, $blockB);
                break;

            case 'moveover':
                $blockA = intval($splitIntoSeparateArguments[1]);
                $blockB = intval($splitIntoSeparateArguments[3]);
                $this->robot->moveOver($blockA, $blockB);
                break;

            case 'pileonto':
                $blockA = intval($splitIntoSeparateArguments[1]);
                $blockB = intval($splitIntoSeparateArguments[3]);
                $this->robot->pileOnto($blockA, $blockB);
                break;

            case 'pileover':
                $blockA = intval($splitIntoSeparateArguments[1]);
                $blockB = intval($splitIntoSeparateArguments[3]);
                $this->robot->pileOver($blockA, $blockB);
                break;

            case 'quit':
                $this->robot->quit();
                break;

            default:
                break;
        }
    }
}
