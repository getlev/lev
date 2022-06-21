<?php
namespace Lev\Plugin\Console;

use Lev\Common\Lev;
use Lev\Console\ConsoleCommand;
use Lev\Plugin\Email\Email;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class FlushQueueCommand
 * @package Lev\Grav\Console\Cli\
 */
class FlushQueueCommand extends ConsoleCommand
{
    /** @var array */
    protected $options = [];

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('flush-queue')
            ->setAliases(['flushqueue'])
            ->addOption(
                'env',
                'e',
                InputOption::VALUE_OPTIONAL,
                'The environment to trigger a specific configuration. For example: localhost, mysite.dev, www.mysite.com'
            )
            ->setDescription('Flushes the email queue of any pending emails')
            ->setHelp('The <info>flush-queue</info> command flushes the email queue of any pending emails');
    }

    /**
     * @return int
     */
    protected function serve()
    {
        // TODO: remove when requiring Lev 1.7+
        if (method_exists($this, 'initializeLev')) {
            $this->initializeLev();
        }

        $this->output->writeln('');
        $this->output->writeln('<yellow>Current Configuration:</yellow>');
        $this->output->writeln('');

        $lev = Lev::instance();
        dump($lev['config']->get('plugins.email'));

        $this->output->writeln('');

        require_once __DIR__ . '/../vendor/autoload.php';

        $output = Email::flushQueue();

        $this->output->writeln('<green>' . $output . '</green>');

        return 0;
    }
}
