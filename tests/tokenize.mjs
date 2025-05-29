import { tokenize, compile } from '@jmespath-community/jmespath/dist/jmespath.esm.js';
import readline from "readline";
import { stdin as input, stdout as output, stderr } from "node:process";


const rl = readline.createInterface({ input, output });
// rl.question("command\n", (input) => {
//     const command = JSON.parse(input);
//     console.log(command.command);
// })
rl.on('line', (input) => {
    // Parse command
    const expression = JSON.parse(input);

        // output.write(JSON.stringify(command) + '\n');
        const result = JSON.stringify({
            tokens: tokenize(expression),
            ast: compile(expression),
            expression: expression
        });

        output.write(result + "\n");
});

rl.on('close', () => {
    process.exit(0);
});

// input.resume();
input.setEncoding('utf8');

// process.stderr.write("This script helps in testing the PHP parser by checking conformity with the TS implementation, it is not meant for interactive use\n")