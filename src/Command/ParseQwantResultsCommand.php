<?php


namespace App\Command;

use App\Entity\SearchResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ParseQwantResultsCommand extends Command
{
    protected const BASE_URL = "https://api.qwant.com/api/search/web?count=10&t=web&safesearch=1&locale=fr_FR&uiv=4&q=";

    protected HttpClientInterface $client;

    protected EntityManagerInterface $entityManager;

    /**
     * ParseSearchResultsCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->client = HttpClient::create();
    }

    protected function configure()
    {
        $this
            ->setName('app:parse:qwant')
            ->setDescription('Parse searches results and populates the database')
            ->addArgument('file', InputArgument::REQUIRED, "Translation file")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->info("Starting...");

        try {
            if (!is_file($file = $input->getArgument('file'))) {
                throw new \LogicException("No such file {$input->getArgument('file')}");
            }
            $words = file($file);
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
        }

        $io->progressStart(count($words));
        foreach($words as $word) {
            $word = substr($word, 0, -1);
            $results = $this->getSearchResults($word);
            foreach ($results as $result) {
                $searchResult = new SearchResult();
                $searchResult->setUrl($result['url'])
                    ->setSearchEngine(SearchResult::ENGINE_QWANT)
                    ->setQuery($word)
                    ->setPosition($result['position']);

                $this->entityManager->persist($searchResult);
            }
            $this->entityManager->flush();
            $this->entityManager->clear(SearchResult::class);


            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->info("Finished parsing results");

        return 0;
    }

    protected function getSearchResults(string $word): array
    {
        $response = $this->client->request('GET', self::BASE_URL . $word, [
            'headers' => [
                'user-agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 11_0_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36"
            ],
        ]);

        $content = json_decode($response->getContent(), true);

        return array_map(function (array $item) {
            return [
                'url' => $item['url'],
                'position' => $item['position']
            ];
        }, $content['data']['result']['items']);
    }
}