<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Communication\Console;

use Exception;
use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\Worldline\Communication\WorldlineCommunicationFactory getFactory()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface getRepository()
 */
class WorldlineTokenConsole extends Console
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'worldline:token:remove-deleted';

    /**
     * @var string
     */
    public const DESCRIPTION = 'Remove tokens that are marked as deleted from the database.';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(static::COMMAND_NAME)
            ->setDescription(static::DESCRIPTION)
            ->setHelp('<info>' . static::COMMAND_NAME . ' -h</info>');

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=yellow>----------------------------------------</fg=yellow>');
        $output->writeln('<fg=yellow>Remove tokens that are marked as deleted from the database</fg=yellow>');
        $output->writeln('');

        try {
            $deleteCount = $this->getFacade()->deleteWorldlineTokensMarkedAsDeleted();
        } catch (Exception $exception) {
            $this->error('Error happened during deleting tokens marked as deleted.');
            $this->error($exception->getMessage());

            return static::CODE_ERROR;
        }

        $output->writeln(sprintf('<fg=white>Removed %s tokens that were marked as deleted</fg=white>', $deleteCount));
        $output->writeln('');
        $output->writeln('<fg=green>Finished. All Done.</fg=green>');

        return static::CODE_SUCCESS;
    }
}
