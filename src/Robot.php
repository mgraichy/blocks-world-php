<?php declare(strict_types=1);

namespace App;

class Robot
{
    /**
     * Constructs an array of blocks as heap nodes for the robot arm to manipulate,
     * with a minimum of 1 block and a max of 25 blocks
     *
     * @param integer $howManyBlocks Number of blocks in the array
     */
    public function __construct(int $howManyBlocks = 1, protected array $blocks = [])
    {
        if ($howManyBlocks < 1) {
            $howManyBlocks = 1;
        }

        if ($howManyBlocks > 25) {
            $howManyBlocks = 25;
        }

        for ($i = 0; $i < $howManyBlocks; ++$i) {
            $this->blocks[] = new Block($i);
        }
    }

    /**
     * move a onto b
     *     where a and b are block numbers,
     *     puts block a onto block b, after returning any blocks that are
     *     stacked on top of both blocks a and b to their initial positions
     *
     * @param  int $a A block number
     * @param  int $b Another block number
     * @return void
     */
    public function moveOnto(int $a, int $b): void
    {
        if (!$blocksArrayKeys = $this->getLegalKeys($a, $b)) {
            return;
        }

        $blockA = $this->resetBlocks($blocksArrayKeys['a'], $a, deleteBlockNumberForBlockA: true);
        $this->resetBlocks($blocksArrayKeys['b'], $b);
        $this->stackBlocks($blockA, $blocksArrayKeys['b'], $b);
    }

    /**
     * move a over b
     *      where a and b are block numbers,
     *      puts block a onto the top of the stack containing block b,
     *      after returning any blocks that are stacked on top of block a
     *      to their initial positions.
     *
     * @param  int $a A block number
     * @param  int $b Another block number
     * @return void
     */
    public function moveOver(int $a, int $b): void
    {
        if (!$blocksArrayKeys = $this->getLegalKeys($a, $b)) {
            return;
        }

        $blockA = $this->resetBlocks($blocksArrayKeys['a'], $a, deleteBlockNumberForBlockA: true);
        $this->stackBlocks($blockA, $blocksArrayKeys['b'], $b);
    }

    /**
     * pile a onto b
     *      where a and b are block numbers,
     *      moves the pile of blocks consisting of block a,
     *      and any blocks that are stacked above block a, onto block b.
     *      All blocks on top of block b are moved to their initial positions
     *      prior to the pile taking place.
     *      The blocks stacked above block a retain their order when moved.
     *
     * @param  int $a A block number
     * @param  int $b Another block number
     * @return void
     */
    public function pileOnto(int $a, int $b): void
    {
        if (!$blocksArrayKeys = $this->getLegalKeys($a, $b)) {
            return;
        }

        $this->resetBlocks($blocksArrayKeys['b'], $b);
        $blockA = $this->getBlockStack($blocksArrayKeys['a'], $a);
        $this->stackBlocks($blockA, $blocksArrayKeys['b'], $b);
    }

    /**
     * pile a over b
     *      where a and b are block numbers,
     *      puts the pile of blocks consisting of block a,
     *      and any blocks that are stacked above block a,
     *      onto the top of the stack containing block b.
     *      The blocks stacked above block a retain their original order when moved.
     *
     * @param  int $a A block number
     * @param  int $b Another block number number
     * @return void
     */
    public function pileOver(int $a, int $b): void
    {
        if (!$blocksArrayKeys = $this->getLegalKeys($a, $b)) {
            return;
        }

        $blockA = $this->getBlockStack($blocksArrayKeys['a'], $a);
        $this->stackBlocks($blockA, $blocksArrayKeys['b'], $b);
    }

    /**
     * Prints the result of the final state of blocks, and quits
     * @return void
     */
    public function quit(): void
    {
        echo "\n";
        foreach ($this->blocks as $k => $block) {
            echo "$k:";
            while ($block !== null) {
                echo $block->number ? " $block->number" : '';
                $block = $block->next;
            }
            echo "\n";
        }
        echo "\n";
    }

    /**
     * A method primarily for testing, where we return
     * the protected blocks property
     *
     * @return array An array of the current blocks configuration
     */
    public function getBlocksArray(): array
    {
        return $this->blocks;
    }

    /**
     * Sets the array of blocks on the indexes given,
     * if we haven't used any other methods from this class to do the same thing
     *
     * @param  array $blocksArray A numeric array of blocks
     * @return void
     */
    public function setArrayOfBlockBlocks(array $blocks): void
    {
        $this->blocks = $blocks;
    }

    /**
     * Checks for an illegal configuration of blocks
     *
     * @param  int $a A block number
     * @param  int $b Another block number
     * @return bool|array Returns false if keys are illegal, an array of the 2 keys otherwise
     */
    public function getLegalKeys(int $a, int $b): bool|array
    {
        // Same block number:
        if ($a == $b) {
            return false;
        }

        $blocksArrayKeys = $this->getPositionsInBlocksArray($a, $b);
        // Same stack of blocks:
        if ($blocksArrayKeys['a'] == $blocksArrayKeys['b']) {
            return false;
        }

        return $blocksArrayKeys;
    }

    /**
     * Gets the positions in the array of both blocks
     *
     * @param  int   $a A block
     * @param  int   $b Another block
     * @return array The positions of both blocks in the array
     */
    public function getPositionsInBlocksArray(int $a, int $b): array
    {
        $blocksArrayKeys = [];

        foreach ($this->blocks as $key => $block) {
            while ($block !== null) {
                if ($block->number !== null) {
                    if ($block->number == $a) {
                        $blocksArrayKeys['a'] = $key;
                    }

                    if ($block->number == $b) {
                        $blocksArrayKeys['b'] = $key;
                    }

                    if (isset($blocksArrayKeys['a']) && isset($blocksArrayKeys['b'])) {
                        return $blocksArrayKeys;
                    }
                }

                $block = $block->next;
            }
        }

        return $blocksArrayKeys;
    }

    /**
     * Puts the stack of $a on top of the stack of $b
     *
     * @param  Block $stackOfBlocks
     * @param  int   $b A block number
     * @return void
     */
    protected function stackBlocks(Block $stackOfBlocks, int $key, int $b): void
    {
        $block = $this->blocks[$key];

        while ($block !== null) {
            if ($block->number == $b) {
                // Move to the top of the stack containing $b:
                while ($block->next) {
                    $block = $block->next;
                }

                // Put $stackOfBlocks on the top of the stack containing $b:
                $block->next = $stackOfBlocks;

                return;
            }

            $block = $block->next;
        }
    }

    /**
     * Puts the blocks into their initial positions
     *
     * @param int $blockNumber
     * @return void
     */
    protected function setToInitialPositionOnStack(int $blockNumber): void
    {
        // Here we set it to its initial position, $this->blocks[$blockNumber]:
        $position = $this->blocks[$blockNumber];
        // Check if there are other blocks on that block:
        while ($position->next) {
            $position = $position->next;
        }
        $position->number = $blockNumber;
    }

    /**
     * Creates a clone of the original block. The clone is independent of the heap.
     * The original block's number / next become null on the heap.
     * We return the detached clone of the original block.
     *
     * @param  int  $blockNumber
     * @return Block A clone independent / detached from the heap
     */
    protected function getBlockStack(int $key, int $blockNumber): Block
    {
        if ($blockNumber === null) {
            return new Block();
        }

        $block = $this->blocks[$key];

        while ($block !== null) {
            if ($block->number == $blockNumber) {
                $heapBlockA = $block;
                // the clone is separate from the heap:
                $clonedBlockA = clone $block;
                // Make the internal pointers null to change the class-wide heap
                // (affecting both $heapBlockA and $block):
                $heapBlockA->number = null;
                $heapBlockA->next = null;
                // Make the external pointers null for completeness
                // (though this isn't necessary: the external pointers die w/this method):
                $heapBlockA = $block = null;

                // Return the (unchanged) clone:
                return $clonedBlockA;
            }
            $block = $block->next;
        }

        return new Block();
    }

    /**
     * Reset all blocks on top of $blockNumber to their initial positions
     *
     * @param  int  $blockNumber The number belonging to a block, which we'll return as a full block node
     * @param  bool $deleteBlockNumberForBlockA
     * @return Block A full node of the $blockNumber
     */
    protected function resetBlocks(int $key, int $blockNumber, bool $deleteBlockNumberForBlockA = false): Block
    {
        // $block points to the class wide heap,
        // and can therefore make class wide changes to it.
        // Can be $a, move[Onto|Over](), OR $b with pileOnto():
        $block = $this->blocks[$key];

        while ($block !== null) {
            if ($block->number == $blockNumber) {
                // $temp can now also make class-wide changes:
                $temp = $block->next;
                // Does not affect $temp:
                $block->next = null;

                // Delete $a when we move[Onto|Over]:
                if ($deleteBlockNumberForBlockA) {
                    $block->number = null;
                }

                // $temp = block $b, which usually has a stack
                // which we have to set to their original positions:
                while ($temp !== null) {
                    // Set each block back to its original position:
                    if ($temp->number !== null) {
                        $this->setToInitialPositionOnStack($temp->number);
                    }
                    $temp = $temp->next;
                }

                return new Block($blockNumber);
            }

            $block = $block->next;
        }

        return new Block($blockNumber);
    }
}
