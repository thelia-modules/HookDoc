<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/
namespace HookDoc\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;
use Thelia\Command\ContainerAwareCommand;
use Thelia\Core\Template\TemplateDefinition;

/**
 * Class ExportList
 * @author Manuel Raynaud <manu@thelia.net>
 */
class ExportList extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('hook:export-list')
            ->setDescription('export official hook list for a defined template')
            ->addArgument(
                'template',
                InputArgument::OPTIONAL,
                'template to export
1 : frontOffice
2 : backOffice
3 : PDF
4 : Email ',
                TemplateDefinition::FRONT_OFFICE
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'export format wanted : json, xml, yml or array',
                'json'
            )
            ->addOption(
                'order',
                'o',
                InputOption::VALUE_NONE,
                'hooks order by file'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // parse the current template
        $hookHelper = $this->getContainer()->get("thelia.hookHelper");
        $templateType = $input->getArgument('template');

        $hooks = $hookHelper->parseActiveTemplate($templateType);

        if ($input->getOption('order')) {
            $hooks = $this->orderByFile($hooks);
        }

        $format = $input->getOption('format');
        $response = '';
        switch ($format) {
            case 'json':
                $response = json_encode($hooks);
                break;
            case 'xml':
                $response = $this->xmlFormat($hooks);
                break;
            case 'yml':
                $response = Yaml::dump($hooks);
                break;
            case 'array':
                $response = var_export($hooks);
                break;
            default:
                throw new \RuntimeException(sprintf('format %s not supported', $format));
        }

        $output->write($response, false, OutputInterface::OUTPUT_RAW);
    }

    /**
     * @param $data
     * @return \Symfony\Component\Serializer\Encoder\scalar
     */
    protected function xmlFormat($data)
    {
        $serializer = new Serializer([], [new XmlEncoder('hooks')]);

        return $serializer->encode($data, 'xml');
    }

    protected function orderByFile($hooks)
    {
        $ordered = [];

        do {
            $current = array_shift($hooks);

            $find = $this->findFile($file = $current['file'], $hooks);

            $find[] = $current;

            $ordered[$file] =$find;

        } while (count($hooks) > 0);

        return $ordered;
    }

    protected function findFile($file, &$hooks)
    {
        $match = [];
        foreach ($hooks as $index => $hook) {
            if ($hook['file'] == $file) {
                $match[] = $hook;
                unset($hooks[$index]);
            }
        }

        return $match;
    }
}
