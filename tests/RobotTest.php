<?php declare(strict_types=1);

namespace App;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RobotTest extends TestCase
{
    protected $robot;

    /**
     * A fresh Bot for every test case:
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->robot = new Robot(10);
    }

    #[Test]
    public function check_existence_of_Robot_object(): void
    {
        $this->assertIsObject($this->robot);
    }

    #[Test]
    public function check_min_max_number_of_blocks(): void
    {
        $minimum = new Robot(-50);
        $blocks = $minimum->getBlocksArray();
        $this->assertSame(count($blocks), 1);

        $maximum = new Robot(25432);
        $blocks = $maximum->getBlocksArray();
        $this->assertSame(count($blocks), 25);
    }

    #[Test]
    public function check_early_return_if_illegal_commands_are_given(): void
    {
        $earlyReturnIfFalse = $this->robot->getLegalKeys(3, 3);
        $this->assertFalse($earlyReturnIfFalse);

        // Keep the input exactly like it would have been
        // had we run this method through robots.php itself:
        $blocks = $this->robot->getBlocksArray();

        $blocks[1] = new Block(1);
        $blocks[3] = new Block();
        $blocks[6] = new Block();

        $blocks[1]->next = new Block(3);
        $blocks[1]->next->next = new Block(6);

        // Set these in Robot class, as we've used no methods
        // from that class to do the same thing:
        $this->robot->setArrayOfBlockBlocks($blocks);

        // Check for early return if the blocks gives are on the same array index
        // (in the same stack of blocks):
        $earlyReturnIfFalse = $this->robot->getLegalKeys(6, 3);
        $this->assertFalse($earlyReturnIfFalse);
    }

    #[Test]
    public function check_moveOnto_method(): void
    {
        $blocks = $this->robot->getBlocksArray();
        $this->robot->moveOnto(6, 1);

        $node1 = new Block(1);
        $this->assertNotEquals($blocks[1], $node1);
        $node1->next = new Block(6);
        $node6 = new Block();

        $this->assertEquals($blocks[1], $node1);
        $this->assertEquals($blocks[6], $node6);

        // Same move has no effect on output:
        $this->robot->moveOnto(6, 1);
        $this->assertEquals($blocks[1], $node1);
        $this->assertEquals($blocks[6], $node6);
    }

    #[Test]
    public function check_moveOver_method(): void
    {
        $blocks = $this->robot->getBlocksArray();

        $node1 = new Block(1);
        $node1->next = new Block(6);
        $node6 = new Block();

        $this->robot->moveOver(6, 1);

        $this->assertEquals($blocks[6], $node6);
        $this->assertEquals($blocks[1], $node1);
        $this->assertNotEquals($blocks[1], $node6);
    }

    #[Test]
    public function check_pileOnto_method(): void
    {
        $blocks = $this->robot->getBlocksArray();

        $node2 = new Block(2);
        $node2->next = new Block(6);

        $node6 = new Block();

        $this->robot->pileOnto(6, 2);

        $this->assertEquals($blocks[6], $node6);
        $this->assertEquals($blocks[2], $node2);
    }

    #[Test]
    public function check_pileOver_method(): void
    {
        $blocks = $this->robot->getBlocksArray();
        $this->robot->pileOver(2, 9);

        $node2 = new Block();
        $node9 = new Block(9);
        $node9->next = new Block(2);

        $this->assertEquals($blocks[2], $node2);
        $this->assertEquals($blocks[9], $node9);

        $this->robot->pileOver(8, 9);
        $this->assertNotEquals($blocks[9], $node9);

        $node8 = new Block();
        $node9->next->next = new Block(8);

        $this->assertEquals($blocks[2], $node2);
        $this->assertEquals($blocks[8], $node8);
        $this->assertEquals($blocks[9], $node9);
    }

    #[Test]
    public function check_method_combo(): void
    {
        $blocks = $this->robot->getBlocksArray();

        // These are all the same Block, but PHPUnit::assertEquals()
        // just checks that the contents of the nodes are the same:
        $node0 = $node1 = $node6 = $node7 = $node8 = new Block();

        $node2 = new Block(2);
        $node2->next = new Block(1);
        $node2->next->next = new Block();
        $node3 = new Block(3);
        $node4 = new Block(4);
        $node4->next = new Block(6);
        $node4->next->next = new Block(7);
        $node4->next->next->next = new Block(0);
        $node5 = new Block(5);
        $node9 = new Block(9);
        $node9->next = new Block(8);

        $nodes = [
            0 => $node0,
            1 => $node1,
            2 => $node2,
            3 => $node3,
            4 => $node4,
            5 => $node5,
            6 => $node6,
            7 => $node7,
            8 => $node8,
            9 => $node9,
        ];

        $this->robot->moveOnto(8, 1);
        $this->robot->moveOver(7, 6);
        $this->robot->pileOnto(6, 1);
        $this->robot->pileOver(0, 1);

        $this->robot->pileOver(1, 2);
        $this->robot->pileOver(1, 7);
        $this->robot->moveOnto(1, 7);

        $this->robot->moveOnto(3, 4);
        $this->robot->moveOnto(8, 9);
        $this->robot->pileOnto(6, 4);

        $this->assertEquals($nodes, $blocks);
    }
}
