<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Movie;
use App\Service\RSSTrailersService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FetchLastTrailersCommand extends Command
{
    /**
     * @var ClientInterface
     */
    private ClientInterface $httpClient;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var string
     */
    protected static $defaultName = 'fetch:trailers';
    /**
     * @var RSSTrailersService
     */
    private RSSTrailersService $RSSTrailersService;

    /**
     * FetchDataCommand constructor.
     *
     * @param ClientInterface $httpClient
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     * @param RSSTrailersService $RSSTrailersService
     * @param string|null $name
     */
    public function __construct(
        ClientInterface $httpClient,
        LoggerInterface $logger,
        EntityManagerInterface $em,
        RSSTrailersService $RSSTrailersService,
        string $name = null
    ) {
        parent::__construct($name);
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->em = $em;
        $this->RSSTrailersService = $RSSTrailersService;
    }

    /**
     *
     */
    public function configure(): void
    {
        $this
            ->setName(self::getDefaultName())
            ->setDescription('Fetch data from iTunes Movie Trailers')
            ->addOption(
                'source',
                's',
                InputOption::VALUE_OPTIONAL,
                'The source data link '
            )
            ->addOption(
                'quantity',
                null,
                InputOption::VALUE_OPTIONAL,
                'The quantity of fetching trailers',
                10
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(sprintf('Start %s at %s', __CLASS__, (string) date_create()->format(DATE_ATOM)));

        $document = new \DOMDocument();
        $rssString = $this->RSSTrailersService->getData($input->getOption('source'));
        $document->loadXML($rssString);

        $quantity = (int) $input->getOption('quantity');
        $count = 0;
        $progressBar = new ProgressBar($output);

        /** @var \DOMDocument $node */
        foreach ($document->getElementsByTagName('item') as $node) {
            if ($count === $quantity) {
                break;
            }

            $movie = new Movie();
            $movie
                ->setTitle($node->getElementsByTagName('title')->item(0)->nodeValue)
                ->setDescription($node->getElementsByTagName('description')->item(0)->nodeValue)
                ->setLink($node->getElementsByTagName('link')->item(0)->nodeValue)
                ->setPubDate(
                    new \DateTime($node->getElementsByTagName('pubDate')->item(0)->nodeValue)
                );
            $textContent = $node->textContent;
            $imageLink = $this->getImageLinkFromTextContent($textContent);
            $movie->setImage($imageLink);

            $this->em->persist($movie);
            $progressBar->advance();
            $count++;
        }
        $this->em->flush();
        $progressBar->finish();
        $output->writeln(sprintf('End %s at %s', __CLASS__, (string) date_create()->format(DATE_ATOM)));

        return CommandConstants::FINISH_SUCCESS;
    }

    private function getImageLinkFromTextContent(string $text)
    {
        preg_match('~\<img src="(.*?)"~', $text, $result);
        return $result[1] ?? null;
    }
}
