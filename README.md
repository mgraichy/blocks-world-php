# Blocks World

This is the classic [Blocks World](https://onlinejudge.org/external/1/101.pdf), in PHP.

  - Implemented in the CLI with a basic text editor
  - Unlike most PHP scripts, Blocks World makes use of a data structure class embedded into an algorithm class (much like linked lists).
    - I found that this was the most elegant way to capture all and only the requirements for Blocks World, which requires very much attention to detail to get right in an efficient way.


## How to Run the Code

- **The Script Runs on Text Files.** Out of the box, I've included an input.txt file with some sample input:
  1. Download the project
  2. Go to the project's root directory
  3. Run the following:
    ```php
    php bot input.txt
    ```
- **Including Your Own Input File.**
  1. Create a text file with the following possible keywords:
        ```
        # Comments can be placed anywhere
        15 blocks
        print move x onto y
        print move x over y
        print pile x onto y
        print pile x over y
        ```
        - Concretely:
            ```
            25 blocks
            print move 8 onto 4
            print pile 6 onto 3
            # ...
            ```
  2. Place the file in the project's root directory.
  3. Run it from the project's root directory: `php bot [your txt file]`
  4. If you prefer, you can print only the final state of how the blocks end up:
        ```
        10 blocks
        move 8 onto 4
        pile 6 onto 3
        # prints out the final state only:
        quit
        ```

## How to Run the Unit Tests

  1. **[Blocks World Uses PHPUnit 11.4](https://docs.phpunit.de/en/11.4/).**  From the project root, type `./tools/phpunit`


