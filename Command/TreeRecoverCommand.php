<?php

namespace Awaresoft\TreeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TreeRecoverCommand
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class TreeRecoverCommand extends ContainerAwareCommand
{
    /**
     * Configuration of command
     */
    protected function configure()
    {
        $this
            ->setName('awaresoft:tree:recover')
            ->setDescription('Recover invalid tree of selected entity')
            ->addArgument('class', InputArgument::REQUIRED, 'Tree entity class which you want to recover')
        ;
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repository = $em->getRepository($input->getArgument('class'));

        if (!class_exists($input->getArgument('class'))) {
            throw new \Exception(sprintf('class %s does not exist', $input->getArgument('class')));
        }

        if(is_array($repository->verify())) {
            $repository->recover();
            $em->flush();
        }
    }
}
