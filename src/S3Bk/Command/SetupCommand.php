<?php

namespace S3Bk\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Dumper;

/**
 * SetupCommand.
 */
class SetupCommand extends Command
{
    protected function configure()
    {
        $this->setName('setup')
            ->setDescription(
                'Setup the backup scheduler run ./s3bk.phar help setup for '.
                'more information'
            );
        $this->setHelp(
            <<<HELP
<comment>s3bk - an Amazon S3 backup client</comment>

Once you run this command a sample configuration file will be written to:

    /your/home/dir/.s3bk/config.yml

This is where you will need to set your AWS key and secret pair, and your
preferred AWS region, which I have defaulted to eu-west-1.

You will also need to set a prefix here that should be as globally unique
as possible. This is not a software limitation, but a limitation of the
S3 bucket naming strategy.  Note, whilst I convert the prefix to lower
case this is the only help you will get in creating a valid bucket name.

<comment>Please see the help for the mount:add command for details of what
to do once the command is installed</comment>

HELP
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseDir = dirname(dirname(dirname(__DIR__)));
        $command
                 =
            '* * * * * /usr/local/bin/php '.$baseDir.'/app/console.php run';
        $regex   = '/'.
            str_replace(['*', '/', '.'], ['\\*', '\\/', '\\.'], $command).
            '/';
        $oldCron = exec('crontab -l 2>&1');
        if (preg_match($regex, $oldCron)) {
            $output->writeln('<comment>Command is already installed</comment>');
        } else {
            $this->addRunCommandToCron($output, $oldCron, $command);
        }

        $baseCfgDir = $_SERVER['HOME'].'/.s3bk';
        if (!is_dir($baseCfgDir)) {
            mkdir($baseCfgDir);
        }
        $cfgPath = $baseCfgDir.'/config.yml';
        if (file_exists($cfgPath)) {
            $output->writeln(
                '<comment>'.
                'Configuration already exists at: '.$cfgPath.
                '</comment>'
            );
        } else {
            $this->dumpSampleConfig($output, $cfgPath);
        }
    }

    /**
     * Adds the run command to the user's crontab
     *
     * @param OutputInterface $output
     * @param string          $oldCron
     * @param string          $command
     */
    private function addRunCommandToCron(
        OutputInterface $output,
        $oldCron,
        $command
    ) {
        $nullTab = '/no crontab for/';
        if (preg_match($nullTab, $oldCron, $matches)) {
            $oldCron = '';
        }

        $newCron = $oldCron.PHP_EOL.$command.PHP_EOL;
        $tmpName = '/tmp/s3bk-'.uniqid().'.cron';
        file_put_contents($tmpName, $newCron);
        exec('crontab '.$tmpName);
        $output->writeln('<info>Command installed</info>');
    }

    /**
     * Writes config.yml to the user's home directory
     *
     * @param OutputInterface $output
     * @param string          $cfgPath
     */
    private function dumpSampleConfig(OutputInterface $output, $cfgPath)
    {
        $config = [
            'aws' => [
                'credentials' => [
                    'key' => 'your-aws-key',
                    'secret' => 'your-aws-secret'
                ],
                'region' => 'eu-west-1'
            ],
            'prefix' => 'make-this-something-unique'
        ];

        $dumper = new Dumper();
        $yaml   = $dumper->dump($config, 3);
        file_put_contents($cfgPath, $yaml);
        $output->writeln(
            '<info>Sample config written to: '.$cfgPath.'</info>'
        );
    }
}
