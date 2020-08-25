<?php

/**
 * 2019-06-28.
 */

declare(strict_types=1);

namespace App\Command;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class FetchDataCommand.
 */
class FetchLastTrailersCommand extends Command
{
    /**
     * The default source link for trailers
     */
    private const SOURCE = 'https://trailers.apple.com/trailers/home/rss/newtrailers.rss';

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
    private EntityManagerInterface $doctrine;

    /**
     * @var string
     */
    protected static $defaultName = 'fetch:trailers';

    /**
     * FetchDataCommand constructor.
     *
     * @param ClientInterface        $httpClient
     * @param LoggerInterface        $logger
     * @param EntityManagerInterface $em
     * @param string|null            $name
     */
    public function __construct(
        ClientInterface $httpClient,
        LoggerInterface $logger,
        EntityManagerInterface $em,
        string $name = null
    ) {
        parent::__construct($name);
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->doctrine = $em;
    }

    /**
     *
     */
    public function configure(): void
    {
        $this
            ->setName('fetch:trailers')
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
        $this->logger->info(sprintf('Start %s at %s', __CLASS__, (string) date_create()->format(DATE_ATOM)));
        if (is_null($input->getOption('source'))) {
            $source = self::SOURCE;
        } else {
            $source = $input->getOption('source');
        }

        if (!is_string($source)) {
            throw new RuntimeException('Source must be string');
        }
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Fetch data from %s', $source));

        try {
            $response = $this->httpClient->sendRequest(new Request('GET', $source));
        } catch (ClientExceptionInterface $e) {
            throw new RuntimeException($e->getMessage());
        }
        if (($status = $response->getStatusCode()) !== 200) {
            throw new RuntimeException(sprintf('Response status is %d, expected %d', $status, 200));
        }
        $data = $response->getBody()->getContents();
        $quantity = (int) $input->getOption('quantity');
        $this->processXml($data, $quantity);

        $this->logger->info(sprintf('End %s at %s', __CLASS__, (string) date_create()->format(DATE_ATOM)));

        return CommandConstants::FINISH_SUCCESS;
    }

    /**
     * @param string $data
     *
     * @param int $quantity
     * @throws \Exception
     */
    protected function processXml(string $data, int $quantity): void
    {
        $xml = (new \SimpleXMLElement($data))->children();

        if (!property_exists($xml, 'channel')) {
            throw new RuntimeException('Could not find \'channel\' element in feed');
        }
        $count = 0;
        /** @var \SimpleXMLElement $item */
        foreach ($xml->channel->item as $item) {
            if ($count === $quantity) {
                break;
            }

            $cdata = (string)$item->xpath('content:encoded')[0];
            $imageLink = $this->getImageLinkFromHTML($cdata);

            $movie = $this->getMovieByTitle((string) $item->title);
            $movie->setTitle((string) $item->title)
                ->setDescription((string) $item->description)
                ->setLink((string) $item->link)
                ->setImage($imageLink)
                ->setPubDate($this->parseDate((string) $item->pubDate))
            ;

            $this->doctrine->persist($movie);
            $count++;
        }

        $this->doctrine->flush();
    }

    /**
     * @param string $date
     *
     * @return \DateTime
     *
     * @throws \Exception
     */
    protected function parseDate(string $date): \DateTime
    {
        return new \DateTime($date);
    }

    /**
     * @param string $title
     *
     * @return Movie
     */
    protected function getMovieByTitle(string $title): Movie
    {
        $item = $this->doctrine->getRepository(Movie::class)->findOneBy(['title' => $title]);

        if ($item === null) {
            $this->logger->info('Create new Movie', ['title' => $title]);
            $item = new Movie();
        } else {
            $this->logger->info('Move found', ['title' => $title]);
        }

        if (!($item instanceof Movie)) {
            throw new RuntimeException('Wrong type!');
        }

        return $item;
    }

    private function getImageLinkFromHTML(string $html)
    {
        preg_match('~\<img src="(.*?)"~', $html, $result);
        return $result[1] ?? null;
    }
}
